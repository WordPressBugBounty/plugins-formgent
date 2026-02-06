<?php

namespace FormGent\App\Providers;

use stdClass;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Contracts\Provider;
use FormGent\App\DTO\ResponseDTO;
use FormGent\App\Repositories\QuizRepository;
use FormGent\App\Repositories\FormSettingsRepository;
use FormGent\App\Repositories\ResponseRepository;
use WP_REST_Request;

class QuizProvider implements Provider {
    public function boot() {
        add_action( 'formgent_after_create_response', [ $this, 'add_quiz_status_to_response_meta' ], 10, 2 );
        add_filter( 'formgent_form_submission_response', [ $this, 'add_quiz_result_to_response' ], 10, 4 );
        add_filter( 'formgent_response_item', [ $this, 'add_quiz_status_to_response_item_data' ], 10, 1 );
    }

    public function add_quiz_result_to_response( array $data, WP_REST_Request $request, stdClass $form, stdClass $response ): array {
        if ( '1' !== "{$response->is_completed}" ) {
            return $data;
        }

        /**
         * @var QuizRepository $quiz_repository
         */
        $quiz_repository = formgent_singleton( QuizRepository::class );
        $quiz_result     = $quiz_repository->get_result( $response->id );

        if ( $quiz_result ) {
            $data['quiz_result'] = $quiz_result;
        }

        return $data;
    }

    public function add_quiz_status_to_response_item_data( stdClass $response ): stdClass {
        /**
         * @var ResponseRepository $response_repository
         */
        $response_repository = formgent_singleton( ResponseRepository::class );

        $response->is_quiz_enabled = '1' === $response_repository->get_meta_value( $response->id, 'is_quiz_enabled' );

        return $response;
    }

    public function add_quiz_status_to_response_meta( int $response_id, ResponseDTO $response_dto ) {
        /**
         * @var FormSettingsRepository $settings_repository
         */
        $settings_repository = formgent_singleton( FormSettingsRepository::class );

        $quiz_settings = $settings_repository->get_setting_by_key( $response_dto->get_form_id(), 'quiz' );
        
        /**
         * @var ResponseRepository $response_repository
         */
        $response_repository = formgent_singleton( ResponseRepository::class );

        $response_repository->add_meta( $response_id, 'is_quiz_enabled', $quiz_settings['is_enabled'] ? '1' : '0' );
    }
}