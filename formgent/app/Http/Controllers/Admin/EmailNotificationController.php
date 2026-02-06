<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\Repositories\EmailNotificationRepository;
use FormGent\App\Repositories\FormRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use FormGent\App\Http\Controllers\Controller;
use FormGent\App\DTO\EmailNotificationDTO;
use FormGent\App\DTO\EmailNotificationReadDTO;
use WP_REST_Request;

class EmailNotificationController extends Controller {
    public EmailNotificationRepository $repository;

    public FormRepository $form_repository;

    public function __construct( EmailNotificationRepository $repository, FormRepository $form_repository ) {
        $this->repository      = $repository;
        $this->form_repository = $form_repository;
    }

    public function index( WP_REST_Request $request, Validator $validator ) {
        $validator->validate(
            [
                'form_id'  => 'required|numeric',
                'per_page' => 'numeric',
                'page'     => 'numeric',
                'sort_by'  => 'string|accepted:last_modified,date_created_asc,date_created_desc,alphabetical',
            ]
        );

        $dto = new EmailNotificationReadDTO;
        $dto->set_per_page( intval( $request->get_param( 'per_page' ) ) );
        $dto->set_page( intval( $request->get_param( 'page' ) ) );
        $dto->set_form_id( intval( $request->get_param( 'form_id' ) ) );
        $dto->set_sort_by( $request->get_param( 'sort_by' ) );

        return Response::send( $this->repository->get( $dto ) );
    }

    public function store( WP_REST_Request $request, Validator $validator ) {
        $validator->validate( $this->get_validation_rules() );

        $form = $this->form_repository->get_by_id( $request->get_param( 'form_id' ) );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ], 404
            );
        }

        $this->repository->create( $this->get_dto( $request ) );

        return Response::send(
            [
                'message' => esc_html__( 'Email notification was created successfully!', 'formgent' )
            ]
        );
    }

    public function update( WP_REST_Request $request, Validator $validator ) {
        $rules       = $this->get_validation_rules();
        $rules['id'] = 'required|numeric';

        $validator->validate( $rules );

        $form = $this->form_repository->get_by_id( $request->get_param( 'form_id' ) );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ], 404
            );
        }

        $this->repository->update( $this->get_dto( $request )->set_id( $request->get_param( 'id' ) ) );

        return Response::send(
            [
                'message' => esc_html__( 'Email notification was updated successfully!', 'formgent' )
            ]
        );
    }

    public function show( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        $form = $this->repository->get_by_id( intval( $wp_rest_request->get_param( 'id' ) ) );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Email notification not found', 'formgent' )
                ], 404
            );
        }

        return Response::send(
            [
                'email' => $form
            ]
        );
    }

    public function delete( WP_REST_Request $request, Validator $validator ) {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        $this->repository->delete_by_id( $request->get_param( 'id' ) );

        return Response::send(
            [
                'message' => esc_html__( 'Email notification was deleted successfully!', 'formgent' )
            ]
        );
    }

    public function duplicate( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        $email = $this->repository->get_by_id( intval( $request->get_param( 'id' ) ) );

        if ( ! $email ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Email notification not found', 'formgent' )
                ], 404
            );
        }

        $dto = ( new EmailNotificationDTO )->set_form_id( $email->form_id )
        ->set_name( $email->name . ' - copy' )
        ->set_send_to( $email->send_to )
        ->set_subject( $email->subject )
        ->set_body( $email->body )
        ->set_cc( $email->cc )
        ->set_bcc( $email->bcc )
        ->set_reply_to( $email->reply_to )
        ->set_from_name( $email->from_name ?? null )
        ->set_from_email( $email->from_email ?? null )
        ->set_status( 'draft' );

        $this->repository->create( $dto );

        return Response::send(
            [
                'message' => esc_html__( 'Email notification was duplicated successfully!', 'formgent' )
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

        $this->repository->update_status( intval( $wp_rest_request->get_param( 'id' ) ), $wp_rest_request->get_param( 'status' ) );

        return Response::send(
            [
                'message' => esc_html__( 'The status has been updated successfully.', 'formgent' )
            ]
        );
    }

    protected function get_validation_rules() {
        $rules = [
            'form_id'    => 'required|integer',
            'name'       => 'required|string|max:255',
            'send_to'    => 'required|string|max:255',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string|max:2000',
            'cc'         => 'string|max:255',
            'bcc'        => 'string|max:255',
            'reply_to'   => 'string|max:255',
            'from_name'  => 'string|max:255',
            'from_email' => 'string|max:255',
            'status'     => 'required|string|accepted:publish,draft',
        ];

        /**
         * TODO: need to move this code to pro plugin
         */
        if ( function_exists( 'formgent_pro' ) ) {
            $rules['condition_status'] = 'required|accepted:0,1';
            $rules['condition_type']   = 'string|accepted:or,and';
            $rules['conditions']       = 'array';
        }

        return $rules;
    }

    protected function get_dto( WP_REST_Request $request ) {
        $dto = ( new EmailNotificationDTO )->set_form_id( $request->get_param( 'form_id' ) )
        ->set_name( $request->get_param( 'name' ) )
        ->set_send_to( $request->get_param( 'send_to' ) )
        ->set_subject( $request->get_param( 'subject' ) )
        ->set_body( $request->get_param( 'body' ) )
        ->set_cc( $request->get_param( 'cc' ) )
        ->set_bcc( $request->get_param( 'bcc' ) )
        ->set_reply_to( $request->get_param( 'reply_to' ) )
        ->set_from_name( $request->get_param( 'from_name' ) )
        ->set_from_email( $request->get_param( 'from_email' ) )
        ->set_status( $request->get_param( 'status' ) );

        /**
         * TODO: need to move this code to pro plugin
         */
        if ( function_exists( 'formgent_pro' ) ) {
            $conditions = $request->get_param( 'conditions' );

            if ( ! is_array( $conditions ) ) {
                $conditions = [];
            }

            $dto->set_condition_status( $request->get_param( 'condition_status' ) )
            ->set_condition_type( $request->get_param( 'condition_type' ) ?? 'or' )
            ->set_conditions( $conditions );
        }

        return $dto;
    }
}