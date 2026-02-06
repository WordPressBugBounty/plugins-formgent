<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\ZohoCRMFeedDTO;
use FormGent\App\DTO\ZohoCRMFeedReadDTO;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\Repositories\ZohoCRMFeedRepository;
use FormGent\App\Repositories\FormRepository;
use WP_REST_Request;

class ZohoCRMFeedController extends Controller {
    public ZohoCRMFeedRepository $repository;

    public FormRepository $form_repository;

    public function __construct( ZohoCRMFeedRepository $repository, FormRepository $form_repository ) {
        $this->repository      = $repository;
        $this->form_repository = $form_repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function index( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'form_id'  => 'required|numeric',
                'per_page' => 'numeric',
                'page'     => 'numeric',
                'sort_by'  => 'string|accepted:last_modified,date_created_asc,date_created_desc,alphabetical',
            ]
        );

        $dto = ( new ZohoCRMFeedReadDTO )
            ->set_per_page( intval( $request->get_param( 'per_page' ) ) )
            ->set_page( intval( $request->get_param( 'page' ) ) )
            ->set_form_id( intval( $request->get_param( 'form_id' ) ) )
            ->set_sort_by( $request->get_param( 'sort_by' ) );

        return Response::send(
            [
                'items' => $this->repository->get( $dto )
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function store( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( $this->get_validation_rules() );

        $form = $this->form_repository->get_by_id( $request->get_param( 'form_id' ) );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ], 404
            );
        }

        $id = $this->repository->create( $this->get_dto( $request ) );

        return Response::send(
            [
                'id'      => $id,
                'message' => esc_html__( 'Zoho CRM feed was created.', 'formgent' )
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     * @throws Exception
     */
    public function show( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        $item = $this->repository->get_by_id( $request->get_param( 'id' ) );

        if ( ! $item ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Feed data not found', 'formgent' )
                ], 404
            );
        }

        return Response::send(
            [
                'data' => $item
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function update( Validator $validator, WP_REST_Request $request ): array {
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
                'message' => esc_html__( 'Item was updated successfully', 'formgent' )
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function delete( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        $this->repository->delete_by_id( $request->get_param( 'id' ) );

        return Response::send(
            [
                'message' => esc_html__( 'Item was deleted successfully', 'formgent' )
            ]
        );
    }

    public function update_status( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id'     => 'required|numeric',
                'status' => 'required|accepted:0,1',
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
        return [
            'form_id'          => 'required|integer',
            'module'           => 'required|string|max:255',
            'fields'           => 'required|array|min:1',
            'status'           => 'required|accepted:0,1',
            'condition_status' => 'accepted:0,1',
            'condition_type'   => 'string|accepted:or,and',
            'conditions'       => 'array'
        ];
    }

    protected function get_dto( WP_REST_Request $request ) {
        $conditions = $request->get_param( 'conditions' );
        if ( ! is_array( $conditions ) ) {
            $conditions = [];
        }

        $dto = ( new ZohoCRMFeedDTO )->set_form_id( $request->get_param( 'form_id' ) )
            ->set_module( $request->get_param( 'module' ) )
            ->set_fields( $request->get_param( 'fields' ) )
            ->set_status( $request->get_param( 'status' ) )
            ->set_condition_status( $request->get_param( 'condition_status' ) ?? 0 )
            ->set_condition_type( $request->get_param( 'condition_type' ) ?? 'or' )
            ->set_conditions( $conditions );

        return $dto;
    }
}