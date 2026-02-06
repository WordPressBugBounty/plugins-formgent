<?php

namespace FormGent\App\Integrations\Google;

defined( "ABSPATH" ) || exit;

use InvalidArgumentException;
use FormGent\Google\Service\Drive;
use FormGent\Google\Service\Sheets;
use FormGent\Google\Service\Sheets\Spreadsheet as GoogleSpreadsheet;
use FormGent\Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use FormGent\Google\Service\Sheets\ValueRange;
use FormGent\App\DTO\GoogleSpreadSheet\DTO;

class Spreadsheet extends Base {
    public function get() {
        $drive_service = new Drive( $this->get_google_client() );

        $opt_params = [
            'q'        => "mimeType='application/vnd.google-apps.spreadsheet' and trashed = false",
            'fields'   => 'files(id, name)',
            'pageSize' => 1000,
        ];

        $results = $drive_service->files->listFiles( $opt_params );

        $sheets = [];

        foreach ( $results->getFiles() as $file ) {
            $sheets[] = [
                'value' => $file->id,
                'label' => $file->name
            ];
        }

        return $sheets;
    }

    public function get_sheets( $spreadsheet_id ) {
        $sheets_service = new Sheets( $this->get_google_client() );

        $spreadsheet = $sheets_service->spreadsheets->get( $spreadsheet_id );
        $sheets      = $spreadsheet->getSheets();

        $_sheets = [];

        foreach ( $sheets as $sheet ) {
            $props     = $sheet->getProperties();
            $_sheets[] = [
                'value' => $props->getSheetId(),
                'label' => $props->getTitle()
            ];
        }

        return $_sheets;
    }

    public function create( DTO $dto ) {
        $sheets_service = new Sheets( $this->get_google_client() );

        // Create the basic spreadsheet
        $spreadsheet = new GoogleSpreadsheet(
            [
                'properties' => [
                    'title' => $dto->get_title()
                ]
            ]
        );

        $spreadsheet    = $sheets_service->spreadsheets->create( $spreadsheet );
        $spreadsheet_id = $spreadsheet->getSpreadsheetId();

        // Rename the default sheet (ID 0)
        $this->rename_sheet( $spreadsheet_id, 0, $dto->get_sheet_title() );

        return $spreadsheet_id;
    }

    protected function rename_sheet( $spreadsheet_id, $sheet_id, $title ) {
        $sheets_service = new Sheets( $this->get_google_client() );

        $request = new BatchUpdateSpreadsheetRequest(
            [
                'requests' => [
                    // 1. Rename sheet and set properties
                    [
                        'updateSheetProperties' => [
                            'properties' => [
                                'sheetId'        => $sheet_id,
                                'title'          => $title,
                                'gridProperties' => [
                                    'frozenRowCount' => 1 // Freeze header row
                                ]
                            ],
                            'fields'     => 'title,gridProperties.frozenRowCount'
                        ]
                    ],
                    // 2. Format ONLY the header row (row 1)
                    [
                        'repeatCell' => [
                            'range'  => [
                                'sheetId'       => $sheet_id,
                                'startRowIndex' => 0,  // Only row 1
                                'endRowIndex'   => 1   // Only row 1
                            ],
                            'cell'   => [
                                'userEnteredFormat' => [
                                    'backgroundColor'     => [
                                        'red'   => 54 / 255,
                                        'green' => 168 / 255,
                                        'blue'  => 84 / 255
                                    ],
                                    'horizontalAlignment' => 'LEFT',
                                    'verticalAlignment'   => 'TOP',
                                    'textFormat'          => [
                                        'foregroundColor' => [
                                            'red'   => 1,
                                            'green' => 1,
                                            'blue'  => 1
                                        ],
                                        'bold'            => true,
                                        'fontSize'        => 12,
                                        'fontFamily'      => 'Inter'
                                    ],
                                    'wrapStrategy'        => 'WRAP'
                                ]
                            ],
                            'fields' => 'userEnteredFormat'
                        ]
                    ],
                    // 3. Set DEFAULT formatting for all other rows (row 2+)
                    [
                        'repeatCell' => [
                            'range'  => [
                                'sheetId'       => $sheet_id,
                                'startRowIndex' => 1 // All rows after header
                            ],
                            'cell'   =>  $this->get_cell_config(),
                            'fields' => 'userEnteredFormat'
                        ]
                    ],
                    // 4. Set column widths
                    [
                        'updateDimensionProperties' => [
                            'range'      => [
                                'sheetId'    => $sheet_id,
                                'dimension'  => 'COLUMNS',
                                'startIndex' => 0
                            ],
                            'properties' => [
                                'pixelSize' => 200
                            ],
                            'fields'     => 'pixelSize'
                        ]
                    ]
                ]
            ]
        );

        $sheets_service->spreadsheets->batchUpdate( $spreadsheet_id, $request );
        return true;
    }

    protected function get_sheet_title_by_id( $spreadsheet_id, $sheet_id ) {
        $sheets_service = new Sheets( $this->get_google_client() );

        $spreadsheet = $sheets_service->spreadsheets->get( $spreadsheet_id );
        $sheets      = $spreadsheet->getSheets();

        foreach ( $sheets as $sheet ) {
            if ( $sheet->getProperties()->getSheetId() == $sheet_id ) {
                return $sheet->getProperties()->getTitle();
            }
        }

        return false;
    }

    public function append_row( $spreadsheet_id, $sheet_id, $value, $end_col ) {
        $sheet_title = $this->get_sheet_title_by_id( $spreadsheet_id, $sheet_id );

        if ( ! $sheet_title ) {
            throw new InvalidArgumentException( 'Sheet title not found' );
        }

        $service = new Sheets( $this->get_google_client() );
    
        if ( empty( $value ) ) {
            throw new InvalidArgumentException( 'Values cannot be empty' );
        }

        // Prepare the value range
        $value_range = new ValueRange();
        $value_range->setValues( [$value] );

        $current = $service->spreadsheets_values->get(
            $spreadsheet_id,
            sprintf( "'%s'!A1:A2", $sheet_title )
        );

        $rows = $current->getValues();

        /**
         * If 2 rows are already present, append the new row, don't need to do anything else
         */
        if ( 2 === count( $rows ) ) {
            $start_col = 'A';
            $range     = sprintf( "'%s'!%s:%s", $sheet_title, $start_col, $end_col );
            return $service->spreadsheets_values->append(
                $spreadsheet_id,
                $range,
                $value_range,
                [
                    'valueInputOption' => 'USER_ENTERED',
                    'insertDataOption' => 'INSERT_ROWS'
                ]
            );
        }

        /**
         * If only 1 row is present, we need to insert a new row and set the row style and formatting.
         */
        $next_row = 2;
        $range    = sprintf( "'%s'!A%d:%s%d", $sheet_title, $next_row, $end_col, $next_row );

        $service->spreadsheets_values->update(
            $spreadsheet_id,
            $range,
            $value_range,
            [
                'valueInputOption' => 'USER_ENTERED'
            ]
        );

        // Set default formatting for the new row
        $format_request = new BatchUpdateSpreadsheetRequest(
            [
                'requests' => [
                    [
                        'repeatCell' => [
                            'range'  => [
                                'sheetId'       => $sheet_id,
                                'startRowIndex' => $next_row - 1,
                                'endRowIndex'   => $next_row
                            ],
                            'cell'   =>  $this->get_cell_config(),
                            'fields' => 'userEnteredFormat'
                        ]
                    ]
                ]
            ]
        );

        // Execute requests in sequence
        $service->spreadsheets->batchUpdate( $spreadsheet_id, $format_request );

        return true;
    }

    public function has_value( $spreadsheet_id, $sheet_id ) {
        $sheet_title = $this->get_sheet_title_by_id( $spreadsheet_id, $sheet_id );

        if ( ! $sheet_title ) {
            throw new InvalidArgumentException( 'Sheet title not found' );
        }

        $service = new Sheets( $this->get_google_client() );

        //Get existing columns
        $current = $service->spreadsheets_values->get(
            $spreadsheet_id,
            sprintf( "'%s'!A1:H2", $sheet_title )
        );

        $rows = $current->getValues();

        return empty( $rows ) ? false : count( $rows ) === 2;
    }

    public function update_header( $spreadsheet_id, $sheet_id, $columns, $end_col ) {
        $sheet_title = $this->get_sheet_title_by_id( $spreadsheet_id, $sheet_id );

        if ( ! $sheet_title ) {
            throw new InvalidArgumentException( 'Sheet title not found' );
        }

        if ( empty( $columns ) ) {
            throw new InvalidArgumentException( 'Values cannot be empty' );
        }

        $service = new Sheets( $this->get_google_client() );

        //Get existing columns
        $current = $service->spreadsheets_values->get(
            $spreadsheet_id,
            sprintf( "'%s'!A1:ZZ1", $sheet_title )
        );

        $rows                   = $current->getValues();
        $total_columns          = count( $columns );
        $total_existing_columns = ! empty( $rows[0] ) ? count( $rows[0] ) : 0;

        // If the number of columns is less than the existing columns, fill the remaining columns with empty strings
        if ( $total_columns < $total_existing_columns ) {
            $columns = array_merge( $columns, array_fill( $total_columns, $total_existing_columns - $total_columns, '' ) );
            $end_col = $this->get_column_name_by_index( $total_existing_columns );
        }

        // Prepare the value range
        $value_range = new ValueRange();
        $value_range->setValues( [$columns] );

        // Update the row
        return $service->spreadsheets_values->update(
            $spreadsheet_id,
            sprintf( "'%s'!A%d:%s%d", $sheet_title, 1, $end_col, 1 ),
            $value_range,
            [
                'valueInputOption' => 'USER_ENTERED'
            ]
        );
    }

    private function get_column_name_by_index( $index ) {
        if ( $index < 1 ) {
            throw new InvalidArgumentException( 'Index must be greater than 0' );
        }

        $end_col = '';

        while ( $index > 0 ) {
            $remainder = ( $index - 1 ) % 26;
            $end_col   = chr( 65 + $remainder ) . $end_col;
            $index     = (int) ( ( $index - $remainder - 1 ) / 26 );
        }

        return $end_col;
    }

    private function get_cell_config() {
        return [
            'userEnteredFormat' => [
                'horizontalAlignment' => 'LEFT',
                'verticalAlignment'   => 'TOP',
                'wrapStrategy'        => 'WRAP',
                'backgroundColor'     => [
                    'red'   => 1,
                    'green' => 1,
                    'blue'  => 1
                ],
                'textFormat'          => [
                    'foregroundColor' => [
                        'red'   => 0,
                        'green' => 0,
                        'blue'  => 0
                    ],
                    'bold'            => false,
                    'fontSize'        => 11,
                    'fontFamily'      => 'Inter'
                ]
            ]
        ];
    }

    public function get_email() {
        $response = wp_remote_get(
            'https://www.googleapis.com/oauth2/v2/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->get_google_client()->getAccessToken()['access_token']
                ],
            ]
        );

        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );

            return $body['email'];
        }

        return false;
    }
}