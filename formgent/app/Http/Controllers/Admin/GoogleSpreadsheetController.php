<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\Models\Spreadsheet;
use FormGent\App\Repositories\FormRepository;
use FormGent\App\DTO\GoogleSpreadSheet\DTO;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\Repositories\SpreadsheetRepository;
use FormGent\App\DTO\SpreadsheetHeaderDTO;
use FormGent\App\Integrations\Google\SpreadsheetHeader;
use WP_REST_Request;

class GoogleSpreadsheetController extends Controller {
    public SpreadsheetRepository $repository;

    public FormRepository $form_repository;

    public function __construct( SpreadsheetRepository $repository, FormRepository $form_repository ) {
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
        $form_id = absint( $request->get_param( "id" ) );

        return Response::send(
            [
                "spreadsheets" => $this->repository->get( $form_id )
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
        $validator->validate(
            [
                "title"              => "required|string|max:255",
                "spreadsheet_id"     => "required|string|max:255",
                "sheet_title"        => "required|string|max:255",
                "sheet_id"           => "required|string|max:255",
                "field_mapping_type" => "required|string|accepted:auto,manual",
                "field_mapping"      => "array",
                "condition_status"   => "required|integer|accepted:0,1",
                "condition_type"     => "required|string|accepted:or,and",
                "conditions"         => "required|array",
            ]
        );

        $form_id = absint( $request->get_param( "id" ) );
        $form    = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( "Form not found", 'formgent' ) );
        }

        $mapping = $request->get_param( 'field_mapping' ) ?? [];
        formgent_sort_characters( $mapping );

        $dto = ( new DTO )->set_form_id( $form_id )
            ->set_title( $request->get_param( "title" ) )
            ->set_spreadsheet_id( $request->get_param( "spreadsheet_id" ) )
            ->set_sheet_title( $request->get_param( "sheet_title" ) )
            ->set_sheet_id( $request->get_param( "sheet_id" ) )
            ->set_field_mapping_type( $request->get_param( "field_mapping_type" ) )
            ->set_field_mapping( $mapping )
            ->set_condition_status( $request->get_param( "condition_status" ) )
            ->set_status( "publish" )
            ->set_condition_type( $request->get_param( "condition_type" ) )
            ->set_conditions( $request->get_param( "conditions" ) )
            ->set_columns( [] );

        $id          = $this->repository->create( $dto );
        $spreadsheet = $this->repository->get_by_id( $id );

        $spreadsheet_header_dto = ( new SpreadsheetHeaderDTO )->set_form( $form )
        ->set_spreadsheet( $spreadsheet )
        ->set_field_mapping_type( $dto->get_field_mapping_type() )
        ->set_field_mapping( $dto->get_field_mapping() );

        try {
            /**
         * @var SpreadsheetHeader $spreadsheet_header
         */
            $spreadsheet_header = formgent_singleton( SpreadsheetHeader::class );
            $columns            = $spreadsheet_header->update( $spreadsheet_header_dto );
            Spreadsheet::query()->where( 'id', $id )->update(
                [
                    'columns' => wp_json_encode( $columns )
                ]
            );
        } catch ( \Throwable $th ) {
            Spreadsheet::query()->where( 'id', $id )->delete();
            throw $th;
        }

        return Response::send(
            [
                "message" => esc_html__( "Item was created successfully", 'formgent' ),
                "data"    => [
                    "id" => $id
                ]
            ], 201
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
        $validator->validate(
            [
                "spread_id"          => "required|numeric",
                "field_mapping_type" => "required|string|accepted:auto,manual",
                "field_mapping"      => "array",
                "condition_status"   => "required|integer|accepted:0,1",
                "condition_type"     => "required|string|accepted:or,and",
                "conditions"         => "required|array"
            ]
        );

        $id          = $request->get_param( "spread_id" );
        $spreadsheet = $this->repository->get_by_id( $id );

        $form = formgent_get_form_by_id( $request->get_param( 'id' ) );

        if ( ! $form ) {
            throw new Exception( esc_html__( "Form not found", 'formgent' ) );
        }
    
        if ( ! $spreadsheet ) {
            throw new Exception( esc_html__( "Spreadsheet not found", 'formgent' ) );
        }

        $dto = ( new DTO )->set_id( $id )
            ->set_field_mapping_type( $request->get_param( "field_mapping_type" ) )
            ->set_condition_status( $request->get_param( "condition_status" ) )
            ->set_condition_type( $request->get_param( "condition_type" ) )
            ->set_conditions( $request->get_param( "conditions" ) );

        $mapping = $request->get_param( 'field_mapping' ) ?? [];
        formgent_sort_characters( $mapping );
        $dto->set_field_mapping( $mapping );

        $spreadsheet_header_dto = ( new SpreadsheetHeaderDTO )->set_form( $form )
        ->set_spreadsheet( $spreadsheet )
        ->set_field_mapping_type( $dto->get_field_mapping_type() )
        ->set_field_mapping( $dto->get_field_mapping() );

        /**
         * @var SpreadsheetHeader $spreadsheet_header
         */
        $spreadsheet_header = formgent_singleton( SpreadsheetHeader::class );
        $columns            = $spreadsheet_header->update( $spreadsheet_header_dto );

        $this->repository->update( $dto->set_columns( $columns ) );

        return Response::send(
            [
                "message" => esc_html__( "Item was updated successfully", 'formgent' )
            ]
        );
    }

    public function update_status( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'spread_id' => 'required|numeric',
                'status'    => 'required|string|accepted:publish,draft'
            ]
        );

        $this->repository->update_status( intval( $wp_rest_request->get_param( 'spread_id' ) ), $wp_rest_request->get_param( 'status' ) );

        return Response::send(
            [
                'message' => esc_html__( 'The spreadsheet status has been updated successfully.', 'formgent' )
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
                'spread_id' => 'required|numeric',
            ]
        );

        $this->repository->delete_by_id( $request->get_param( "spread_id" ) );

        return Response::send(
            [
                "message" => esc_html__( "Spreadsheet was deleted successfully", 'formgent' )
            ]
        );
    }
}