<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\ResponseToken;
use FormGent\App\Models\Response;

class ResponseTokenRepository {
    public function get_by_id( int $id, $columns = ['*'] ) {
        return ResponseToken::query()->select( $columns )->where( 'id', $id )->first();
    }

    public function get_by_token( int $form_id, string $token ) {
        $token_data = ResponseToken::query()->where( 'form_id', $form_id )->where( 'token', $token )->first();

        if ( ! $token_data ) {
            return null;
        }

        if ( ! empty( $token_data->expired_at ) && strtotime( (string) $token_data->expired_at ) <= time() ) {
            $this->delete( (int) $token_data->id );
            return null;
        }

        return $token_data;
    }

    public function create( int $form_id, int $response_id, string $token ) {
        $ttl = max( MINUTE_IN_SECONDS, (int) apply_filters( 'formgent_response_token_ttl', DAY_IN_SECONDS, $form_id ) );

        return ResponseToken::query()->insert_get_id(
            [
                'form_id'     => $form_id,
                'response_id' => $response_id,
                'token'       => $token,
                'expired_at'  => gmdate( 'Y-m-d H:i:s', time() + $ttl ),
            ] 
        );
    }

    /** Remove a bounded batch of expired tokens and abandoned draft responses. */
    public function cleanup_expired( int $limit = 100 ): void {
        $tokens = ResponseToken::query()
            ->select( ['id', 'response_id'] )
            ->where( 'expired_at', '<=', formgent_now() )
            ->limit( max( 1, min( 500, $limit ) ) )
            ->get();

        foreach ( $tokens as $token ) {
            Response::query()
                ->where( 'id', (int) $token->response_id )
                ->where( 'is_completed', 0 )
                ->delete();
            $this->delete( (int) $token->id );
        }
    }

    public function delete( int $id ) {
        return ResponseToken::query()->where( 'id', $id )->delete();
    }
}
