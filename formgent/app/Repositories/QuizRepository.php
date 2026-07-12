<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Repositories\ResponseRepository;

class QuizRepository {
    protected ResponseRepository $response_repository;

    protected AnswerRepository $answer_repository;

    public function __construct( ResponseRepository $response_repository, AnswerRepository $answer_repository ) {
        $this->response_repository = $response_repository;
        $this->answer_repository   = $answer_repository;
    }

    public function get_result( int $response_id ): ?array {
        // Get the response
        $response = $this->response_repository->get_by_id( $response_id );

        if ( '1' !== $this->response_repository->get_meta_value( $response_id, 'is_quiz_enabled' ) ) {
            return null;
        }

        // Get the form
        $form = formgent_get_form_by_id( $response->form_id );

        // Get the form fields
        $form_fields = formgent_get_form_fields( $form );

        // Get the form answers
        $answers = $this->answer_repository->get( $response->id );

        // Fill the form fields with the answers
        foreach ( $answers as $answer ) {
            $field_name = $answer->field_name;

            if ( isset( $form_fields[ $answer->field_name ] ) ) {
                $form_fields[ $field_name ]['value'] = $answer->value;
            }
        }

        // Prepare the result
        $total_points = 0;
        $total_score  = 0;
        $quiz_fields  = [];

        $supported_quiz_fields = apply_filters(
            'formgent_quiz_fields', [
                'text',
                'textarea',
                'dropdown',
                'single-choice',
                'multiple-choice',
            ]
        );

        $choice_fields = apply_filters(
            'formgent_choice_fields', [
                'dropdown',
                'single-choice',
                'multiple-choice',
            ]
        );

        foreach ( $form_fields as $field_key => $field ) {
            if ( ! in_array( $field['field_type'], $supported_quiz_fields, true ) ) {
                continue;
            }

            // If the field has choices
            if ( in_array( $field['field_type'], $choice_fields ) ) {
                $field_options      = [];
                $is_multiple_choice = 'multiple-choice' === $field['field_type']
                    || ( 'dropdown' === $field['field_type'] && ! empty( $field['allow_multi_select'] ) );

                if ( $is_multiple_choice ) {
                    // Multiple-choice and multi-select dropdown use options[].is_default for correct answers.
                    // The total points are the sum of those correct options' numeric_value values.
                    // Back-compat: fall back to legacy correct_answer/points when is_default/numeric_value are not present.
                    $derived_correct = [];
                    $derived_points  = null;

                    if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
                        $sum_points      = 0;
                        $has_any_numeric = false;

                        foreach ( $field['options'] as $opt ) {
                            if ( empty( $opt ) || ! is_array( $opt ) ) {
                                continue;
                            }

                            if ( ! empty( $opt['is_default'] ) ) {
                                if ( isset( $opt['value'] ) ) {
                                    $derived_correct[] = (string) $opt['value'];
                                }

                                if ( isset( $opt['numeric_value'] ) && is_numeric( $opt['numeric_value'] ) ) {
                                    $sum_points     += floatval( $opt['numeric_value'] );
                                    $has_any_numeric = true;
                                }
                            }
                        }

                        if ( $has_any_numeric ) {
                            $derived_points = $sum_points;
                        }
                    }

                    $legacy_correct = isset( $field['correct_answer'] ) ? $field['correct_answer'] : [];
                    if ( ! is_array( $legacy_correct ) ) {
                        $legacy_correct = '' !== $legacy_correct ? [ $legacy_correct ] : [];
                    }

                    $correct_answer = ! empty( $derived_correct ) ? $derived_correct : $legacy_correct;

                    if ( isset( $field['value'] ) && is_array( $field['value'] ) ) {
                        $submitted_answer = $field['value'];
                    } else {
                        $submitted_answer = isset( $field['value'] ) ? json_decode( $field['value'] ) : [];
                    }

                    if ( ! is_array( $submitted_answer ) ) {
                        $submitted_answer = empty( $field['value'] ) ? [] : [ $field['value'] ];
                    }
                    $field_points = null !== $derived_points
                        ? $derived_points
                        : ( ( isset( $field['points'] ) && is_numeric( $field['points'] ) ) ? floatval( $field['points'] ) : 0 );
                } else {
                    // Dropdown: new quiz config uses options[].is_default as correct answer and options[].numeric_value as points.
                    // Back-compat: fall back to legacy correct_answer/points when is_default/numeric_value are not present.
                    if ( in_array( $field['field_type'], [ 'dropdown', 'single-choice' ], true ) && ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
                        $derived_correct = '';
                        $derived_points  = null;

                        foreach ( $field['options'] as $opt ) {
                            if ( empty( $opt ) || ! is_array( $opt ) ) {
                                continue;
                            }

                            if ( ! empty( $opt['is_default'] ) ) {
                                $derived_correct = $opt['value'] ?? '';

                                if ( isset( $opt['numeric_value'] ) && is_numeric( $opt['numeric_value'] ) ) {
                                    $derived_points = floatval( $opt['numeric_value'] );
                                }
                                break;
                            }
                        }

                        $correct_answer = $derived_correct ? $derived_correct : ( isset( $field['correct_answer'] ) ? $field['correct_answer'] : '' );
                        $field_points   = null !== $derived_points ? $derived_points : ( ( isset( $field['points'] ) && is_numeric( $field['points'] ) ) ? floatval( $field['points'] ) : 0 );
                    } else {
                        $correct_answer = isset( $field['correct_answer'] ) ? $field['correct_answer'] : '';
                        $field_points   = isset( $field['points'] ) && is_numeric( $field['points'] ) ? floatval( $field['points'] ) : 0;
                    }

                    $submitted_answer = isset( $field['value'] ) ? $field['value'] : '';
                }

                $field_score = 0;

                $total_correct_answer          = 0;
                $total_selected_correct_answer = 0;
                $total_selected_wrong_answer   = 0;

                foreach ( $field['options'] as $option ) {
                    $is_selected = false;

                    if ( $is_multiple_choice ) {
                        $is_correct_current_option = in_array( $option['value'], $correct_answer );

                        if ( in_array( $option['value'], $submitted_answer, true ) ) {
                            $is_selected = true;
                        }
                    } else {
                        $is_correct_current_option = $correct_answer === $option['value'];

                        if ( ( string ) $option['value'] === ( string ) $submitted_answer ) {
                            $is_selected = true;
                        }
                    }

                    if ( $is_correct_current_option ) {
                        $total_correct_answer++;

                        if ( $is_selected ) {
                            $total_selected_correct_answer++;
                        }
                    } else {
                        if ( $is_selected ) {
                            $total_selected_wrong_answer++;
                        }
                    }

                    $field_options[] = array_merge(
                        [
                            'label'       => $option['label'],
                            'is_selected' => $is_selected,
                            'is_correct'  => $is_correct_current_option,
                        ],
                        formgent_get_choice_option_media( $option )
                    );
                }

                if ( $total_selected_wrong_answer === 0 && ( $total_selected_correct_answer === $total_correct_answer ) ) {
                    $field_score = $field_points;
                }

                $total_points += $field_points;
                $total_score  += $field_score;

                $quiz_fields[ $field_key ] = [
                    'label'              => $field['label'],
                    'field_type'         => $field['field_type'],
                    'allow_multi_select' => 'dropdown' === $field['field_type'] && ! empty( $field['allow_multi_select'] ),
                    'options'            => $field_options,
                    'correct_answer'     => $correct_answer,
                    'points'             => $field_points,
                    'score'              => $field_score,
                ];
                continue;
            }

            // If the field is singular value field
            $value        = isset( $field['value'] ) ? $field['value'] : '';
            $field_points = isset( $field['points'] ) && is_numeric( $field['points'] ) ? floatval( $field['points'] ) : 1;
            $field_score  = trim( strtolower( ( string ) $field['correct_answer'] ) ) === trim( strtolower( ( string ) $value ) ) ? $field_points : 0;

            $total_points += $field_points;
            $total_score  += $field_score;

            $quiz_fields[ $field_key ] = [
                'label'            => $field['label'],
                'field_type'       => $field['field_type'],
                'submitted_answer' => $value,
                'correct_answer'   => $field['correct_answer'],
                'is_correct'       => $field_points === $field_score,
                'points'           => $field_points,
                'score'            => $field_score,
            ];
        }

        $form_settings_repository = formgent_form_settings_repository();
        $form_settings            = $form_settings_repository->get_settings( $form->ID );
        $quiz_settings            = isset( $form_settings['quiz'] ) ? $form_settings['quiz'] : [];
        $score_percentage         = $total_points > 0 ? round( ( $total_score / $total_points ) * 100 ) : 0;
        $grading_enabled          = isset( $quiz_settings['is_grading_enabled'] ) ? (bool) $quiz_settings['is_grading_enabled'] : true;

        return [
            'total_points' => $total_points,
            'total_score'  => $total_score,
            'percentage'   => $score_percentage,
            'grade'        => $grading_enabled ? (string) $this->get_score_grade( $score_percentage, $quiz_settings['grades'] ?? [] ) : null,
            'fields'       => $quiz_fields,
        ];
    }

    private function get_score_grade( int $grade_score, array $grades ): ?string {
        if ( empty( $grades ) ) {
            return null;
        }

        foreach ( $grades as $grade_item ) {
            if ( isset( $grade_item['min'] ) && isset( $grade_item['max'] ) && isset( $grade_item['label'] ) ) {
                if ( $grade_score >= intval( $grade_item['min'] ) && $grade_score <= intval( $grade_item['max'] ) ) {
                    return $grade_item['label'];
                }
            }
        }

        return null;
    }
}
