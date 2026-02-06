<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\Integrations\Google\Spreadsheet;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\DTO\GoogleSpreadSheet\DTO;
use WP_REST_Request;

class GoogleSheetController extends Controller {
    public Spreadsheet $spreadsheet;

    public function __construct( Spreadsheet $spreadsheet ) {
        $this->spreadsheet = $spreadsheet;
    }

    public function index(): array {
        return Response::send(
            [
                "spreadsheets" => $this->spreadsheet->get()
            ]
        );
    }

    public function store( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                "title"       => "required|string|max:255",
                "sheet_title" => "required|string|max:255",
            ]
        );

        $dto = ( new DTO )->set_title( $request->get_param( "title" ) )
            ->set_sheet_title( $request->get_param( "sheet_title" ) );

        $spreadsheet_id = $this->spreadsheet->create( $dto );

        return Response::send(
            [
                "spreadsheet_id" => $spreadsheet_id,
                "sheet_id"       => 0
            ]
        );
    }

    public function email(): array {
        return Response::send(
            [
                "email" => $this->spreadsheet->get_email()
            ]
        );
    }

    public function sheets( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                "sheet_id" => "required|string"
            ]
        );

        return Response::send(
            [
                "sheets" => $this->spreadsheet->get_sheets( $request->get_param( "sheet_id" ) )
            ]
        );
    }

    public function disconnect(): array {
        $this->spreadsheet->disconnect();
        return Response::send( [] );
    }
}