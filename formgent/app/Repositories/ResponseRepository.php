<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\ResponseDTO;
use FormGent\App\DTO\ResponseReadDTO;
use FormGent\App\DTO\ResponseSingleDTO;
use FormGent\App\DTO\AllResponsesReadDTO;
use FormGent\App\EnumeratedList\ResponseStatus;
use FormGent\App\Models\Response;
use FormGent\App\Models\ResponseMeta;
use FormGent\App\Models\User;
use FormGent\App\Models\Post;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\App\Models\Answer;
use FormGent\WpMVC\Database\Query\JoinClause;
use FormGent\App\Fields\FileUpload\FileUpload;
use FormGent\App\Fields\Dropdown\Dropdown;
use FormGent\App\Fields\MultipleChoice\MultipleChoice;
use FormGent\App\Fields\Rating\Rating;
use FormGent\App\Fields\Repeater\Repeater;
use FormGent\App\Fields\SingleChoice\SingleChoice;
use FormGent\App\Fields\RangeSlider\RangeSlider;
use Exception;

class ResponseRepository {
    public function get( ResponseReadDTO $dto ) {
        $form = formgent_get_form_by_id( $dto->get_form_id() );

        if ( ! $form ) {
            throw new Exception( esc_html__( 'Form not found.', 'formgent' ), 404 );
        }

        $responses_query = $this->response_query( $dto );

        $count_query = clone $responses_query;

        do_action( 'formgent_responses_count_query', $count_query, $dto );

        $responses_query->with( 'answers.children' );

        $fields_data = formgent_get_form_fields( $form );

        if ( formgent_is_payment_form( $fields_data ) ) {
            $responses_query->with( 'order.payment' );
        }

        $select_columns = ['response.id', 'response.form_id', 'post.post_title as form_title', 'user.user_email', 'response.is_read', 'response.is_starred', 'response.is_completed', 'response.device', 'response.browser', 'response.created_at', 'response.updated_at'];
        $group_columns  = ['response.id', 'response.form_id', 'response.is_read', 'response.is_starred', 'response.is_completed', 'response.device', 'response.browser', 'response.created_at', 'response.updated_at'];

        $responses_query->select( $select_columns )->group_by( $group_columns );

        $this->responses_order_query( $responses_query, $dto );

        do_action( 'formgent_responses_query', $responses_query, $dto );

        $responses = array_map(
            function( $response ) use( $fields_data ) {
                $answers = [];

                foreach ( $response->answers as $answer ) {
                    if ( ! empty( $answer->children ) ) {
                        $answers = array_merge(
                            $answers, array_map(
                                function( $children_answer ) use( $answer, $fields_data ) {
                                    if ( ! empty( $fields_data[$answer->field_name] ) ) {
                                            $field_data      = $fields_data[$answer->field_name];
                                            $children_answer = $this->prepare_answer_data( $children_answer, $field_data['children'][$children_answer->field_name] ?? [], false );
                                    }

                                    $children_answer->field_name = $answer->field_name . '.' . $children_answer->field_name;

                                    return $children_answer;
                                }, $answer->children
                            )
                        );
                    } else {
                        $field_data = $fields_data[$answer->field_name] ?? [];
                        $answers[]  = $this->prepare_answer_data( $answer, $field_data, false );
                    }
                    $answer->children = [];
                }
                $response->answers = $answers;
                return apply_filters( 'formgent_response_item', $response );
            }, $responses_query->pagination( $dto->get_page(), $dto->get_per_page() )
        );

        return [
            'total'     => $count_query->count( 'DISTINCT response.id' ),
            'responses' => $responses,
        ];
    }

    public function get_all( AllResponsesReadDTO $dto ) {
        $responses_query = $this->all_responses_query( $dto );

        $count_query = clone $responses_query;

        do_action( 'formgent_responses_count_query', $count_query, $dto );

        $page     = max( 1, $dto->get_page() );
        $per_page = max( 1, $dto->get_per_page() );
        $offset   = ( $page - 1 ) * $per_page;

        // Get distinct response IDs first with proper pagination
        $ids_query = clone $responses_query;

        // Apply sorting only to the IDs query (not to responses_query before cloning)
        $this->all_responses_sort_query( $ids_query, $dto );

        $ids_results = $ids_query->select( 'response.id' )
            ->group_by( ['response.id'] )
            ->limit( $per_page )
            ->offset( $offset )
            ->get();

        // Extract IDs from results
        $response_ids = array_map(
            function( $row ) {
                return (int) $row->id;
            }, $ids_results
        );

        if ( empty( $response_ids ) ) {
            return [
                'total'     => $count_query->count( 'DISTINCT response.id' ),
                'responses' => [],
            ];
        }

        // Fetch full response data for the paginated IDs
        $select_columns = [
            'response.id',
            'response.form_id',
            'post.post_title as form_title',
            'response.is_completed',
            'response.is_read',
            'response.is_starred',
            'response.created_at',
        ];

        $group_columns = [
            'response.id',
            'response.form_id',
            'post.post_title',
            'response.is_completed',
            'response.is_read',
            'response.is_starred',
            'response.created_at',
        ];

        $responses_query = $this->all_responses_query( $dto );
        $responses_query->select( $select_columns )
            ->group_by( $group_columns )
            ->where_in( 'response.id', $response_ids );

        // Apply sorting to maintain order
        $this->all_responses_sort_query( $responses_query, $dto );

        do_action( 'formgent_responses_query', $responses_query, $dto );

        $responses = array_map(
            function( $response ) {
                return [
                    'id'           => (int) $response->id,
                    'form_id'      => (int) $response->form_id,
                    'form_title'   => $response->form_title ?? '',
                    'submitted_on' => $response->created_at,
                    'is_completed' => (int) $response->is_completed === 1 ? 1 : 0,
                    'is_read'      => (int) $response->is_read,
                    'is_starred'   => (int) $response->is_starred,
                ];
            },
            $responses_query->get()
        );

        // Maintain original sort order from IDs query
        $sorted_responses = [];
        foreach ( $response_ids as $id ) {
            foreach ( $responses as $response ) {
                if ( $response['id'] === $id ) {
                    $sorted_responses[] = $response;
                    break;
                }
            }
        }

        return [
            'total'     => $count_query->count( 'DISTINCT response.id' ),
            'responses' => $sorted_responses,
        ];
    }

    private function all_responses_query( AllResponsesReadDTO $dto ) {
        $responses_query = Response::query( 'response' )
            ->join( Post::get_table_name() . ' as post', 'post.ID', 'response.form_id' )
            ->where( 'response.status', ResponseStatus::PUBLISH );

        // Apply date filtering
        $this->all_responses_date_query( $responses_query, $dto );

        if ( $dto->get_is_completed() !== null ) {
            $responses_query->where( 'response.is_completed', '=', $dto->get_is_completed() );
        }

        if ( $dto->get_is_read() !== null ) {
            $responses_query->where( 'response.is_read', $dto->get_is_read() );
        }

        if ( $dto->get_is_starred() !== null ) {
            $responses_query->where( 'response.is_starred', $dto->get_is_starred() );
        }

        $search = $dto->get_search();

        if ( empty( $search ) ) {
            return $responses_query;
        }

        $responses_query->left_join(
            Answer::get_table_name() . " as answer", function( JoinClause $join ) {
                $join->on_column( "response.id", "answer.response_id" );
            }
        );

        global $wpdb;

        $search = "%{$search}%";

        $search_query = $wpdb->prepare( "(post.post_title like %s or answer.value like %s)", $search, $search );

        return $responses_query->where_raw( $search_query );
    }

    private function all_responses_date_query( Builder $query, AllResponsesReadDTO $dto ) {
        return $this->apply_date_filter( $query, $dto->get_date_type(), $dto->get_date_frame() );
    }

    private function responses_date_query( Builder $query, ResponseReadDTO $dto ) {
        return $this->apply_date_filter( $query, $dto->get_date_type(), $dto->get_date_frame() );
    }

    private function apply_date_filter( Builder $query, ?string $date_type, ?array $date_frame ): Builder {
        if ( empty( $date_type ) || 'all' === $date_type ) {
            return $query;
        }

        global $wpdb;

        $now              = formgent_now();
        $from_date_format = "Y-m-d 00:00:01";
        $to_date_format   = "Y-m-d 23:59:59";

        if ( 'today' === $date_type ) {
            $today = $now->format( $from_date_format );
            return $query->where_raw( $wpdb->prepare( "response.created_at >= %s", $today ) );
        }

        if ( 'date_frame' === $date_type ) {
            $date_format = "Y-m-d";
            $date_frame  = is_array( $date_frame ) ? $date_frame : [];

            if ( empty( $date_frame['from'] ) ||
                ! is_string( $date_frame['from'] ) ||
                empty( $date_frame['to'] ) ||
                ! is_string( $date_frame['to'] ) ||
                ! formgent_is_valid_date( $date_frame['from'], $date_format ) ||
                ! formgent_is_valid_date( $date_frame['to'], $date_format )
            ) {
                return $query;
            }

            $from = sanitize_text_field( $date_frame['from'] ) . ' 00:00:01';
            $to   = sanitize_text_field( $date_frame['to'] ) . ' 23:59:59';
        } else {
            switch ( $date_type ) {
                case 'yesterday':
                    $date = $now->sub_days( 1 );
                    break;
                case 'last_week':
                    $date = $now->sub_days( 6 );
                    break;
                case 'last_month':
                    $date = $now->sub_days( 30 );
                    break;
                default:
                    return $query;
            }
            $from = $date->format( $from_date_format );
            $to   = formgent_now()->format( $to_date_format );
        }

        return $query->where_raw( $wpdb->prepare( "response.created_at >= %s AND response.created_at <= %s", $from, $to ) );
    }

    private function all_responses_sort_query( Builder $query, AllResponsesReadDTO $dto ) {
        $sort_by = $dto->get_sort_by();

        if ( empty( $sort_by ) ) {
            // Use order_by and order if sort_by is not provided
            $allowed_columns = ['id', 'form_id', 'is_completed', 'is_read', 'created_at'];
            $order_by        = $dto->get_order_by() ?? 'id';
            $order_by        = in_array( $order_by, $allowed_columns, true ) ? $order_by : 'id';
            $order           = $dto->get_order() ?? 'desc';
            $order           = in_array( $order, ['asc', 'desc'], true ) ? strtolower( $order ) : 'desc';
            return $query->order_by( 'response.' . $order_by, $order );
        }

        switch ( $sort_by ) {
            case 'alphabetical':
                return $query->order_by_raw( 'post.post_title asc, response.id desc' );

            case 'date_created':
                $order = $dto->get_order() ?? 'desc';
                $order = in_array( $order, ['asc', 'desc'], true ) ? strtolower( $order ) : 'desc';

                if ( 'asc' === $order ) {
                    return $query->order_by( 'response.created_at', 'asc' );
                }

                return $query->order_by_desc( 'response.created_at' );

            case 'read':
                return $query->order_by_raw(
                    "CASE
                    WHEN response.is_read = 1 THEN 1
                    WHEN response.is_read = 0 THEN 2
                    ELSE 3
                END, response.id desc"
                );

            case 'unread':
                return $query->order_by_raw(
                    "CASE
                    WHEN response.is_read = 0 THEN 1
                    WHEN response.is_read = 1 THEN 2
                    ELSE 3
                END, response.id desc"
                );

            case 'complete':
                return $query->order_by_raw(
                    "CASE
                    WHEN response.is_completed = 1 THEN 1
                    WHEN response.is_completed = 0 THEN 2
                    ELSE 3
                END, response.id desc"
                );

            case 'incomplete':
                return $query->order_by_raw(
                    "CASE
                    WHEN response.is_completed = 0 THEN 1
                    WHEN response.is_completed = 1 THEN 2
                    ELSE 3
                END, response.id desc"
                );
            case 'starred':
                return $query->order_by_raw(
                    "CASE
                    WHEN response.is_starred = 1 THEN 1
                    WHEN response.is_starred = 0 THEN 2
                    ELSE 3
                END, response.id desc"
                );

            default:
                return $query->order_by_desc( 'response.id' );
        }
    }

    /**
     * Prepare a single field's value based on its type.
     */
    protected function prepare_field_value( \stdClass $answer, array $field_data ): \stdClass {
        if ( Dropdown::get_key() === $answer->field_type ) {
            $answer->option_label = $this->get_option_label( $field_data, $answer->value );
        } elseif ( SingleChoice::get_key() === $answer->field_type ) {
            $label = $this->get_option_label( $field_data, $answer->value );

            $allow_other = ! empty( $field_data['allow_user_add_other_option'] );
            $other_label = isset( $field_data['other_label'] ) && $field_data['other_label'] !== ''
                ? $field_data['other_label']
                : esc_html__( 'Other', 'formgent' );

            if ( '' !== $label ) {
                $answer->option_label = $label;
                $answer->is_other     = false;
            } elseif ( $allow_other ) {
                // Keep the real free-text in $answer->value, but flag the selection as "Other".
                $answer->option_label = $other_label;
                $answer->is_other     = true;
            } else {
                $answer->option_label = '';
                $answer->is_other     = false;
            }
        } elseif ( MultipleChoice::get_key() === $answer->field_type ) {
            $values = is_array( $answer->value ) ? $answer->value : json_decode( $answer->value, true ) ?? [];

            $allow_other = ! empty( $field_data['allow_user_add_other_option'] );
            $other_label = isset( $field_data['other_label'] ) && $field_data['other_label'] !== ''
                ? $field_data['other_label']
                : esc_html__( 'Other', 'formgent' );

            $answer->options = array_map(
                function( $value ) use ( $field_data, $allow_other, $other_label ) {
                    $label = $this->get_option_label( $field_data, $value );
                    if ( '' !== $label ) {
                        return [
                            'label'    => $label,
                            'value'    => $value,
                            'is_other' => false,
                        ];
                    }

                    if ( $allow_other ) {
                        return [
                            'label'    => $other_label,
                            'value'    => $value,
                            'is_other' => true,
                        ];
                    }

                    return [
                        'label'    => '',
                        'value'    => $value,
                        'is_other' => false,
                    ];
                },
                is_array( $values ) ? $values : []
            );
        } elseif ( RangeSlider::get_key() === $answer->field_type
                && isset( $field_data['slider_type'] ) && $field_data['slider_type'] === 'text'
                && isset( $field_data['text_options'] ) && is_array( $field_data['text_options'] ) ) {
            // For Range Slider text type, get label from text_options
            $answer->option_label = $this->get_text_option_label( $field_data, $answer->value );
        } elseif ( FileUpload::get_key() === $answer->field_type ) {
            // Use content_url() which respects HTTPS from site settings
            $content_url = content_url();
            $files       = json_decode( $answer->value, true ) ?? [];

            $answer->value = array_map(
                function( $file ) use ( $content_url ) {
                    return [
                        'token' => base64_encode( $file ),
                        'url'   => $content_url . '/uploads/' . $file,
                    ];
                },
                $files
            );
        } elseif ( Rating::get_key() === $answer->field_type ) {
            $answer->rating_limit = $field_data['rating_limit'] ?? 5;
            $answer->rating_icon  = $field_data['rating_icon'] ?? 'star';
        }

        return apply_filters( 'formgent_prepare_answer_data', $answer, $field_data );
    }

    protected function prepare_answer_data( \stdClass $answer, array $field_data, bool $is_repeater = true ): \stdClass {
        if ( Repeater::get_key() !== $answer->field_type || ! $is_repeater ) {
            return $this->prepare_field_value( $answer, $field_data );
        }

        $raw_value = json_decode( $answer->value, true );

        if ( ! is_array( $raw_value ) ) {
            return $answer;
        }

        $formatted = [];

        foreach ( $raw_value as $index => $item ) {
            $formatted_item = [];

            foreach ( $item as $child_key => $child_value ) {
                $child_field_data = $field_data['children'][ $child_key ] ?? [];

                $child_answer = (object) [
                    'field_type' => $child_field_data['field_type'] ?? 'text',
                    'value'      => $child_value,
                ];

                $prepared = $this->prepare_field_value( $child_answer, $child_field_data );

                $formatted_item[ $child_key ] = [
                    'field_name' => $child_key,
                    'label'      => $child_field_data['label'] ?? '',
                    'field_type' => $prepared->field_type,
                    'value'      => $prepared->value ?? '',
                ];

                switch ( $prepared->field_type ) {
                    case SingleChoice::get_key():
                    case Dropdown::get_key():
                        $formatted_item[ $child_key ]['option_label'] = $prepared->option_label ?? '';
                        break;

                    case MultipleChoice::get_key():
                        $formatted_item[ $child_key ]['options'] = $prepared->options ?? [];
                        break;

                    case Rating::get_key():
                        $formatted_item[ $child_key ]['rating_limit'] = $prepared->rating_limit ?? 5;
                        $formatted_item[ $child_key ]['rating_icon']  = $prepared->rating_icon ?? 'star';
                        break;
                }
            }

            $formatted[ $index ] = $formatted_item;
        }

        $answer->value = $formatted;

        return $answer;
    }

    private function responses_order_query( Builder $query, ResponseReadDTO $dto ) {
        if ( 'response' === $dto->get_order_field_type() ) {
            return $query->order_by( $dto->get_order_by(), $dto->get_order() );
        }

        $order_by = explode( '.', $dto->get_order_by() );

        $query->left_join(
            Answer::get_table_name() . " as order_answer", function( JoinClause $join ) use( $order_by ) {
                $join->on_column( "response.id", "order_answer.response_id" )->on( "order_answer.field_name", $order_by[0] );
            }
        );

        if ( ! empty( $order_by[1] ) ) {
            return $query->left_join(
                Answer::get_table_name() . " as order_answer_children", function( JoinClause $join ) use( $order_by ) {
                    $join->on_column( "order_answer.id", "order_answer_children.parent_id" )->on( "order_answer_children.field_name", $order_by[1] );
                }
            )->order_by( "order_answer_children.value", $dto->get_order() );
        } else {
            return $query->order_by( "order_answer.value", $dto->get_order() );
        }
    }

    public function create( ResponseDTO $dto ) {
        $id = Response::query()->insert_get_id( $dto->to_array() );

        do_action( 'formgent_after_create_response', $id, $dto );

        return $id;
    }

    public function get_by_id( int $id, $columns = ['response.*'] ) {
        return Response::query( 'response' )->select( $columns )->where( 'response.id', $id )->first();
    }

    public function get_count_by_form_id( int $form_id ) {
        return Response::query( 'response' )->where( 'response.form_id', $form_id )->where( 'response.status', ResponseStatus::PUBLISH )->count();
    }

    public function get_count_completed_by_form_id( int $form_id ) {
        return Response::query( 'response' )->where( 'response.form_id', $form_id )->where( 'response.status', ResponseStatus::PUBLISH )->where( 'response.is_completed', 1 )->count();
    }

    public function get_count_incompleted_by_form_id( int $form_id ) {
        return Response::query( 'response' )->where( 'response.form_id', $form_id )->where( 'response.status', ResponseStatus::PUBLISH )->where( 'response.is_completed', 0 )->count();
    }

    /**
     * Get total count of unread responses across all forms (published responses only).
     *
     * @return int
     */
    public function get_total_unread_count(): int {
        return (int) Response::query( 'response' )->where( 'response.status', ResponseStatus::PUBLISH )->where( 'response.is_read', 0 )->count();
    }

    public function get_single_with_pagination( ResponseSingleDTO $dto ) {
        $form = formgent_get_form_by_id( $dto->get_form_id() );

        if ( ! $form ) {
            throw new Exception( esc_html__( 'Form not found.', 'formgent' ), 404 );
        }

        $responses_query = $this->response_query( $dto );

        $count_query = clone $responses_query;

        do_action( 'formgent_responses_count_query', $count_query, $dto );

        $responses_query->with(
            'answers', function( Builder $query ){
                $query->select( 'id', 'response_id', 'field_name', 'field_type', 'value', 'created_at', 'updated_at' )->where_null( 'parent_id' )->where( 'field_type', '!=', 'gdpr' );
            }
        )->with(
            'answers.children', function( Builder $query ){
                $query->select( 'id', 'parent_id', 'field_name', 'field_type', 'value', 'created_at', 'updated_at' );
            }
        );

        $group_columns = ['response.id', 'response.form_id', 'response.is_read', 'response.is_starred', 'response.is_completed', 'response.device', 'response.browser', 'response.created_at'];

        $responses_query->select( $dto->get_columns() )->group_by( $group_columns );

        // If id is provided, filter by id directly (simpler and more reliable)
        if ( $dto->get_id() !== null ) {
            $responses_query->where( 'response.id', $dto->get_id() );
        } else {
            // Fall back to page-based approach (backward compatibility)
            $this->responses_order_query( $responses_query, $dto );
            do_action( 'formgent_responses_query', $responses_query, $dto );
            $responses = $responses_query->pagination( $dto->get_page(), 1, 1, 1 );
        }

        // If using id, get the response directly
        if ( $dto->get_id() !== null ) {
            do_action( 'formgent_responses_query', $responses_query, $dto );
            $responses = $responses_query->limit( 1 )->get();
        }

        if ( ! empty( $responses[0] ) ) {
            $responses[0] = $this->prepare_response_data( $responses[0], $form );
        }

        return [
            'total'     => $count_query->count( 'DISTINCT response.id' ),
            'responses' => $responses
        ];
    }

    public function get_single( ResponseSingleDTO $dto, int $response_id ) {
        $form = formgent_get_form_by_id( $dto->get_form_id() );

        if ( ! $form ) {
            throw new Exception( esc_html__( 'Form not found.', 'formgent' ), 404 );
        }

        $responses_query = $this->response_query( $dto );

        $this->single_response_query( $responses_query, $dto );

        do_action( 'formgent_responses_query', $responses_query, $dto );

        $response = $responses_query->where( 'response.id', $response_id )->first();

        // error_log(print_r($response, true));

        if ( ! empty( $response ) ) {
            return $this->prepare_response_data( $response, $form );
        }

        return $response;
    }

    public function single_response_query( Builder $responses_query, ResponseSingleDTO $dto ) {
        $group_columns = ['response.id', 'response.form_id', 'response.is_read', 'response.is_starred', 'response.is_completed', 'response.device', 'response.browser', 'response.created_at'];

        $responses_query->select( $dto->get_columns() )->with(
            'answers', function( Builder $query ){
                $query->select( 'id', 'response_id', 'field_name', 'field_type', 'value', 'created_at', 'updated_at' )->where_null( 'parent_id' )->where( 'field_type', '!=', 'gdpr' );
            }
        )->with(
            'answers.children', function( Builder $query ) {
                $query->select( 'id', 'parent_id', 'field_name', 'field_type', 'value', 'created_at', 'updated_at' );
            }
        )->group_by( $group_columns );
    }

    public function prepare_response_data( $response, $form ) {
        $data     = formgent_get_form_fields( $form );
        $response = apply_filters( 'formgent_response_item', $response );

        $response->answers = array_map(
            function( $answer ) use( $data ) {
                // Set label for the current answer if available
                $field_data    = $data[$answer->field_name] ?? [];
                $answer->label = $field_data['label'] ?? '';
                $answer        = $this->prepare_answer_data( $answer, $field_data );

                // Set labels for children if applicable
                if ( ! empty( $answer->children ) && ! empty( $field_data['children'] ) ) {
                    $children_data    = $field_data['children'];
                    $answer->children = array_map(
                        function( $children_answer ) use( $children_data ) {
                            $children_answer->label = $children_data[$children_answer->field_name]['label'] ?? '';
                            return $this->prepare_answer_data( $children_answer, $children_data[$children_answer->field_name] ?? [] );
                        },
                        $answer->children
                    );
                }

                return apply_filters( 'formgent_response_single_answer_field', $answer, $field_data );
            },
            $response->answers
        );

        return $response;
    }

    /**
     * Helper function to get option label by value.
     */
    protected function get_option_label( $field_data, $value ) {
        $option_keys = array_column( $field_data['options'] ?? [], 'value' );
        $option_key  = array_search( $value, $option_keys );
        if ( is_int( $option_key ) ) {
            return $field_data['options'][$option_key]['label'];
        }
        return '';
    }

    /**
     * Helper function to get text option label by value from text_options.
     */
    protected function get_text_option_label( $field_data, $value ) {
        if ( ! isset( $field_data['text_options'] ) || ! is_array( $field_data['text_options'] ) ) {
            return '';
        }

        foreach ( $field_data['text_options'] as $option ) {
            // Match by value first
            if ( isset( $option['value'] ) && $option['value'] === $value ) {
                return isset( $option['label'] ) ? $option['label'] : $value;
            }
            // Fallback: match by label (for backward compatibility)
            if ( isset( $option['label'] ) && $option['label'] === $value ) {
                return $option['label'];
            }
        }

        return '';
    }

    private function response_query( ResponseReadDTO $dto ) {
        $responses_query = Response::query( 'response' )->join( Post::get_table_name() . ' as post', 'post.ID', 'response.form_id' )->where( 'response.status', ResponseStatus::PUBLISH )->left_join( User::get_table_name() . ' as user', 'response.created_by', 'user.ID' );

        if ( $dto->get_is_completed() !== null ) {
            $responses_query->where( 'response.is_completed', '=', $dto->get_is_completed() );
        }

        // Apply date filtering
        $this->responses_date_query( $responses_query, $dto );

        if ( $dto->get_form_id() ) {
            $responses_query->where( 'post.ID', $dto->get_form_id() );
        }

        if ( $dto->get_is_read() !== null ) {
            $responses_query->where( 'response.is_read', $dto->get_is_read() );
        }

        if ( $dto->get_is_starred() !== null ) {
            $responses_query->where( 'response.is_starred', $dto->get_is_starred() );
        }

        $search = $dto->get_search();

        if ( empty( $search ) ) {
            return $responses_query;
        }

        $responses_query->left_join(
            Answer::get_table_name() . " as answer", function( JoinClause $join ) {
                $join->on_column( "response.id", "answer.response_id" );
            }
        );

        global $wpdb;

        $search = "%{$search}%";

        $search_query = $wpdb->prepare( "(post.post_title like %s or user.user_email like %s or answer.value like %s)", $search, $search, $search );

        return $responses_query->where_raw( $search_query );
    }

    public function update_starred( int $response_id, int $is_starred ) {
        return Response::query()->where( 'id', $response_id )->update( [ 'is_starred' => $is_starred ] );
    }

    public function update_read( int $response_id, int $is_read ) {
        return Response::query()->where( 'id', $response_id )->update( [ 'is_read' => $is_read ] );
    }

    /**
     * Update response timestamp.
     *
     * @param int $response_id
     * @return bool|int
     */
    public function update_response_timestamp( int $response_id ) {
        return Response::query()->where( 'id', $response_id )->update(
            [
                'updated_at' => formgent_now(),
            ]
        );
    }

    public function update_read_bulk( array $ids, int $is_read ) {
        return Response::query()
            ->where_in( 'id', $ids )
            ->update( [ 'is_read' => $is_read ] );
    }

    public function update_completed( int $response_id, int $is_completed ) {
        $data = [
            'is_completed' => $is_completed,
            'completed_at' => $is_completed ? formgent_now() : null,
        ];

        return Response::query()->where( 'id', $response_id )->update( $data );
    }

    public function get_export_count( int $form_id, array $response_ids = [] ): int {
        $query = Response::query()->where( 'form_id', $form_id )->where( 'status', 'publish' );

        if ( ! empty( $response_ids ) ) {
            $query->where_in( 'id', $response_ids );
        }

        return (int) $query->count();
    }

    public function get_export_data( int $form_id, array $response_ids = [], int $page = 1, int $per_page = 0 ) {
        $form = formgent_get_form_by_id( $form_id );
        if ( ! $form ) {
            return [];
        }

        $fields_data = formgent_get_form_fields( $form );

        $response_query = Response::query( 'response' )->with(
            'answers', function( Builder $query ) {
                $query->select( 'id', 'response_id', 'field_name', 'field_type', 'value' )->where_null( 'parent_id' );
            }
        )->with(
            'answers.children', function( Builder $query ) {
                $query->select( 'id', 'response_id', 'parent_id', 'field_name', 'field_type', 'value' );
            }
        )->where( 'form_id', $form_id )
        ->where( 'status', 'publish' );

        if ( ! empty( $response_ids ) ) {
            $response_query->where_in( 'id', $response_ids );
        }

        if ( $per_page > 0 ) {
            $offset = ( max( 1, $page ) - 1 ) * $per_page;
            $response_query->limit( $per_page )->offset( $offset );
        }

        $responses = $response_query->get();

        // Process each response to add option labels
        return array_map(
            function( $response ) use ( $fields_data ) {
                foreach ( $response->answers as $answer ) {
                    $field_data = $fields_data[ $answer->field_name ] ?? [];

                    // Process answers to add option_label for choice fields
                    if ( ! in_array( $answer->field_type, ['file-upload', 'signature', 'digital-signature'], true ) ) {
                        $this->prepare_field_value( $answer, $field_data );
                    }

                    // Process children for name/address fields
                    if ( ! empty( $answer->children ) && is_array( $answer->children ) ) {
                        foreach ( $answer->children as $child ) {
                            $child_field_data = $field_data['children'][ $child->field_name ] ?? [];
                            if ( ! in_array( $child->field_type, ['file-upload', 'signature', 'digital-signature'], true ) ) {
                                $this->prepare_field_value( $child, $child_field_data );
                            }
                        }
                    }
                }

                return $response;
            },
            $responses
        );
    }

    public function deletes( int $form_id, array $ids ) {
        return Response::query( 'response' )->where( 'form_id', $form_id )->where_in( 'id', $ids )->delete();
    }

    public function delete_by_ids( array $ids ) {
        if ( empty( $ids ) ) {
            return 0;
        }

        // Sanitize IDs to ensure they're integers
        $ids = array_map( 'intval', $ids );
        $ids = array_filter(
            $ids, function( $id ) {
                return $id > 0;
            }
        );

        if ( empty( $ids ) ) {
            return 0;
        }

        return Response::query()->where_in( 'id', $ids )->delete();
    }

    public function mark_as_completed( int $id ) {
        return Response::query()->where( 'id', $id )->update(
            [
                'is_completed' => 1,
                'completed_at' => formgent_now(),
                'status'       => ResponseStatus::PUBLISH,
            ]
        );
    }

    public function create_token( int $response_id, int $form_id ) {

        /**
         * Generating and storing token to identify the subsequent response on this response.
         */
        $token = 'response_token-' . formgent_generate_token();

        /**
         * @var ResponseTokenRepository $response_token_repository
         */
        $response_token_repository = formgent_singleton( ResponseTokenRepository::class );
        $response_token_repository->create( $form_id, $response_id, $token );

        return $token;
    }

    public function get_meta( int $response_id, string $meta_key ) {
        return ResponseMeta::query()->where( 'response_id', $response_id )->where( 'meta_key', $meta_key )->first();
    }

    public function get_meta_value( int $response_id, string $meta_key ) {
        $meta = $this->get_meta( $response_id, $meta_key );

        return $meta ? $meta->meta_value : null;
    }

    public function add_meta( int $response_id, string $meta_key, string $meta_value ) {
        $meta = $this->get_meta( $response_id, $meta_key );

        if ( $meta ) {
            return false;
        }

        return ResponseMeta::query()->insert(
            [
                'response_id' => $response_id,
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_key'    => $meta_key,
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                'meta_value'  => $meta_value,
            ]
        );
    }

    public function update_meta( int $response_id, string $meta_key, string $meta_value ) {
        $update = ResponseMeta::query()->where( 'response_id', $response_id )->where( 'meta_key', $meta_key )->update(
            [
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                'meta_value' => $meta_value,
            ]
        );

        if ( $update ) {
            return $update;
        }

        return ResponseMeta::query()->insert(
            [
                'response_id' => $response_id,
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_key'    => $meta_key,
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                'meta_value'  => $meta_value,
            ]
        );
    }

    public function delete_meta( int $response_id, string $meta_key ) {
        return ResponseMeta::query()->where( 'response_id', $response_id )->where( 'meta_key', $meta_key )->delete();
    }

    public function get_response_dto( int $id ): ?ResponseDTO {
        $response_data = $this->get_by_id( $id );

        if ( ! $response_data ) {
            return null;
        }

        $response_dto = new ResponseDTO();

        $response_dto->set_id( $response_data->id )
            ->set_form_id( $response_data->form_id )
            ->set_is_read( $response_data->is_read )
            ->set_ip( $response_data->ip )
            ->set_device( $response_data->device )
            ->set_browser( $response_data->browser )
            ->set_browser_version( $response_data->browser_version )
            ->set_created_at( $response_data->created_at )
            ->set_created_by( $response_data->created_by );

        return $response_dto;
    }
}