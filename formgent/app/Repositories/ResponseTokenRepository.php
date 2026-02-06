<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\ResponseToken;

class ResponseTokenRepository {
    public function get_by_id( int $id, $columns = ['*'] ) {
        return ResponseToken::query()->select( $columns )->where( 'id', $id )->first();
    }

    public function get_by_token( int $form_id, string $token ) {
        return ResponseToken::query()->where( 'form_id', $form_id )->where( 'token', $token )->first();
    }

    public function create( int $form_id, int $response_id, string $token ) {
        return ResponseToken::query()->insert_get_id(
            [
                'form_id'     => $form_id,
                'response_id' => $response_id,
                'token'       => $token
            ] 
        );
    }

    public function delete( int $id ) {
        return ResponseToken::query()->where( 'id', $id )->delete();
    }
}