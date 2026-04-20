<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\DTO\PdfDTO;
use FormGent\App\Repositories\PdfRepository;
use FormGent\App\Repositories\FormRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;

class PdfController extends Controller {
    public PdfRepository $repository;

    public FormRepository $form_repository;

    public function __construct( PdfRepository $repository, FormRepository $form_repository ) {
        $this->repository      = $repository;
        $this->form_repository = $form_repository;
    }

    public function index( WP_REST_Request $request, Validator $validator ) {
        $validator->validate(
            [
                'id' => 'required|numeric',
            ]
        );

        $form_id = absint( $request->get_param( 'id' ) );
        $form    = $this->form_repository->get_by_id( $form_id );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found.', 'formgent' )
                ], 404
            );
        }

        // Get pagination parameters
        $page     = max( 1, absint( $request->get_param( 'page' ) ) );
        $per_page = max( 1, min( 100, absint( $request->get_param( 'per_page' ) ) ) );

        // Use paginated query if pagination parameters are provided
        if ( $request->has_param( 'page' ) || $request->has_param( 'per_page' ) ) {
            $result = $this->repository->get_paginated_by_form_id( $form_id, $page, $per_page );
            return Response::send(
                [
                    'pdfs'  => $result['items'],
                    'total' => $result['total'],
                ]
            );
        }

        // Fallback to non-paginated query for backward compatibility
        $pdfs = $this->repository->get_by_form_id( $form_id );
        return Response::send( [ 'pdfs' => $pdfs ] );
    }

    public function store( WP_REST_Request $request, Validator $validator ) {
        $validator->validate( $this->get_validation_rules() );

        $form = $this->form_repository->get_by_id( $request->get_param( 'id' ) );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ], 404
            );
        }

        $dto = $this->get_dto( $request )->set_form_id( absint( $request->get_param( 'id' ) ) );
        $this->repository->create( $dto );

        return Response::send(
            [
                'message' => esc_html__( 'PDF template was created successfully!', 'formgent' )
            ]
        );
    }

    public function update( WP_REST_Request $request, Validator $validator ) {
        $rules           = $this->get_validation_rules();
        $rules['id']     = 'required|numeric';
        $rules['pdf_id'] = 'required|numeric';

        $validator->validate( $rules );

        $form_id = absint( $request->get_param( 'id' ) );
        $pdf_id  = absint( $request->get_param( 'pdf_id' ) );

        $form = $this->form_repository->get_by_id( $form_id );
        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found.', 'formgent' )
                ], 404
            );
        }

        $pdf = $this->repository->get_by_id_and_form( $pdf_id, $form_id );
        if ( ! $pdf ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF template not found.', 'formgent' )
                ], 404
            );
        }

        $dto = $this->get_dto( $request )->set_id( $pdf_id )->set_form_id( $form_id );
        $this->repository->update( $dto );
        $updated = $this->repository->get_by_id( $pdf_id );

        return Response::send(
            [
                'message' => esc_html__( 'PDF template updated successfully.', 'formgent' ),
                'pdf'     => $updated,
            ]
        );
    }

    public function show( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'id'     => 'required|numeric',
                'pdf_id' => 'required|numeric',
            ]
        );

        $form_id = absint( $request->get_param( 'id' ) );
        $pdf_id  = absint( $request->get_param( 'pdf_id' ) );
        $pdf     = $this->repository->get_by_id_and_form( $pdf_id, $form_id );

        if ( ! $pdf ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF template not found.', 'formgent' )
                ], 404
            );
        }

        return Response::send(
            [
                'pdf' => $pdf,
            ]
        );
    }

    public function delete( WP_REST_Request $request, Validator $validator ) {
        $validator->validate(
            [
                'id'     => 'required|numeric',
                'pdf_id' => 'required|numeric',
            ]
        );

        $form_id = absint( $request->get_param( 'id' ) );
        $pdf_id  = absint( $request->get_param( 'pdf_id' ) );
        $pdf     = $this->repository->get_by_id_and_form( $pdf_id, $form_id );

        if ( ! $pdf ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF template not found.', 'formgent' )
                ], 404
            );
        }

        $this->repository->delete_by_id( $pdf_id );

        return Response::send(
            [
                'message' => esc_html__( 'PDF template deleted successfully.', 'formgent' )
            ]
        );
    }

    protected function get_validation_rules() {
        return [
            'id'            => 'required|numeric',
            'template_name' => 'required|string|max:255',
            'content'       => 'required|string',
            'template_type' => 'string|max:255',
            'paper_size'    => 'string|max:50',
            'orientation'   => 'string|max:50',
            'direction'     => 'string|max:50',
            'password'      => 'string|max:72',
        ];
    }

    protected function get_dto( WP_REST_Request $request ): PdfDTO {
        return ( new PdfDTO() )
            ->set_template_name( (string) $request->get_param( 'template_name' ) )
            ->set_template_type( $request->get_param( 'template_type' ) !== null ? (string) $request->get_param( 'template_type' ) : null )
            ->set_content( (string) $request->get_param( 'content' ) )
            ->set_paper_size( $request->get_param( 'paper_size' ) !== null ? (string) $request->get_param( 'paper_size' ) : null )
            ->set_orientation( $request->get_param( 'orientation' ) !== null ? (string) $request->get_param( 'orientation' ) : null )
            ->set_direction( $request->get_param( 'direction' ) !== null ? (string) $request->get_param( 'direction' ) : null )
            ->set_password( $request->get_param( 'password' ) !== null ? (string) $request->get_param( 'password' ) : null );
    }
}
