<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use FormGent\App\Repositories\SpreadsheetRepository;
use FormGent\App\Jobs\SpreadsheetHeader;
use FormGent\WpMVC\Contracts\Provider;
use FormGent\App\DTO\AnswerFieldDTO;
use FormGent\App\DTO\QueueDTO;
use FormGent\App\EnumeratedList\QueueStatus;
use FormGent\App\Fields\MultipleChoice\MultipleChoice;
use FormGent\App\Repositories\FormPresetFieldRepository;
use FormGent\App\Fields\Address\Address;
use FormGent\App\Fields\Dropdown\Dropdown;
use FormGent\App\Fields\FileUpload\FileUpload;
use FormGent\App\Fields\GoogleMap\GoogleMap;
use FormGent\App\Fields\Name\Name;
use FormGent\App\Fields\PhoneNumber\PhoneNumber;
use FormGent\App\Fields\SingleChoice\SingleChoice;
use stdClass;

class SpreadsheetServiceProvider implements Provider {
    public SpreadsheetHeader $spreadsheet_header_job;

    const TASK_TYPE = 'spreadsheet';

    public function boot() {
        $this->spreadsheet_header_job = formgent_singleton( SpreadsheetHeader::class );

        $task_type = self::TASK_TYPE;

        add_action( 'formgent_push_queue_items', [$this, 'push_queue_items'], 10, 3 );
        add_action( "formgent_process_{$task_type}", [$this, 'process_task'], 10, 2 );
        add_action( 'post_updated', [ $this, 'update_google_sheet_header' ], 11, 2 );
    }

    public function push_queue_items( array &$dtos, int $response_id, stdClass $form ) {
        if ( ! formgent_is_google_sheet_enabled() ) {
            return;
        }

        $repository   = formgent_singleton( SpreadsheetRepository::class );
        $spreadsheets = $repository->get_by_form_id( $form->ID );

        if ( empty( $spreadsheets ) ) {
            return [];
        }

        foreach ( $spreadsheets as $spreadsheet ) {
            $dtos[] = ( new QueueDTO )->set_form_id( $form->ID )->set_response_id( $response_id )->set_task_type( self::TASK_TYPE )->set_task_id( $spreadsheet->id );
        }
    }

    public function process_task( stdClass $queue, callable $callback ) {
        if ( ! formgent_is_google_sheet_enabled() ) {
            $callback( QueueStatus::COMPLETED );
            return;
        }

        $spreadsheet = \FormGent\App\Models\Spreadsheet::query( 'spreadsheet' )->where( 'spreadsheet.id', $queue->task_id )->first();

        if ( ! $spreadsheet ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        $response = formgent_response_repository()->get_response_dto( $queue->response_id );

        if ( ! $response ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        // Form Answers
        $form_answers_data = formgent_get_form_answers( $spreadsheet->form_id, $queue->response_id, false );

        if ( $spreadsheet->condition_status ) {
            $condition = formgent_conditional_logic()
                ->set_response( $response )
                ->set_answers_items( $form_answers_data )
                ->set_logical_type( $spreadsheet->condition_type )
                ->set_conditions( json_decode( $spreadsheet->conditions, true ) );

            if ( ! $condition->is_passed() ) {
                $callback( QueueStatus::COMPLETED );
                return;
            }
        }

        $columns = json_decode( $spreadsheet->columns, true );
        $values  = $this->get_values( $form_answers_data, $columns );

        /**
         * @var \FormGent\App\Integrations\Google\Spreadsheet $spreadsheet_service
         */
        $spreadsheet_service = formgent_singleton( \FormGent\App\Integrations\Google\Spreadsheet::class );

        try {
            $spreadsheet_service->append_row( $spreadsheet->spreadsheet_id, $spreadsheet->sheet_id, $values, array_key_last( $columns ) );
            $callback( QueueStatus::COMPLETED );
        } catch ( \Throwable $th ) {
            $callback( QueueStatus::FAILED );
        }
    }

    private function get_values( array $form_answers_data, array $columns ) {
        $field_processors = [
            Dropdown::get_key()       => function( AnswerFieldDTO $field ) {
                return $this->get_option_label_by_value( $field, $field->get_value() );
            },
            SingleChoice::get_key()   => function( AnswerFieldDTO $field ) {
                return $this->get_option_label_by_value( $field, $field->get_value() );
            },
            FileUpload::get_key()     => function( AnswerFieldDTO $field ) {
                return $this->process_file_upload_value( $field->get_value() );
            },
            'digital-signature'       => function( AnswerFieldDTO $field ) {
                return WP_CONTENT_URL . '/uploads/' . $field->get_value();
            },
            MultipleChoice::get_key() => function( AnswerFieldDTO $field ) {
                return $this->process_multiple_choice_value( $field );
            },
            Name::get_key()           => function( AnswerFieldDTO $field ) {
                return $this->process_composite_field_value( $field );
            },
            Address::get_key()        => function( AnswerFieldDTO $field ) {
                return $this->process_composite_field_value( $field );
            },
            PhoneNumber::get_key()    => function( AnswerFieldDTO $field ) {
                return $this->process_phone_number_value( $field->get_value() );
            },
            GoogleMap::get_key()      => function( AnswerFieldDTO $field ) {
                $value = $field->get_value();
                if ( empty( $value ) ) {
                    return $value;
                }
                $map_data = json_decode( $field->get_value(), true );
                return "Address: {$map_data['address']}, Coordinates: ({$map_data['map']['lat']}, {$map_data['map']['lng']})";
            },
            'date-picker'             => function ( AnswerFieldDTO $field ) {
                return "'" . $field->get_value();
            }
        ];

        foreach ( $columns as &$field_id ) {
            if ( ! isset( $form_answers_data[$field_id] ) ) {
                $field_id = '';
                continue;
            }

            $field      = $form_answers_data[$field_id];
            $field_type = $field->get_field_type();

            if ( isset( $field_processors[$field_type] ) ) {
                $field_id = $field_processors[$field_type]( $field );
            } else {
                $field_id = $field->get_value();
            }
        }

        return array_values( $columns );
    }

    /**
     * Get option label by value from field options
     */
    private function get_option_label_by_value( AnswerFieldDTO $field, $value ) {
        $option_key = array_search( $value, array_column( $field->get_options(), 'value' ) );
        return is_int( $option_key ) ? $field->get_options()[$option_key]['label'] : '';
    }

    /**
     * Process file upload field value
     */
    private function process_file_upload_value( $value ) {
        $files = array_map(
            function( $file ) {
                return WP_CONTENT_URL . '/uploads/' . $file;
            }, 
            json_decode( $value, true ) ?? []
        );
        return implode( PHP_EOL, $files );
    }

    /**
     * Process multiple choice field value
     */
    private function process_multiple_choice_value( AnswerFieldDTO $field ) {
        $selected_options = json_decode( $field->get_value() );
        
        return implode(
            PHP_EOL, array_map(
                function( $option ) use ( $field ) {
                    return $this->get_option_label_by_value( $field, $option );
                }, 
                $selected_options
            )
        );
    }

    /**
     * Process composite field value (Name, Address)
     */
    private function process_composite_field_value( AnswerFieldDTO $field ) {
        $children = array_map(
            function( $child ) {
                if ( in_array( $child->get_field_type(), [Dropdown::get_key(), SingleChoice::get_key()], true ) ) {
                    return $this->get_option_label_by_value( $child, $child->get_value() );
                }
                return $child->get_field_label() . ': ' . $child->get_value();
            }, 
            $field->get_children()
        );
        return implode( PHP_EOL, $children );
    }

    /**
     * Process phone number field value
     */
    private function process_phone_number_value( $value ) {
        return ( substr( $value, 0, 1 ) === '+' ) ? "'" . $value : $value;
    }

    public function update_google_sheet_header( int $post_id, \WP_Post $post ) {
        if ( formgent_post_type() !== $post->post_type || ! formgent_is_google_sheet_enabled() ) {
            return;
        }

        $fields = formgent_get_form_fields( formgent_get_form_by_id( $post_id ) );

        $labels = array_values(
            array_map(
                function( $field ) {
                    return $field['label'] ?? '';
                }, $fields
            )
        );

        $old_labels = get_post_meta( $post_id, 'formgent_form_fields_labels', true ) ?? [];

        /**
         * Dispatches a spreadsheet header update job if form field labels have changed,
         * including cases where fields were added, renamed, reordered, or removed.
         */
        if ( $old_labels === $labels ) {
            return;
        }

        update_post_meta( $post_id, 'formgent_form_fields_labels', $labels );

        \FormGent\App\Models\Spreadsheet::query()->where( 'form_id', $post_id )->update(
            [
                'is_columns_updated' => 0
            ]
        );

        $spreadsheet = \FormGent\App\Models\Spreadsheet::query()->where( 'form_id', $post_id )->where( 'is_columns_updated', 0 )->first();

        $this->spreadsheet_header_job->dispatch_spreadsheet( $spreadsheet, $post_id );
    }
}