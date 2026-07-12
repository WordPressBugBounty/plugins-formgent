<?php
namespace FormGent\App\Utils;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerFieldDTO;

/**
 * Resolve field references to display-ready values.
 *
 * All public resolve_* methods return HTML-safe output that is ready to be
 * inserted into an HTML template (PDF, email, confirmation page).
 */
class FieldValueResolver {
    /**
     * Resolve a field reference using front-end form data (context.data).
     *
     * @param array  $form_data    Reactive form data array.
     * @param array  $form_fields  Parsed form field definitions.
     * @param string $reference    Field reference (e.g. "email" or "group.child").
     *
     * @return string|null
     */
    public function resolve_from_form_data( array $form_data, array $form_fields, string $reference ): ?string {
        $value = $this->traverse_array( $form_data, $reference );

        if ( null === $value ) {
            return null;
        }

        $field_definition = $this->locate_field_definition( $form_fields, $reference );

        return $this->format_value( $value, $field_definition );
    }

    /**
     * Resolve a field reference using AnswerFieldDTOs.
     *
     * @param AnswerFieldDTO[] $answers       Flattened answers array.
     * @param array            $form_fields   Parsed form field definitions.
     * @param string           $reference     Field reference.
     *
     * @return string|null
     */
    public function resolve_from_answers( array $answers, array $form_fields, string $reference ): ?string {
        $segments = explode( '.', $reference );

        // Handle nested field references (e.g., "name.first_name", "address.city",
        // or "repeater.repeater_text").
        if ( count( $segments ) > 1 ) {
            $parent_name = $segments[0];
            $parent_def  = isset( $form_fields[ $parent_name ] ) ? $form_fields[ $parent_name ] : [];

            // If the parent is a repeater, resolve from the parent's JSON value
            // so that ALL entries are shown, not just the last flattened child.
            if ( isset( $parent_def['field_type'] ) && 'repeater' === $parent_def['field_type'] ) {
                $parent_dto = $this->find_answer_by_field_name( $answers, $parent_name );

                if ( null === $parent_dto ) {
                    return null;
                }

                return $this->format_repeater( $parent_dto->get_value(), $parent_def );
            }

            // For non-repeater nested refs, find the last segment in the flattened array.
            $last_segment = end( $segments );
            $answer_dto   = $this->find_answer_by_field_name( $answers, $last_segment );

            if ( null === $answer_dto ) {
                return null;
            }

            $value            = $answer_dto->get_value();
            $field_definition = $this->locate_field_definition( $form_fields, $reference );

            return $this->format_value( $value, $field_definition );
        }

        // Handle simple field references (e.g., "email")
        $answer_dto = $this->find_answer_by_field_name( $answers, $segments[0] );

        if ( null === $answer_dto ) {
            return null;
        }

        $value            = $answer_dto->get_value();
        $field_definition = $this->locate_field_definition( $form_fields, $reference );

        return $this->format_value( $value, $field_definition );
    }

    /**
     * Find an answer DTO by field name from a flattened answers array.
     *
     * @param AnswerFieldDTO[] $answers     Flattened answers array.
     * @param string           $field_name  Field name to search for.
     *
     * @return AnswerFieldDTO|null
     */
    private function find_answer_by_field_name( array $answers, string $field_name ): ?AnswerFieldDTO {
        foreach ( $answers as $answer_dto ) {
            if ( $answer_dto instanceof AnswerFieldDTO && $answer_dto->get_field_name() === $field_name ) {
                return $answer_dto;
            }
        }
        return null;
    }

    /**
     * Locate form field definition by reference.
     *
     * @param array  $form_fields Parsed form field definitions.
     * @param string $reference   Field reference.
     *
     * @return array
     */
    private function locate_field_definition( array $form_fields, string $reference ): array {
        $segments = explode( '.', $reference );
        $field    = $form_fields;

        foreach ( $segments as $segment ) {
            if ( isset( $field[ $segment ] ) ) {
                $field = $field[ $segment ];
                continue;
            }

            if ( isset( $field['children'][ $segment ] ) ) {
                $field = $field['children'][ $segment ];
                continue;
            }

            return [];
        }

        return is_array( $field ) ? $field : [];
    }

    /**
     * Traverse nested array using dot notation.
     */
    private function traverse_array( array $data, string $reference ) {
        $segments = explode( '.', $reference );
        $value    = $data;

        foreach ( $segments as $segment ) {
            if ( is_array( $value ) && array_key_exists( $segment, $value ) ) {
                $value = $value[ $segment ];
                continue;
            }

            return null;
        }

        return $value;
    }

    /**
     * Ensure a value is a PHP array, decoding JSON if needed.
     *
     * @param mixed $value
     *
     * @return array
     */
    private function ensure_array( $value ): array {
        if ( is_array( $value ) ) {
            return $value;
        }

        $decoded = json_decode( (string) $value, true );

        return is_array( $decoded ) ? $decoded : [];
    }

    /**
     * Format raw value according to field type.
     *
     * Returns HTML-safe output. Plain text values are escaped with esc_html(),
     * while rich types (file links, images, tables) return sanitized HTML.
     *
     * @param mixed $value
     * @param array $definition
     *
     * @return string
     */
    private function format_value( $value, array $definition ): string {
        if ( ! isset( $definition['field_type'] ) ) {
            return esc_html( (string) $value );
        }

        switch ( $definition['field_type'] ) {
            case 'single-choice':
                return esc_html( $this->resolve_choice_label( $definition, $value ) );

            case 'dropdown':
                if ( ! empty( $definition['allow_multi_select'] ) ) {
                    return $this->format_multiple_choice( $value, $definition );
                }

                return esc_html( $this->resolve_choice_label( $definition, $value ) );

            case 'multiple-choice':
                return $this->format_multiple_choice( $value, $definition );

            case 'google-map':
                return $this->format_google_map( $value );

            case 'file-upload':
                return $this->format_file_upload( $value );

            case 'rating':
                $max = isset( $definition['rating_limit'] ) ? absint( $definition['rating_limit'] ) : 5;
                return esc_html( sprintf( '%s out of %s', (string) $value, (string) $max ) );

            case 'digital-signature':
            case 'signature':
                return $this->format_signature( $value );

            case 'repeater':
                return $this->format_repeater( $value, $definition );

            case 'number':
                return esc_html( (string) $value );

            default:
                return esc_html( (string) $value );
        }
    }

    /**
     * Format multiple-choice values as comma-separated labels.
     */
    private function format_multiple_choice( $value, array $definition ): string {
        $items = is_array( $value ) ? $value : $this->ensure_array( $value );

        if ( empty( $items ) ) {
            return esc_html( (string) $value );
        }

        $labels = array_map(
            function ( $item ) use ( $definition ) {
                return $this->resolve_choice_label( $definition, $item );
            },
            $items
        );

        return esc_html( implode( ', ', array_filter( $labels ) ) );
    }

    /**
     * Format google-map value as a linked address pointing to Google Maps.
     */
    private function format_google_map( $value ): string {
        $map_data = is_array( $value ) ? $value : json_decode( (string) $value, true );

        if ( ! is_array( $map_data ) ) {
            return esc_html( (string) $value );
        }

        $address = isset( $map_data['address'] ) ? trim( (string) $map_data['address'] ) : '';

        if ( '' === $address ) {
            return '';
        }

        $lat = isset( $map_data['map']['lat'] ) ? $map_data['map']['lat'] : '';
        $lng = isset( $map_data['map']['lng'] ) ? $map_data['map']['lng'] : '';

        $maps_url = 'https://www.google.com/maps/place/' . rawurlencode( $address );

        if ( '' !== $lat && '' !== $lng && is_numeric( $lat ) && is_numeric( $lng ) ) {
            $maps_url .= '/@' . (float) $lat . ',' . (float) $lng;
        }

        return '<a href="' . esc_url( $maps_url ) . '" style="color:#2563EB;text-decoration:underline;">' . esc_html( $address ) . '</a>';
    }

    /**
     * Format file-upload value as clickable HTML links.
     */
    private function format_file_upload( $value ): string {
        $files      = is_array( $value ) ? $value : $this->ensure_array( $value );
        $upload_dir = wp_get_upload_dir();
        $links      = [];

        foreach ( $files as $file ) {
            $file = trim( (string) $file );

            if ( '' === $file ) {
                continue;
            }

            if ( 0 === strpos( $file, 'http://' ) || 0 === strpos( $file, 'https://' ) ) {
                $url = $file;
            } else {
                $url = trailingslashit( $upload_dir['baseurl'] ) . $file;
            }

            $name    = basename( $file );
            $links[] = '<a href="' . esc_url( $url ) . '" style="color:#2563EB;text-decoration:underline;">' . esc_html( $name ) . '</a>';
        }

        return implode( '<br>', $links );
    }

    /**
     * Format digital-signature/signature as an embedded image.
     *
     * Uses a local file path so Dompdf can read the image directly
     * without making an HTTP request (which can fail due to SSL, loopback, etc.).
     */
    private function format_signature( $value ): string {
        $file = trim( (string) $value );

        if ( '' === $file ) {
            return '';
        }

        // If value is already an absolute URL, try to convert to a local path.
        if ( 0 === strpos( $file, 'http://' ) || 0 === strpos( $file, 'https://' ) ) {
            $upload_dir = wp_get_upload_dir();
            // Strip the base URL to get a relative path, then build the local path.
            $relative = str_replace( trailingslashit( $upload_dir['baseurl'] ), '', $file );
            $local    = trailingslashit( $upload_dir['basedir'] ) . $relative;

            if ( file_exists( $local ) ) {
                $file = $relative;
            } else {
                // Fallback to URL if local conversion fails.
                return '<img src="' . esc_url( $file ) . '" style="max-width:220px;max-height:110px;" />';
            }
        }

        // Build local filesystem path for Dompdf (normalized to match chroot).
        $upload_dir = wp_get_upload_dir();
        $local_path = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $file );

        if ( ! file_exists( $local_path ) ) {
            return '';
        }

        return '<img src="' . esc_attr( $local_path ) . '" style="max-width:220px;max-height:110px;" />';
    }

    /**
     * Format repeater value as an HTML table with all entries.
     */
    private function format_repeater( $value, array $definition ): string {
        $entries = is_array( $value ) ? $value : json_decode( (string) $value, true );

        if ( ! is_array( $entries ) || empty( $entries ) ) {
            return '';
        }

        $children   = isset( $definition['children'] ) ? $definition['children'] : [];
        $first_item = $entries[0];

        if ( ! is_array( $first_item ) ) {
            return '';
        }

        $columns = array_keys( $first_item );

        // Build header row using child field labels from definition.
        $header_cells = '';
        foreach ( $columns as $col ) {
            $label = $col;

            if ( isset( $children[ $col ]['label'] ) ) {
                $label = $children[ $col ]['label'];
            }

            $header_cells .= '<th style="padding:8px 12px;text-align:left;font-size:12px;font-weight:700;color:#64748b;letter-spacing:0.5px;border:1px solid #e2e8f0;">' . esc_html( $label ) . '</th>';
        }

        // Build body rows.
        $body_rows = '';
        foreach ( $entries as $entry ) {
            if ( ! is_array( $entry ) ) {
                continue;
            }

            $cells = '';
            foreach ( $columns as $col ) {
                $cell_value      = isset( $entry[ $col ] ) ? $entry[ $col ] : '';
                $child_def       = isset( $children[ $col ] ) ? $children[ $col ] : [];
                $formatted_value = $this->format_value( $cell_value, $child_def );

                $cells .= '<td style="padding:8px 12px;font-size:13px;border:1px solid #e2e8f0;">' . $formatted_value . '</td>';
            }

            $body_rows .= '<tr>' . $cells . '</tr>';
        }

        return '<table style="width:100%;border-collapse:collapse;margin:4px 0;"><thead><tr style="background:#f1f5f9;">' . $header_cells . '</tr></thead><tbody>' . $body_rows . '</tbody></table>';
    }

    /**
     * Map option value to its label.
     */
    private function resolve_choice_label( array $definition, $value ): string {
        if ( empty( $definition['options'] ) ) {
            return (string) $value;
        }

        foreach ( $definition['options'] as $option ) {
            if ( isset( $option['value'] ) && $option['value'] == $value ) {
                return isset( $option['label'] ) ? (string) $option['label'] : (string) $value;
            }
        }

        return (string) $value;
    }
}
