<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Error;

/**
 * Projects per-form metadata onto the explicit non-secret MCP contract.
 */
class SafeFormSettingsService {
    private const APPEARANCE_KEYS = [
        'use_label_as_placeholder',
        'show_page_break_labels',
        'page_break_progress_indicator',
        'submit_button_alignment',
        'submit_button_style',
        'next_button_style',
        'back_button_style',
        'submit_button_background_color',
        'submit_button_border_color',
        'submit_button_text_color',
        'next_button_bg_color',
        'next_button_border_color',
        'next_button_text_color',
        'back_button_bg_color',
        'back_button_border_color',
        'back_button_text_color',
        'form_background',
        'form_background_color',
        'form_background_image',
        'form_padding',
        'form_margin',
        'form_border',
        'field_vertical_spacing',
        'field_horizontal_spacing',
        'field_colors',
        'field_border',
        'submit_button_disabled',
        'submit_button_label',
    ];

    private const DESIGN_KEYS = [
        'status',
        'show_cover',
        'cover',
        'cover_type',
        'is_cover_bg',
        'show_logo',
        'logo',
        'show_title',
        'title',
        'width',
    ];

    /** @return array<string,mixed> */
    public function get( int $form_id ): array {
        $settings      = get_post_meta( $form_id, '_formgent_settings', true );
        $form_settings = get_post_meta( $form_id, '_formgent_form_settings', true );
        $settings      = is_array( $settings ) ? $settings : [];
        $form_settings = is_array( $form_settings ) ? $form_settings : [];
        $confirmation  = isset( $settings['confirmation'] ) && is_array( $settings['confirmation'] ) ? $settings['confirmation'] : [];
        $quiz          = isset( $settings['quiz'] ) && is_array( $settings['quiz'] ) ? $settings['quiz'] : [];

        $message = is_string( $confirmation['message'] ?? null ) ? wp_kses_post( $confirmation['message'] ) : esc_html__( 'Thank you! Your submission has been received.', 'formgent' );
        $url     = is_string( $confirmation['url'] ?? null ) ? esc_url_raw( $confirmation['url'] ) : '';

        $public = [
            'behavior'     => [
                'save_incompleted_data'  => $this->yes_no( $settings['save_incompleted_data'] ?? 'no' ),
                'hide_formgent_branding' => $this->yes_no( $settings['hide_formgent_branding'] ?? 'no' ),
            ],
            'confirmation' => [
                'type'             => in_array( $confirmation['type'] ?? '', ['message', 'page', 'url'], true ) ? $confirmation['type'] : 'message',
                'message'          => substr( $message, 0, 10000 ),
                'page'             => absint( $confirmation['page'] ?? 0 ),
                'url'              => substr( $url, 0, 2048 ),
                'after_submission' => in_array( $confirmation['after_submission'] ?? '', ['reset', 'hide'], true ) ? $confirmation['after_submission'] : 'reset',
            ],
            'quiz'         => [
                'is_enabled'         => ! empty( $quiz['is_enabled'] ),
                'show_results'       => ! isset( $quiz['show_results'] ) || ! empty( $quiz['show_results'] ),
                'is_grading_enabled' => ! isset( $quiz['is_grading_enabled'] ) || ! empty( $quiz['is_grading_enabled'] ),
                'score_text'         => substr( sanitize_text_field( $quiz['score_text'] ?? '' ), 0, 1000 ),
                'grades'             => $this->grades( $quiz['grades'] ?? [] ),
            ],
            'appearance'   => $this->appearance( $form_settings ),
            'design'       => $this->design( is_array( $settings['design'] ?? null ) ? $settings['design'] : [] ),
        ];

        if ( current_user_can( 'unfiltered_html' ) ) {
            $public['custom_code'] = [
                'css' => is_string( $settings['customScript']['css'] ?? null ) ? substr( $settings['customScript']['css'], 0, 100000 ) : '',
                'js'  => is_string( $settings['customScript']['js'] ?? null ) ? substr( $settings['customScript']['js'], 0, 100000 ) : '',
            ];
        }

        /**
         * Lets entitled extensions append non-secret per-form settings.
         *
         * @param array<string,mixed> $public Safe public settings.
         * @param array<string,mixed> $settings Stored general settings.
         * @param array<string,mixed> $form_settings Stored appearance settings.
         * @param int $form_id Form ID.
         */
        $public = apply_filters( 'formgent_mcp_read_form_settings', $public, $settings, $form_settings, $form_id );

        return is_array( $public ) ? $public : [];
    }

    /**
     * Validate a complete patch before any post metadata is changed.
     *
     * @param array<string,mixed> $patch Public settings patch.
     * @return array<string,mixed>|WP_Error
     */
    public function prepare( int $form_id, array $patch ) {
        $core_groups  = ['behavior', 'confirmation', 'quiz', 'appearance', 'design', 'custom_code'];
        $known_groups = apply_filters( 'formgent_mcp_form_setting_groups', $core_groups );

        if ( ! is_array( $known_groups ) || ! empty( array_diff( array_keys( $patch ), $known_groups ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'An unknown form setting group was provided.', 'formgent' ) );
        }

        // Empty core objects are no-ops. Extension list groups (for example an
        // empty conditional-confirmation list) must remain able to clear data.
        foreach ( $core_groups as $group ) {
            if ( isset( $patch[$group] ) && [] === $patch[$group] ) {
                unset( $patch[$group] );
            }
        }

        $settings      = 0 < $form_id ? get_post_meta( $form_id, '_formgent_settings', true ) : [];
        $form_settings = 0 < $form_id ? get_post_meta( $form_id, '_formgent_form_settings', true ) : [];
        $settings      = is_array( $settings ) ? $settings : [];
        $form_settings = is_array( $form_settings ) ? $form_settings : [];
        $warnings      = [];

        if ( isset( $patch['behavior'] ) ) {
            $behavior = $patch['behavior'];

            if ( ! is_array( $behavior ) || empty( $behavior ) || ! empty( array_diff( array_keys( $behavior ), ['save_incompleted_data', 'hide_formgent_branding'] ) ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The form behavior settings are invalid.', 'formgent' ) );
            }

            foreach ( $behavior as $key => $value ) {
                if ( ! in_array( $value, ['yes', 'no'], true ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'The form behavior settings are invalid.', 'formgent' ) );
                }

                $settings[$key] = $value;
            }
        }

        if ( isset( $patch['confirmation'] ) ) {
            $confirmation = $this->confirmation( $settings['confirmation'] ?? [], $patch['confirmation'], $warnings );

            if ( is_wp_error( $confirmation ) ) {
                return $confirmation;
            }

            $settings['confirmation'] = $confirmation;
        }

        if ( isset( $patch['quiz'] ) ) {
            $quiz = $this->quiz( $settings['quiz'] ?? [], $patch['quiz'] );

            if ( is_wp_error( $quiz ) ) {
                return $quiz;
            }

            $settings['quiz'] = $quiz;
        }

        if ( isset( $patch['appearance'] ) ) {
            $appearance = $this->prepare_appearance( $patch['appearance'] );

            if ( is_wp_error( $appearance ) ) {
                return $appearance;
            }

            $form_settings = array_merge( $form_settings, $appearance );
        }

        if ( isset( $patch['design'] ) ) {
            $design = $this->prepare_design( $patch['design'] );

            if ( is_wp_error( $design ) ) {
                return $design;
            }

            $settings['design'] = array_merge( is_array( $settings['design'] ?? null ) ? $settings['design'] : [], $design );
        }

        if ( isset( $patch['custom_code'] ) ) {
            $custom_code = $patch['custom_code'];

            if ( ! is_array( $custom_code ) || empty( $custom_code ) || ! empty( array_diff( array_keys( $custom_code ), ['css', 'js'] ) ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The custom CSS/JS settings are invalid.', 'formgent' ) );
            }

            $settings['customScript'] = [
                'css' => isset( $custom_code['css'] ) ? substr( (string) $custom_code['css'], 0, 100000 ) : (string) ( $settings['customScript']['css'] ?? '' ),
                'js'  => isset( $custom_code['js'] ) ? substr( (string) $custom_code['js'], 0, 100000 ) : (string) ( $settings['customScript']['js'] ?? '' ),
            ];
        }

        /**
         * Lets extensions validate and merge entitled non-secret setting groups.
         * A filter may return WP_Error to fail the complete atomic patch.
         *
         * @param array<string,mixed> $prepared Core prepared values.
         * @param array<string,mixed> $patch Original public patch.
         * @param int $form_id Form ID.
         */
        $prepared = apply_filters(
            'formgent_mcp_prepare_form_settings',
            [
                'settings'      => $settings,
                'form_settings' => $form_settings,
                'warnings'      => $warnings,
            ],
            $patch,
            $form_id
        );

        if ( is_wp_error( $prepared ) ) {
            return $prepared;
        }

        return is_array( $prepared ) ? $prepared : McpErrorFactory::internal();
    }

    /** @param array<string,mixed> $prepared Prepared result from prepare(). */
    public function save( int $form_id, array $prepared ): bool {
        return $this->save_meta( $form_id, '_formgent_settings', $prepared['settings'] ?? [] )
            && $this->save_meta( $form_id, '_formgent_form_settings', $prepared['form_settings'] ?? [] );
    }

    /** @param mixed $value Prepared metadata value. */
    private function save_meta( int $form_id, string $key, $value ): bool {
        if ( get_post_meta( $form_id, $key, true ) === $value ) {
            return true;
        }

        return false !== update_post_meta( $form_id, $key, $value );
    }

    /** @param mixed $value Stored value. */
    private function yes_no( $value ): string {
        return 'yes' === $value || true === $value || 1 === $value || '1' === $value ? 'yes' : 'no';
    }

    /** @param mixed $grades Stored grades. @return array<int,array<string,mixed>> */
    private function grades( $grades ): array {
        if ( ! is_array( $grades ) ) {
            return [];
        }

        $safe = [];

        foreach ( array_slice( $grades, 0, 20 ) as $index => $grade ) {
            if ( ! is_array( $grade ) ) {
                continue;
            }

            $min   = min( 100, max( 0, (int) ( $grade['min'] ?? 0 ) ) );
            $max   = min( 100, max( $min, (int) ( $grade['max'] ?? 100 ) ) );
            $label = is_string( $grade['label'] ?? null ) ? substr( sanitize_text_field( $grade['label'] ), 0, 50 ) : '';
            $id    = is_string( $grade['id'] ?? null ) ? substr( sanitize_key( $grade['id'] ), 0, 64 ) : '';

            $safe[] = [
                'id'    => $id,
                'label' => '' !== $label ? $label : sprintf( esc_html__( 'Grade %d', 'formgent' ), $index + 1 ),
                'min'   => $min,
                'max'   => $max,
            ];
        }

        return $safe;
    }

    /** @param array<string,mixed> $settings Stored appearance. @return array<string,mixed> */
    private function appearance( array $settings ): array {
        $settings   = array_intersect_key( $settings, array_flip( self::APPEARANCE_KEYS ) );
        $normalized = [];

        foreach ( ['use_label_as_placeholder', 'show_page_break_labels', 'submit_button_disabled'] as $key ) {
            if ( array_key_exists( $key, $settings ) ) {
                $normalized[$key] = ! empty( $settings[$key] );
            }
        }

        $enums = [
            'page_break_progress_indicator' => ['none', 'progress_bar', 'steps'],
            'submit_button_alignment'       => ['left', 'middle', 'right', 'block'],
            'submit_button_style'           => ['default', 'solid', 'bordered'],
            'next_button_style'             => ['default', 'solid', 'bordered'],
            'back_button_style'             => ['default', 'solid', 'bordered'],
        ];

        foreach ( $enums as $key => $values ) {
            if ( isset( $settings[$key] ) && in_array( $settings[$key], $values, true ) ) {
                $normalized[$key] = $settings[$key];
            }
        }

        $color_keys = [
            'submit_button_background_color',
            'submit_button_border_color',
            'submit_button_text_color',
            'next_button_bg_color',
            'next_button_border_color',
            'next_button_text_color',
            'back_button_bg_color',
            'back_button_border_color',
            'back_button_text_color',
            'form_background_color',
        ];

        foreach ( $color_keys as $key ) {
            if ( isset( $settings[$key] ) ) {
                $color = is_string( $settings[$key] ) ? sanitize_hex_color( $settings[$key] ) : null;

                if ( $color ) {
                    $normalized[$key] = $color;
                }
            }
        }

        foreach ( array_diff( self::APPEARANCE_KEYS, array_merge( array_keys( $enums ), ['use_label_as_placeholder', 'show_page_break_labels', 'submit_button_disabled'], $color_keys ) ) as $key ) {
            if ( array_key_exists( $key, $settings ) ) {
                $normalized[$key] = $this->sanitize_tree( $settings[$key], $key );
            }
        }

        return $normalized;
    }

    /**
     * @param mixed $stored Stored confirmation.
     * @param mixed $patch Confirmation patch.
     * @param array<int,string> $warnings Output warnings.
     * @return array<string,mixed>|WP_Error
     */
    private function confirmation( $stored, $patch, array &$warnings ) {
        if ( ! is_array( $patch ) || empty( $patch ) || ! empty( array_diff( array_keys( $patch ), ['type', 'message', 'page', 'url', 'after_submission'] ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The confirmation settings are invalid.', 'formgent' ) );
        }

        $confirmation = array_merge(
            [
                'type'             => 'message',
                'message'          => esc_html__( 'Thank you! Your submission has been received.', 'formgent' ),
                'page'             => 0,
                'url'              => '',
                'after_submission' => 'reset',
            ],
            is_array( $stored ) ? $stored : [],
            $patch
        );

        if ( ! in_array( $confirmation['type'], ['message', 'page', 'url'], true ) || ! in_array( $confirmation['after_submission'], ['reset', 'hide'], true ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The confirmation type or post-submit behavior is invalid.', 'formgent' ) );
        }

        if ( isset( $patch['message'] ) ) {
            if ( ! is_string( $patch['message'] ) || 10000 < strlen( $patch['message'] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The confirmation message is invalid.', 'formgent' ) );
            }

            $confirmation['message'] = wp_kses_post( $patch['message'] );
        }

        if ( 'message' === $confirmation['type'] && '' === trim( wp_strip_all_tags( $confirmation['message'] ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A message confirmation requires a message.', 'formgent' ) );
        }

        $confirmation['page'] = absint( $confirmation['page'] );

        if ( 'page' === $confirmation['type'] && ( 'page' !== get_post_type( $confirmation['page'] ) || 'publish' !== get_post_status( $confirmation['page'] ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A page confirmation requires a published page.', 'formgent' ) );
        }

        if ( isset( $patch['url'] ) ) {
            if ( ! is_string( $patch['url'] ) || 2048 < strlen( $patch['url'] ) || ( '' !== $patch['url'] && ! wp_http_validate_url( $patch['url'] ) ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The confirmation URL must be a valid HTTP or HTTPS URL.', 'formgent' ) );
            }

            $confirmation['url'] = esc_url_raw( $patch['url'] );
        }

        if ( 'url' === $confirmation['type'] && ! wp_http_validate_url( $confirmation['url'] ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A URL confirmation requires a valid URL.', 'formgent' ) );
        }

        if ( 'url' === $confirmation['type'] && wp_parse_url( home_url(), PHP_URL_HOST ) !== wp_parse_url( $confirmation['url'], PHP_URL_HOST ) ) {
            $warnings[] = esc_html__( 'The confirmation redirects to an external host.', 'formgent' );
        }

        return $confirmation;
    }

    /** @param mixed $stored Stored quiz. @param mixed $patch Quiz patch. @return array<string,mixed>|WP_Error */
    private function quiz( $stored, $patch ) {
        $allowed = ['is_enabled', 'show_results', 'is_grading_enabled', 'score_text', 'grades'];

        if ( ! is_array( $patch ) || empty( $patch ) || ! empty( array_diff( array_keys( $patch ), $allowed ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The quiz settings are invalid.', 'formgent' ) );
        }

        foreach ( ['is_enabled', 'show_results', 'is_grading_enabled'] as $key ) {
            if ( isset( $patch[$key] ) && ! is_bool( $patch[$key] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Quiz state settings must be boolean.', 'formgent' ) );
            }
        }

        $quiz = array_merge( is_array( $stored ) ? $stored : [], $patch );

        if ( isset( $patch['score_text'] ) ) {
            if ( ! is_string( $patch['score_text'] ) || 1000 < strlen( $patch['score_text'] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The quiz score text is invalid.', 'formgent' ) );
            }

            $quiz['score_text'] = sanitize_text_field( $patch['score_text'] );
        }

        if ( isset( $patch['grades'] ) ) {
            $grades = $this->prepare_grades( $patch['grades'] );

            if ( is_wp_error( $grades ) ) {
                return $grades;
            }

            $quiz['grades'] = $grades;
        }

        return $quiz;
    }

    /** @param mixed $grades Candidate grades. @return array<int,array<string,mixed>>|WP_Error */
    private function prepare_grades( $grades ) {
        if ( ! is_array( $grades ) || 20 < count( $grades ) ) {
            return McpErrorFactory::limit_exceeded( esc_html__( 'A quiz can contain at most 20 grades.', 'formgent' ) );
        }

        $prepared = [];
        $labels   = [];

        foreach ( $grades as $grade ) {
            if ( ! is_array( $grade ) || ! empty( array_diff( array_keys( $grade ), ['id', 'label', 'min', 'max'] ) ) || ! isset( $grade['label'], $grade['min'], $grade['max'] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A quiz grade is invalid.', 'formgent' ) );
            }

            $label = sanitize_text_field( $grade['label'] );
            $min   = is_int( $grade['min'] ) ? $grade['min'] : -1;
            $max   = is_int( $grade['max'] ) ? $grade['max'] : -1;

            if ( '' === $label || 50 < strlen( $label ) || isset( $labels[$label] ) || 0 > $min || 100 < $max || $min > $max ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Quiz grade labels and ranges must be unique and valid.', 'formgent' ) );
            }

            $labels[$label] = true;
            $prepared[]     = [
                'id'    => sanitize_key( $grade['id'] ?? substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 12 ) ),
                'label' => $label,
                'min'   => $min,
                'max'   => $max,
            ];
        }

        usort( $prepared, static fn( array $left, array $right ): int => $left['min'] <=> $right['min'] );

        for ( $i = 1; $i < count( $prepared ); $i++ ) {
            if ( $prepared[$i]['min'] <= $prepared[$i - 1]['max'] ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Quiz grade ranges cannot overlap.', 'formgent' ) );
            }
        }

        return $prepared;
    }

    /** @param mixed $patch Appearance patch. @return array<string,mixed>|WP_Error */
    private function prepare_appearance( $patch ) {
        if ( ! is_array( $patch ) || empty( $patch ) || ! empty( array_diff( array_keys( $patch ), self::APPEARANCE_KEYS ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The appearance settings are invalid.', 'formgent' ) );
        }

        $prepared = [];
        $enums    = [
            'page_break_progress_indicator' => ['none', 'progress_bar', 'steps'],
            'submit_button_alignment'       => ['left', 'middle', 'right', 'block'],
            'submit_button_style'           => ['default', 'solid', 'bordered'],
            'next_button_style'             => ['default', 'solid', 'bordered'],
            'back_button_style'             => ['default', 'solid', 'bordered'],
        ];

        foreach ( $patch as $key => $value ) {
            if ( in_array( $key, ['use_label_as_placeholder', 'show_page_break_labels', 'submit_button_disabled'], true ) ) {
                if ( ! is_bool( $value ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'Appearance switches must be boolean.', 'formgent' ) );
                }

                $prepared[$key] = $value;
            } elseif ( isset( $enums[$key] ) ) {
                if ( ! in_array( $value, $enums[$key], true ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'An appearance option is invalid.', 'formgent' ) );
                }

                $prepared[$key] = $value;
            } elseif ( in_array(
                $key, [
                    'submit_button_background_color',
                    'submit_button_border_color',
                    'submit_button_text_color',
                    'next_button_bg_color',
                    'next_button_border_color',
                    'next_button_text_color',
                    'back_button_bg_color',
                    'back_button_border_color',
                    'back_button_text_color',
                    'form_background_color',
                ], true
            ) ) {
                $color = is_string( $value ) ? sanitize_hex_color( $value ) : null;

                if ( ! $color || 7 !== strlen( $color ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'Appearance colors must use six-digit hexadecimal values.', 'formgent' ) );
                }

                $prepared[$key] = $color;
            } elseif ( 'form_background' === $key ) {
                if ( ! in_array( $value, ['color', 'image'], true ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'The form background type is invalid.', 'formgent' ) );
                }

                $prepared[$key] = $value;
            } elseif ( 'submit_button_label' === $key ) {
                if ( ! is_string( $value ) || 255 < strlen( $value ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'The submit button label is invalid.', 'formgent' ) );
                }

                $prepared[$key] = sanitize_text_field( $value );
            } else {
                $prepared[$key] = $this->sanitize_tree( $value, $key );
            }
        }

        return $prepared;
    }

    /** @param array<string,mixed> $design Stored design settings. @return array<string,mixed> */
    private function design( array $design ): array {
        $design = array_intersect_key( $design, array_flip( self::DESIGN_KEYS ) );
        $safe   = [];

        foreach ( $design as $key => $value ) {
            if ( in_array( $key, ['status', 'show_cover', 'is_cover_bg', 'show_logo', 'show_title'], true ) ) {
                $safe[$key] = ! empty( $value );
            } elseif ( 'cover_type' === $key ) {
                if ( in_array( $value, ['color', 'media'], true ) ) {
                    $safe[$key] = $value;
                }
            } elseif ( 'title' === $key ) {
                $safe[$key] = substr( sanitize_text_field( is_scalar( $value ) ? (string) $value : '' ), 0, 255 );
            } elseif ( 'width' === $key ) {
                $safe[$key] = substr( sanitize_text_field( is_scalar( $value ) ? (string) $value : '' ), 0, 50 );
            } else {
                $safe[$key] = $this->sanitize_tree( $value, $key );
            }
        }

        return $safe;
    }

    /** @param mixed $patch Design patch. @return array<string,mixed>|WP_Error */
    private function prepare_design( $patch ) {
        if ( ! is_array( $patch ) || empty( $patch ) || ! empty( array_diff( array_keys( $patch ), self::DESIGN_KEYS ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The design settings are invalid.', 'formgent' ) );
        }

        $prepared = [];

        foreach ( $patch as $key => $value ) {
            if ( in_array( $key, ['status', 'show_cover', 'is_cover_bg', 'show_logo', 'show_title'], true ) ) {
                if ( ! is_bool( $value ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'Design switches must be boolean.', 'formgent' ) );
                }

                $prepared[$key] = $value;
            } elseif ( 'cover_type' === $key ) {
                if ( ! in_array( $value, ['color', 'media'], true ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'The cover type is invalid.', 'formgent' ) );
                }

                $prepared[$key] = $value;
            } elseif ( 'title' === $key ) {
                if ( ! is_string( $value ) || 255 < strlen( $value ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'The design title is invalid.', 'formgent' ) );
                }

                $prepared[$key] = sanitize_text_field( $value );
            } elseif ( 'width' === $key ) {
                if ( ! is_string( $value ) || ! preg_match( '/^\\d+(?:\\.\\d+)?(?:px|%|rem|em|vw)$/', $value ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'The design width must be a valid CSS length.', 'formgent' ) );
                }

                $prepared[$key] = $value;
            } else {
                $prepared[$key] = $this->sanitize_tree( $value, $key );
            }
        }

        return $prepared;
    }

    /**
     * Recursively normalize bounded, non-executable design values.
     *
     * @param mixed $value Candidate value.
     * @return mixed
     */
    private function sanitize_tree( $value, string $key = '', int $depth = 0 ) {
        if ( 8 < $depth ) {
            return null;
        }

        if ( is_array( $value ) ) {
            $safe = [];

            foreach ( array_slice( $value, 0, 100, true ) as $child_key => $child_value ) {
                $child_key        = is_int( $child_key ) ? $child_key : sanitize_key( (string) $child_key );
                $safe[$child_key] = $this->sanitize_tree( $child_value, (string) $child_key, $depth + 1 );
            }

            return $safe;
        }

        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
            return $value;
        }

        if ( ! is_scalar( $value ) ) {
            return null;
        }

        $value = substr( (string) $value, 0, 20000 );

        if ( preg_match( '/(?:html|content|message|description|text)$/i', $key ) ) {
            return wp_kses_post( $value );
        }

        if ( preg_match( '/(?:url|src|image)$/i', $key ) && '' !== $value ) {
            return esc_url_raw( $value );
        }

        return sanitize_text_field( $value );
    }
}
