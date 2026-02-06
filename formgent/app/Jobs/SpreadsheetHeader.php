<?php

namespace FormGent\App\Jobs;

defined( "ABSPATH" ) || exit;

use FormGent\App\DTO\GoogleSpreadSheet\DTO;
use FormGent\App\DTO\SpreadsheetHeaderDTO;
use FormGent\App\Models\Spreadsheet;
use FormGent\App\Repositories\SpreadsheetRepository;
use FormGent\WpMVC\Queue\Sequence;

class SpreadsheetHeader extends Sequence {
    protected $prefix = 'formgent';

    protected $action = 'google_spreadsheet_header_processor';

    protected function sleep_on_rest_time() {
        return true;
    }

    protected function triggered_error( ?array $error ) {
        update_option( 'formgent_lock_spreadsheet_header_process', 1 );
    }

    protected function get_item( $item ) {
        $spreadsheet = Spreadsheet::query()->where( 'form_id', $item['form_id'] )->where( 'is_columns_updated', 0 )->first();

        if ( ! $spreadsheet ) {
            return false;
        }

        $item['spreadsheet'] = $spreadsheet;
        return $item;
    }

    protected function perform_sequence_task( $item ) {
        $spreadsheet = $item['spreadsheet'];
        $dto         = ( new DTO )->set_id( $spreadsheet->id );

        try {
            $mapping = json_decode( $spreadsheet->field_mapping, true );

            $spreadsheet_header_dto = ( new SpreadsheetHeaderDTO )
                ->set_form( formgent_get_form_by_id( $item['form_id'] ) )
                ->set_spreadsheet( $spreadsheet )
                ->set_field_mapping( $mapping )
                ->set_field_mapping_type( $spreadsheet->field_mapping_type );

            /**
             * @var \FormGent\App\Integrations\Google\SpreadsheetHeader $spreadsheet_header
             */
            $spreadsheet_header = formgent_singleton( \FormGent\App\Integrations\Google\SpreadsheetHeader::class );
            $columns            = $spreadsheet_header->update( $spreadsheet_header_dto );

            $dto->set_columns( $columns )->set_is_columns_updated( 1 );
        } catch ( \Exception $ex ) {
            $dto->set_is_columns_updated( 1 );
        }

        /**
         * @var SpreadsheetRepository $spreadsheet_repository
         */
        $spreadsheet_repository = formgent_singleton( SpreadsheetRepository::class );
        $spreadsheet_repository->update( $dto );

        return $item;
    }

    public function dispatch_spreadsheet( $spreadsheet, $form_id ) {
        if ( ! $spreadsheet ) {
            return;
        }

        if ( $this->is_active() ) {
            $lock = get_option( 'formgent_lock_spreadsheet_header_process', 0 );

            if ( 1 == $lock ) {
                update_option( 'formgent_lock_spreadsheet_header_process', 0 );
                $this->unlock_process()->dispatch();
            }

            return;
        }

        $this->push_to_queue(
            [
                'form_id'     => $form_id,
                'spreadsheet' => $spreadsheet,
            ]
        )->save()->dispatch();
    }
}