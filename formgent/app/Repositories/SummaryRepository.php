<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use Exception;
use FormGent\App\Models\Answer;
use FormGent\App\Fields\Rating\Rating;
use FormGent\App\Fields\Repeater\Repeater;

class SummaryRepository {
    public function get( int $form_id, string $field_name, int $page = 0, int $per_page = 0 ) {
        $form = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( 'Form not found.', 'formgent' ), 404 );
        }

        $fields = formgent_get_form_fields( $form );

        if ( ! isset( $fields[ $field_name ] ) ) {
            throw new Exception( esc_html__( 'Field not found', 'formgent' ), 404 );
        }

        $field      = $fields[ $field_name ];
        $field_type = $field[ 'field_type' ];

        $field_handlers = formgent_config( 'fields' );

        if ( empty( $field_handlers[ $field_type ] ) ) {
            throw new Exception( esc_html__( 'Field handler not found.', 'formgent' ), 404 );
        }

        /**
         * @var \FormGent\App\Fields\Field $field_handler
         */
        $field_handler = formgent_singleton( $field_handlers[ $field_type ]['class'] );

        return $field_handler->get_summary( $form, $field, $page, $per_page );
    }

    public function get_fields( int $form_id ) {
        $form = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( 'Form not found.', 'formgent' ), 404 );
        }

        /**
         * @var ResponseRepository $response_repository
         */
        $response_repository = formgent_singleton( ResponseRepository::class );
        $response_count      = $response_repository->get_count_by_form_id( $form_id );

        $final_fields = [];

        $totals = Answer::query()
            ->where( 'form_id', $form_id )
            ->where_null( 'parent_id' )
            ->select( 'field_name, COUNT(field_name) as total' )
            ->group_by( 'field_name' )
            ->get();

        $ignore_summary_fields = ['paypal', 'stripe', 'custom-payment-amount', 'payment-item', 'quantity', 'payment-summary', 'repeater'];

        foreach ( formgent_get_form_fields( $form ) as $field ) {
            if ( Repeater::get_key() === $field['field_type'] ) {
                continue;
            }

            $field_type = $field['field_type'];

            if ( in_array( $field_type, $ignore_summary_fields ) ) {
                continue;
            }

            $field_name = $field['name'];

            $total_key = array_search( $field_name, array_column( $totals, 'field_name' ) );

            if ( is_int( $total_key ) ) {
                $total = intval( $totals[ $total_key ]->total );
            } else {
                $total = 0;
            }

            $final_field = [
                'field_name'     => $field_name,
                'label'          => isset( $field['label'] ) ? $field['label'] : $field_name,
                'field_type'     => $field_type,
                'total_response' => $response_count,
                'total_answer'   => $total,
            ];

            if ( ! empty( $field['children'] ) ) {
                $children = [];

                foreach ( array_keys( $field['children'] ) as $child_key ) {
                    $children[ $child_key ] = ! empty( $field['children'][ $child_key ]['label'] ) ? $field['children'][ $child_key ]['label'] : $child_key;
                }

                $final_field['children'] = $children;
            }

            if ( Rating::get_key() === $field['field_type'] ) {
                $final_field['rating_limit'] = $field['rating_limit'];
                $final_field['rating_icon']  = $field['rating_icon'];
            }

            $final_fields[] = apply_filters( 'formgent_summary_field', $final_field, $field );
        }

        return $final_fields;
    }
}