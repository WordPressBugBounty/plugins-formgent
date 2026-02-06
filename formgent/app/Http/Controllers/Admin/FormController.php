<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\FormDTO;
use FormGent\App\DTO\FormReadDTO;
use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Models\Post;
use FormGent\App\Repositories\FormRepository;
use FormGent\App\Repositories\FormPresetFieldRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;
use Exception;

class FormController extends Controller {
    public FormRepository $form_repository;

    public FormPresetFieldRepository $form_preset_field_repository;

    public function __construct( FormRepository $form_repository, FormPresetFieldRepository $form_preset_field_repository ) {
        $this->form_preset_field_repository = $form_preset_field_repository;
        $this->form_repository              = $form_repository;
    }

    public function index( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'per_page'   => 'numeric',
                'page'       => 'numeric',
                's'          => 'string|max:255',
                'sort_by'    => 'string|accepted:last_modified,date_created,alphabetical,last_submission,unread,draft,publish',
                'date_type'  => 'string|accepted:all,today,yesterday,last_week,last_month,date_frame',
                'date_frame' => 'array',
                'type'       => 'string|accepted:all,general,conversational'
            ]
        );

        do_action( 'formgent_before_rest_request', $wp_rest_request );

        $dto = new FormReadDTO;
        $dto->set_page( intval( $wp_rest_request->get_param( 'page' ) ) );
        $dto->set_per_page( intval( $wp_rest_request->get_param( 'per_page' ) ) );
        $dto->set_search( (string) $wp_rest_request->get_param( 's' ) );
        $dto->set_sort_by( $wp_rest_request->get_param( 'sort_by' ) );
        $dto->set_date_type( $wp_rest_request->get_param( 'date_type' ) );
        $dto->set_date_frame( (array) $wp_rest_request->get_param( 'date_frame' ) );
        $dto->set_type( $wp_rest_request->get_param( 'type' ) );

        $data                      = $this->form_repository->get( $dto );
        $response                  = $this->pagination( $wp_rest_request, $data['total'], $dto->get_per_page() );
        $response['types']         = $data['types'];
        $response['forms']         = $data['forms'];
        $response['form_edit_url'] = add_query_arg( ['action' => 'edit'], admin_url( 'post.php' ) );

        do_action( 'formgent_after_rest_request', $wp_rest_request );

        return Response::send( $response );
    }

    public function store( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'title'  => 'required|string|max:255|min:5',
                'status' => 'required|string|accepted:publish,draft',
                'fields' => 'array',
                'type'   => 'required|string|accepted:general,conversational'
            ]
        );

        $dto = new FormDTO;

        $fields = $wp_rest_request->get_param( 'fields' );

        if ( is_array( $fields ) && ! empty( $fields ) ) {
            $ai_forms = intval( get_option( 'formgent_ai_created_form', 0 ) );

            if ( $ai_forms >= 5 && ! function_exists( "formgent_pro" ) ) {
                throw new Exception( "Limit Reached: You have used all 5 free AI Forms.", 422 );
            }

            $dto->set_fields( $fields );
        }

        $content = (string) $wp_rest_request->get_param( "content" );
        $type    = $wp_rest_request->get_param( 'type' );

        // if ( empty( $content ) && 'general' === $type ) {
        //     $content = '<!-- wp:formgent/submit-button {"id":"P5rxfTIwma0-"} /-->';
        // }

        $settings      = $wp_rest_request->get_param( 'settings' );
        $form_settings = $wp_rest_request->get_param( 'form_settings' );

        if ( ! empty( $settings ) && is_array( $settings ) ) {
            $dto->set_settings( $settings );
        }

        if ( ! empty( $form_settings ) && is_array( $form_settings ) ) {
            $dto->set_form_settings( $form_settings );
        }

        $dto->set_title( $wp_rest_request->get_param( 'title' ) );
        $dto->set_status( $wp_rest_request->get_param( 'status' ) );
        $dto->set_content( $content );
        $dto->set_type( $type );

        do_action( "formgent_before_create_form", $dto, $wp_rest_request );

        $form_id = $this->form_repository->create( $dto );

        $dto->set_id( $form_id );

        do_action( "formgent_after_create_form", $dto, $wp_rest_request );

        if ( isset( $ai_forms ) ) {
            update_option( 'formgent_ai_created_form', $ai_forms + 1 );
        }

        return Response::send(
            [
                'form_edit_url' => add_query_arg( ['post' => $form_id, 'action' => 'edit'], admin_url( 'post.php' ) ),
                'message'       => esc_html__( 'The form has been created successfully.', 'formgent' )
            ], 201
        );
    }

    public function update_title( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id'    => 'required|numeric',
                'title' => 'required|string|max:255|min:5',
            ]
        );

        do_action( 'formgent_before_update_form_title', $wp_rest_request );

        $this->form_repository->update_title( intval( $wp_rest_request->get_param( 'id' ) ), $wp_rest_request->get_param( 'title' ) );

        do_action( 'formgent_after_update_form_title', $wp_rest_request );

        return Response::send(
            [
                'message' => esc_html__( 'The form title has been updated successfully.', 'formgent' )
            ]
        );
    }

    public function update_bulk_status( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'ids'    => 'required|array',
                'status' => 'required|string|accepted:publish,draft'
            ]
        );

        $ids = $wp_rest_request->get_param( 'ids' );

        if ( ! formgent_is_one_level_array( $ids ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Sorry, some thing was wrong','formgent' )
                ]
            );
        }

        $this->form_repository->update_bulk_status( $ids, $wp_rest_request->get_param( 'status' ) );

        return Response::send(
            [
                'message' => esc_html__( 'The form status has been updated successfully.', 'formgent' )
            ]
        );
    }

    public function update_status( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id'     => 'required|numeric',
                'status' => 'required|string|accepted:publish,draft'
            ]
        );

        do_action( 'formgent_before_update_form_status', $wp_rest_request );

        $this->form_repository->update_status( intval( $wp_rest_request->get_param( 'id' ) ), $wp_rest_request->get_param( 'status' ) );

        do_action( 'formgent_after_update_form_status', $wp_rest_request );

        return Response::send(
            [
                'message' => esc_html__( 'The form status has been updated successfully.', 'formgent' )
            ]
        );
    }

    public function duplicate( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        $form = $this->form_repository->get_by_id( intval( $wp_rest_request->get_param( 'id' ) ) );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ], 404
            );
        }

        $dto = new FormDTO;
        $dto->set_title( $form->post_title . ' - copy' );
        $dto->set_status( $form->post_status );
        $dto->set_content( $form->post_content );
        $dto->set_type( get_post_meta( $form->ID, '_formgent_type', true ) );
        $dto->set_save_incomplete_data( formgent_is_save_incompleted_data( $form->ID ) );

        do_action( "formgent_before_duplicate_form", $dto, $wp_rest_request );

        $form_id = $this->form_repository->create( $dto );

        $dto->set_id( $form_id );

        do_action( "formgent_after_duplicate_form", $dto, $wp_rest_request );

        return Response::send(
            [
                'data'    => [
                    'id' => $form_id
                ],
                'message' => esc_html__( 'The form has been duplicated successfully.', 'formgent' )
            ], 201
        );
    }

    public function select( Validator $validator, WP_REST_Request $wp_rest_request ) {
        do_action( 'formgent_before_rest_request', $wp_rest_request );

        $query = Post::query( 'post' )->select( 'post.post_title as label', 'post.ID as value' )->where( 'post.post_type', formgent_app_config( 'post_type' ) )->where( 'post.post_status', 'publish' );

        do_action( 'formgent_forms_select_query', $query );
        do_action( 'formgent_after_rest_request', $wp_rest_request );

        return Response::send(
            [
                'forms' => $query->order_by_desc( 'post.ID' )->get()
            ]
        );
    }

    public function delete( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        $form_id = intval( $wp_rest_request->get_param( 'id' ) );

        $form = $this->form_repository->get_by_id( $form_id );

        do_action( 'formgent_before_delete_form', $form_id, $form );

        $this->form_repository->delete_by_id( $form_id );

        do_action( 'formgent_after_delete_form', $form_id, $form );

        return Response::send(
            [
                'message' => esc_html__( 'The form has been deleted successfully.', 'formgent' )
            ]
        );
    }

    public function delete_bulk_form( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'ids' => 'required|array'
            ]
        );

        $form_ids = $wp_rest_request->get_param( 'ids' );

        if ( empty( $form_ids ) || ! formgent_is_one_level_array( $form_ids ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Sorry, Something was wrong.', 'formgent' )
                ]
            );
        }

        $forms = $this->form_repository->deletes( $form_ids );

        foreach ( $forms as $form ) {
            do_action( 'formgent_after_delete_form', $form->ID, $form );
        }

        return Response::send(
            [
                'message' => esc_html__( 'Forms have been successfully deleted.', 'formgent' )
            ]
        );
    }

    public function insert_media( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'url' => 'required|url'
            ]
        );

        $attachment_url = $wp_rest_request->get_param( 'url' );

        $data = [
            'data' => [
                'status' => 201
            ]
        ];

        return Response::send( array_merge( $this->form_repository->insert_media( $attachment_url ), $data ), 201 );
    }

    public function get_settings( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        return Response::send(
            [
                'settings' => $this->form_repository->get_settings( intval( $wp_rest_request->get_param( 'id' ) ) ),
            ]
        );
    }

    public function update_settings( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id'       => 'required|numeric',
                'settings' => 'required|array'
            ]
        );

        $form_id = intval( $wp_rest_request->get_param( 'id' ) );

        $this->form_repository->save_settings(
            $form_id,
            array_merge(
                $this->form_repository->get_settings( $form_id ),
                $wp_rest_request->get_param( 'settings' )
            )
        );

        return Response::send(
            [
                'message' => esc_html__( 'Settings have been saved successfully.', 'formgent' )
            ]
        );
    }

    public function get_preset_fields( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        return Response::send(
            [
                'preset_fields' => $this->form_preset_field_repository->get_preset_fields( $request->get_param( 'id' ) ),
            ]
        );
    }
}