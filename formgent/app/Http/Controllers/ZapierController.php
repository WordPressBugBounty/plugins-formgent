<?php

namespace FormGent\App\Http\Controllers;

defined( "ABSPATH" ) || exit;

use FormGent\App\Fields\FileUpload\FileUpload;
use FormGent\App\Fields\Address\Address;
use FormGent\App\Fields\MultipleChoice\MultipleChoice;
use FormGent\App\Fields\Name\Name;
use FormGent\App\Models\Post;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\DTO\ZapierZapsDTO;
use FormGent\App\Models\ZapierProcessedResponses;
use FormGent\App\Repositories\ZapierZapsRepository;
use WP_REST_Request;

class ZapierController extends Controller {
    public ZapierZapsRepository $repository;

    public function __construct( ZapierZapsRepository $repository ) {
        $this->repository = $repository;
    }

    /**
     * Verify zap auth
     */
    public function me( Validator $validator, WP_REST_Request $request ): array {
        return Response::send(
            [
                'name' => get_bloginfo( 'name' )
            ] 
        );
    }

    public function forms( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'per_page' => 'required|numeric',
                'page'     => 'required|numeric'
            ]
        );

        $forms = Post::query( 'post' )->select( 'ID as id', 'post_title as title' )->where( 'post.post_type', formgent_post_type() )->where( 'post_status', 'publish' )->pagination( absint( $request->get_param( 'page' ) ), absint( $request->get_param( 'per_page' ) ) );

        $forms = array_map(
            function( $form ) {
                $form->id = intval( $form->id );
                return $form;
            }, $forms
        );

        return Response::send( $forms );
    }

    public function responses( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'form_id'  => 'required|numeric',
                'page'     => 'required|numeric',
                'per_page' => 'required|numeric',
                'zap_id'   => 'required|string'
            ]
        );

        $zap_id  = $request->get_param( 'zap_id' );
        $form_id = $request->get_param( 'form_id' );

        $query = \FormGent\App\Models\Response::query( "response" )->select( "response.*" )->with(
            'answers', function( $query ) {
                $query->where_is_null( 'parent_id' );
            } 
        )->with( 'answers.children' )->where( 'response.form_id', $form_id )->where( 'response.is_completed', 1 )->order_by_desc( 'response.id' );

        if ( ! is_int( strpos( $zap_id, 'subscription' ) ) ) {
            $responses = $query->limit( 5 )->get();
            return Response::send( $this->process_responses( $responses ) );
        }

        $zap_id = explode( ":", $zap_id )[1];

        $zap = $this->repository->first( $zap_id, $form_id );

        if ( ! $zap ) {
            $dto = ( new ZapierZapsDTO )->set_form_id( $form_id )->set_zap_id( $zap_id );
            $this->repository->create( $dto );
            return Response::send( [] );
        }

        $query->left_join(
            ZapierProcessedResponses::get_table_name() . ' as processed_response', function( $join ) use( $zap_id ) {
                $join->on_column( "response.id", "processed_response.response_id" )->where( "processed_response.zap_id", $zap_id );
            }
        )->where_is_null( "processed_response.id" )->where( "response.created_at", ">", $zap->created_at );

        $responses = $query->get();

        if ( empty( $responses ) ) {
            return Response::send( [] );
        }

        $responses           = $this->process_responses( $responses );
        $processed_responses = array_map(
            function( $id ) use( $zap_id ) {
                return [
                    'response_id' => $id,
                    'zap_id'      => $zap_id
                ];
            },  array_column( $responses, 'id' )
        );

        ZapierProcessedResponses::query()->insert( $processed_responses );

        return Response::send( $responses );
    }

    protected function process_responses( $responses ) {
        $field_processors = [
            FileUpload::get_key()     => function( $answer ) {
                return $this->process_file_upload_value( $answer->value );
            },
            'digital-signature'       => function( $answer ) {
                return WP_CONTENT_URL . '/uploads/' . $answer->value;
            },
            MultipleChoice::get_key() => function( $answer ) {
                return json_decode( $answer->value );
            },
            Name::get_key()           => function( $answer ) {
                return $this->process_composite_field_value( $answer );
            },
            Address::get_key()        => function( $answer ) {
                return $this->process_composite_field_value( $answer );
            },
        ];

        $data = [];

        foreach ( $responses as $response ) {
            $values = ['id' => intval( $response->id )];
            foreach ( $response->answers as $answer ) {
                if ( isset( $field_processors[$answer->field_type] ) ) {
                    $value = $field_processors[$answer->field_type]( $answer );
                } else {
                    $value = $answer->value;
                }
                $values[$answer->field_name] = $value;

            }
            $data[] = $values;
        }

        return $data;
    }

    /**
     * Process composite field value (Name, Address)
     */
    protected function process_composite_field_value( $answer ) {
        $value = [];
        foreach ( $answer->children as $child ) {
            $value[$child->field_name] = $child->value;
        }
        return $value;
    }

    /**
     * Process file upload field value
     */
    protected function process_file_upload_value( $value ) {
        return array_map(
            function( $file ) {
                return WP_CONTENT_URL . '/uploads/' . $file;
            }, 
            json_decode( $value, true ) ?? []
        );
    }
}