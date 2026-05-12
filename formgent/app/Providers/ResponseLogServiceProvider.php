<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\ResponseLogDTO;
use FormGent\App\Fields\Dropdown\Dropdown;
use FormGent\App\Fields\GoogleMap\GoogleMap;
use FormGent\App\Fields\MultipleChoice\MultipleChoice;
use FormGent\App\Fields\Repeater\Repeater;
use FormGent\App\Fields\SingleChoice\SingleChoice;
use FormGent\App\Repositories\AnswerRepository;
use FormGent\App\Repositories\ResponseLogRepository;
use FormGent\WpMVC\Contracts\Provider;
use WP_REST_Request;

class ResponseLogServiceProvider implements Provider {
    private static array $before_snapshots = [];

    public function boot(): void {
        add_action( 'formgent_before_update_response', [ $this, 'snapshot_before' ], 10, 3 );
        add_action( 'formgent_after_update_response',  [ $this, 'write_log' ],       10, 3 );
    }

    public function snapshot_before( int $response_id, \stdClass $form, WP_REST_Request $request ): void {
        self::$before_snapshots[ $response_id ] = $this->build_answer_map( $response_id );
    }

    public function write_log( int $response_id, \stdClass $form, WP_REST_Request $request ): void {
        $before = self::$before_snapshots[ $response_id ] ?? [];
        unset( self::$before_snapshots[ $response_id ] );

        $after   = $this->build_answer_map( $response_id );
        $changes = $this->diff( $before, $after, $form );

        if ( empty( $changes ) ) {
            return;
        }

        $user = wp_get_current_user();
        $dto  = ( new ResponseLogDTO )
            ->set_response_id( $response_id )
            ->set_action( 'entry_edited' )
            ->set_created_by( $user->exists() ? $user->ID : null )
            ->set_meta(
                wp_json_encode(
                    [
                        'actor_display_name' => $user->exists() ? sanitize_text_field( $user->display_name ) : 'Unknown',
                        'source'             => 'admin_edit_entry',
                        'changes'            => $changes,
                    ]
                )
            );

        formgent_singleton( ResponseLogRepository::class )->create( $dto );
    }

    private function build_answer_map( int $response_id ): array {
        $map     = [];
        $answers = formgent_singleton( AnswerRepository::class )->get( $response_id );

        foreach ( $answers as $answer ) {
            $map[ $answer->field_name ] = (string) ( $answer->value ?? '' );

            foreach ( $answer->children ?? [] as $child ) {
                $map[ $answer->field_name . '.' . $child->field_name ] = (string) ( $child->value ?? '' );
            }
        }

        return $map;
    }

    private function diff( array $before, array $after, \stdClass $form ): array {
        $sensitive_types = [ 'digital-signature', 'file-upload', 'stripe', 'paypal', 'payment-summary' ];
        $fields          = formgent_get_form_fields( $form );
        $changes         = [];
        $keys            = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );

        foreach ( $keys as $key ) {
            $old = $before[ $key ] ?? '';
            $new = $after[ $key ]  ?? '';

            if ( $old === $new ) {
                continue;
            }

            $dot_pos    = strpos( $key, '.' );
            $base_name  = false !== $dot_pos ? substr( $key, 0, $dot_pos ) : $key;
            $child_name = false !== $dot_pos ? substr( $key, $dot_pos + 1 ) : null;

            $field_data = $child_name
                ? ( $fields[ $base_name ]['children'][ $child_name ] ?? [] )
                : ( $fields[ $base_name ] ?? [] );
            $field_type = $field_data['field_type'] ?? $fields[ $base_name ]['field_type'] ?? '';

            if ( $child_name && isset( $fields[ $base_name ]['children'][ $child_name ]['label'] ) ) {
                $field_label = $fields[ $base_name ]['children'][ $child_name ]['label'];
            } else {
                $field_label = $fields[ $base_name ]['label'] ?? $base_name;
            }

            if ( in_array( $field_type, $sensitive_types, true ) ) {
                continue;
            }

            $changes[] = [
                'field_name'  => $key,
                'field_label' => sanitize_text_field( $field_label ),
                'old_display' => wp_strip_all_tags( $this->format_value_for_display( $old, $field_type, $field_data ) ),
                'new_display' => wp_strip_all_tags( $this->format_value_for_display( $new, $field_type, $field_data ) ),
            ];
        }

        return $changes;
    }

    /**
     * Format a raw stored value into a human-readable display string for entry logs.
     *
     * @param string $raw_value  Raw value from the database.
     * @param string $field_type Field type (dropdown, single-choice, multiple-choice, google-map, repeater, etc.).
     * @param array  $field_data Field settings (options, children, etc.).
     * @return string Human-readable display string.
     */
    private function format_value_for_display( string $raw_value, string $field_type, array $field_data ): string {
        if ( '' === $raw_value ) {
            return '';
        }

        switch ( $field_type ) {
            case Dropdown::get_key():
            case SingleChoice::get_key():
                $label = $this->get_option_label( $field_data, $raw_value );
                if ( '' !== $label ) {
                    return $label;
                }
                $allow_other = ! empty( $field_data['allow_user_add_other_option'] );
                return $allow_other ? $raw_value : '';

            case MultipleChoice::get_key():
                $values = json_decode( $raw_value, true );
                if ( ! is_array( $values ) ) {
                    return $raw_value;
                }
                $labels = array_map(
                    function ( $val ) use ( $field_data ) {
                        $label = $this->get_option_label( $field_data, $val );
                        return '' !== $label ? $label : $val;
                    },
                    $values
                );
                return implode( ', ', array_filter( $labels ) );

            case GoogleMap::get_key():
                $decoded = json_decode( $raw_value, true );
                if ( is_array( $decoded ) && isset( $decoded['address'] ) ) {
                    return $decoded['address'];
                }
                return $raw_value;

            case Repeater::get_key():
                return $this->format_repeater_for_display( $raw_value, $field_data );

            default:
                return $raw_value;
        }
    }

    /**
     * Get the option label for a given value from field options.
     */
    private function get_option_label( array $field_data, $value ): string {
        $options = $field_data['options'] ?? [];
        if ( empty( $options ) || ! is_array( $options ) ) {
            return '';
        }
        $option_keys = array_column( $options, 'value' );
        $idx         = array_search( $value, $option_keys );
        if ( false !== $idx && isset( $options[ $idx ]['label'] ) ) {
            return $options[ $idx ]['label'];
        }
        return '';
    }

    /**
     * Format repeater JSON value into a human-readable string (e.g., "Row 1: Label1: value1, Label2: value2; Row 2: ...").
     */
    private function format_repeater_for_display( string $raw_value, array $field_data ): string {
        $rows = json_decode( $raw_value, true );
        if ( ! is_array( $rows ) ) {
            return $raw_value;
        }

        $children = $field_data['children'] ?? [];
        $lines    = [];

        foreach ( $rows as $i => $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $parts = [];
            foreach ( $row as $child_key => $child_val ) {
                $child_field = $children[ $child_key ] ?? [];
                $label       = $child_field['label'] ?? $child_key;
                $child_type  = $child_field['field_type'] ?? 'text';
                $val         = $this->format_repeater_child_value( $child_val, $child_type, $child_field );
                $parts[]     = $label . ': ' . $val;
            }
            $row_num = $i + 1;
            $lines[] = sprintf(
                /* translators: %d: row number */
                __( 'Row %d', 'formgent' ) . ': ' . implode( ', ', $parts ),
                $row_num
            );
        }

        return implode( '; ', $lines );
    }

    /**
     * Format a single repeater child value (e.g., choice fields use label instead of key).
     */
    private function format_repeater_child_value( $value, string $field_type, array $field_data ): string {
        if ( null === $value || '' === $value ) {
            return '';
        }
        if ( ! is_scalar( $value ) ) {
            return wp_json_encode( $value );
        }
        $str = (string) $value;

        if ( Dropdown::get_key() === $field_type || SingleChoice::get_key() === $field_type ) {
            $label = $this->get_option_label( $field_data, $str );
            return '' !== $label ? $label : $str;
        }
        if ( MultipleChoice::get_key() === $field_type ) {
            $arr = is_array( $value ) ? $value : json_decode( $str, true );
            if ( is_array( $arr ) ) {
                $labels = array_map(
                    function ( $v ) use ( $field_data ) {
                        $label = $this->get_option_label( $field_data, $v );
                        return '' !== $label ? $label : $v;
                    },
                    $arr
                );
                return implode( ', ', array_filter( $labels ) );
            }
        }
        if ( GoogleMap::get_key() === $field_type ) {
            $decoded = json_decode( $str, true );
            if ( is_array( $decoded ) && isset( $decoded['address'] ) ) {
                return $decoded['address'];
            }
        }

        return $str;
    }
}
