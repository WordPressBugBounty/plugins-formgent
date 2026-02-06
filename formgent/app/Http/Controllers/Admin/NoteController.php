<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\NoteDTO;
use FormGent\App\Repositories\NoteRepository;
use FormGent\WpMVC\Routing\Response;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\DTO\NoteReadDTO;
use WP_REST_Request;

class NoteController extends Controller {
    public NoteRepository $repository;

    public function __construct( NoteRepository $repository ) {
        $this->repository = $repository;
    }

    public function index( Validator $validate, WP_REST_Request $request ) {
        $validate->validate(
            [
                'response_id' => 'required|numeric'
            ]
        );

        $dto = new NoteReadDTO;
        $dto->set_response_id( $request->get_param( 'response_id' ) );

        return Response::send(
            [
                'notes' => $this->repository->get( $dto )
            ] 
        );
    }

    public function store( Validator $validate, WP_REST_Request $request ) {
        $validate->validate(
            [
                'response_id' => 'required|integer',
                'note'        => 'required|string|max:1000',
            ]
        );

        $dto = new NoteDTO;
        $dto->set_response_id( $request->get_param( 'response_id' ) )->set_note( $request->get_param( 'note' ) );

        $this->repository->create( $dto );

        return Response::send(
            [
                'message' => esc_html__( "The note has been created successfully.", 'formgent' )
            ], 201 
        );
    }

    public function update( Validator $validate, WP_REST_Request $request ) {
        $validate->validate(
            [
                'id'          => 'required|numeric',
                'response_id' => 'required|integer',
                'note'        => 'required|string|max:1000',
            ]
        );

        $dto = new NoteDTO;
        $dto->set_id( $request->get_param( 'id' ) )->set_response_id( $request->get_param( 'response_id' ) )->set_note( $request->get_param( 'note' ) );

        $this->repository->update( $dto );

        return Response::send(
            [
                'message' => esc_html__( "The note has been updated successfully.", 'formgent' )
            ] 
        );
    }

    public function delete( Validator $validate, WP_REST_Request $request ) {
        $validate->validate(
            [
                'response_id' => 'required|integer',
                'id'          => 'required|numeric'
            ]
        );

        $this->repository->delete( $request->get_param( 'response_id' ), intval( $request->get_param( 'id' ) ) );

        return Response::send(
            [
                'message' => esc_html__( "The note has been deleted successfully.", 'formgent' )
            ] 
        );
    }
}