<?php

namespace FormGent\App\Http\Controllers\Admin;


defined( "ABSPATH" ) || exit;

use FormGent\App\Repositories\MailchimpRepository;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\DTO\Mailchimp\FeedDTO;
use FormGent\App\Integrations\Mailchimp\Mailchimp;
use WP_REST_Request;

class MailchimpController extends Controller {
    public MailchimpRepository $repository;

    public Mailchimp $mailchimp;

    public function __construct( MailchimpRepository $repository, Mailchimp $mailchimp ) {
        $this->repository = $repository;
        $this->mailchimp  = $mailchimp;
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
                "items" => $this->repository->get_feeds( $form_id )
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
                "id" => "required|numeric"
            ]
        );

        $item = $this->repository->get_by_id( $request->get_param( "id" ) );

        if ( ! $item ) {
            throw new Exception( esc_html__( "Item not found", 'formgent' ) );
        }

        return Response::send(
            [
                "data" => $this->repository->prepare_item( $item )
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
                "title"                        => "required|string|max:255",
                "list_id"                      => "required|string|max:255",
                "group_id"                     => "string|max:255",
                "group_option_id"              => "string|max:255",
                "field_mapping"                => "required|array",
                "tags"                         => "array",
                "double_opt_in"                => "required|integer|accepted:0,1",
                "resubscribe_existing_contact" => "required|integer|accepted:0,1",
                "update_existing_contact"      => "required|integer|accepted:0,1",
                "mark_as_vip"                  => "required|integer|accepted:0,1",
                "condition_status"             => "required|integer|accepted:0,1",
                "condition_type"               => "required|string|accepted:or,and",
                "conditions"                   => "required|array",
                "status"                       => "integer|accepted:0,1",
            ]
        );

        $form_id = absint( $request->get_param( "id" ) );
        $form    = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( "Form not found", 'formgent' ) );
        }

        $field_mapping = $request->get_param( 'field_mapping' );

        if ( empty( $field_mapping['EMAIL'] ) ) {
            throw new Exception( esc_html__( "The email field must be mapped", 'formgent' ) );
        }

        $group_id        = $request->get_param( 'group_id' ) ?? null;
        $group_option_id = $request->get_param( 'group_option_id' ) ?? null;

        if ( $group_id && ! $group_option_id ) {
            throw new Exception( esc_html__( "Group option is required when group is selected", 'formgent' ) );
        }

        $dto = ( new FeedDTO )->set_form_id( $form_id )
            ->set_title( $request->get_param( "title" ) )
            ->set_list_id( $request->get_param( "list_id" ) )
            ->set_group_id( $group_id )
            ->set_group_option_id( $group_option_id )
            ->set_field_mapping( $field_mapping )
            ->set_tags( $request->get_param( 'tags' ) ?? [] )
            ->set_double_opt_in( $request->get_param( 'double_opt_in' ) )
            ->set_resubscribe_existing_contact( $request->get_param( 'resubscribe_existing_contact' ) )
            ->set_update_existing_contact( $request->get_param( 'update_existing_contact' ) )
            ->set_mark_as_vip( $request->get_param( 'mark_as_vip' ) )
            ->set_condition_status( $request->get_param( 'condition_status' ) )
            ->set_condition_type( $request->get_param( 'condition_type' ) )
            ->set_conditions( $request->get_param( 'conditions' ) )
            ->set_status( $request->get_param( 'status' ) );

        $id = $this->repository->create_feed( $dto );

        if ( ! $id ) {
            throw new Exception( esc_html__( "Could not create the item", 'formgent' ) );
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
                "feed_id"                      => "required|numeric",
                "title"                        => "string|max:255",
                "list_id"                      => "string|max:255",
                "group_id"                     => "string|max:255",
                "group_option_id"              => "string|max:255",
                "field_mapping"                => "array",
                "tags"                         => "array",
                "double_opt_in"                => "integer|accepted:0,1",
                "resubscribe_existing_contact" => "integer|accepted:0,1",
                "update_existing_contact"      => "integer|accepted:0,1",
                "mark_as_vip"                  => "integer|accepted:0,1",
                "condition_status"             => "integer|accepted:0,1",
                "condition_type"               => "string|accepted:or,and",
                "conditions"                   => "array",
                "status"                       => "integer|accepted:0,1",
            ]
        );

        
        $form = formgent_get_form_by_id( $request->get_param( "id" ) );

        if ( ! $form ) {
            throw new Exception( esc_html__( "Form not found", 'formgent' ) );
        }

        $feed_id = absint( $request->get_param( "feed_id" ) );
        $feed    = $this->repository->get_by_id( $feed_id );

        if ( ! $feed ) {
            throw new Exception( esc_html__( "The item not found", 'formgent' ) );
        }

        $field_mapping = $request->get_param( 'field_mapping' );

        if ( ! empty( $field_mapping ) && empty( $field_mapping['EMAIL'] ) ) {
            throw new Exception( esc_html__( "The email field must be mapped", 'formgent' ) );
        }

        $group_id        = $request->get_param( 'group_id' ) ?? null;
        $group_option_id = $request->get_param( 'group_option_id' ) ?? null;

        if ( $group_id && ! $group_option_id ) {
            throw new Exception( esc_html__( "Group option is required when group is selected", 'formgent' ) );
        }

        $dto = ( new FeedDTO )->set_id( $feed_id );

        if ( $request->has_param( "title" ) ) {
            $dto->set_title( $request->get_param( "title" ) );
        }

        if ( $request->has_param( "list_id" ) ) {
            $dto->set_list_id( $request->get_param( "list_id" ) );
        }

        if ( $group_id ) {
            $dto->set_group_id( $group_id );
        }

        if ( $group_option_id ) {
            $dto->set_group_option_id( $group_option_id );
        }

        if ( $field_mapping ) {
            $dto->set_field_mapping( $field_mapping );
        }

        if ( $request->has_param( 'tags' ) ) {
            $dto->set_tags( $request->get_param( "tags" ) );
        }

        if ( $request->has_param( "double_opt_in" ) ) {
            $dto->set_double_opt_in( $request->get_param( "double_opt_in" ) );
        }

        if ( $request->has_param( "resubscribe_existing_contact" ) ) {
            $dto->set_resubscribe_existing_contact( $request->get_param( "resubscribe_existing_contact" ) );
        }

        if ( $request->has_param( "update_existing_contact" ) ) {
            $dto->set_update_existing_contact( $request->get_param( "update_existing_contact" ) );
        }

        if ( $request->has_param( "mark_as_vip" ) ) {
            $dto->set_mark_as_vip( $request->get_param( "mark_as_vip" ) );
        }

        if ( $request->has_param( "condition_status" ) ) {
            $dto->set_condition_status( $request->get_param( "condition_status" ) );
        }

        if ( $request->has_param( "condition_type" ) ) {
            $dto->set_condition_type( $request->get_param( "condition_type" ) );
        }

        if ( $request->has_param( "conditions" ) ) {
            $dto->set_conditions( $request->get_param( "conditions" ) );
        }

        if ( $request->has_param( "status" ) ) {
            $dto->set_status( $request->get_param( 'status' ) );
        }

        if ( empty( $dto->to_array() ) ) {
            throw new Exception( esc_html__( "Nothing to update", 'formgent' ) );
        }

        $this->repository->update_feed( $dto );

        return Response::send(
            [
                "message" => esc_html__( "Item was updated successfully", 'formgent' )
            ]
        );
    }

    public function update_status( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'feed_id' => 'required|numeric',
                'status'  => 'required|integer|accepted:0,1'
            ]
        );

        $this->repository->update_status( intval( $wp_rest_request->get_param( 'feed_id' ) ), $wp_rest_request->get_param( 'status' ) === 1 ? 'publish' : 'draft' );

        return Response::send(
            [
                'message' => esc_html__( 'The status has been updated successfully.', 'formgent' )
            ]
        );
    }

    public function list() {
        $lists = array_map(
            function( $item ) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                ];
            }, $this->mailchimp->get_list()->lists 
        );


        return Response::send(
            [
                'lists' => $lists,
            ]
        );
    }

    public function list_groups( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'list_id' => 'required|string',
            ]
        );

        $groups = array_map(
            function( $item ) {
                return [
                    'id'    => $item->id,
                    'title' => $item->title,
                ];
            }, $this->mailchimp->get_categories( $wp_rest_request->get_param( 'list_id' ) )->categories 
        );

        return Response::send(
            [
                'groups' => $groups,
            ]
        );
    }

    public function list_group_options( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'list_id'  => 'required|string',
                'group_id' => 'required|string',
            ]
        );

        $group_options = array_map(
            function( $item ) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                ];
            }, $this->mailchimp->get_category_options( 
                $wp_rest_request->get_param( 'list_id' ),
                $wp_rest_request->get_param( 'group_id' )
            )->interests
        );

        return Response::send(
            [
                'options' => $group_options,
            ]
        );
    }

    public function list_fields( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'list_id' => 'required|string',
            ]
        );

        $fields = array_map(
            function( $item ) {
                return [
                    'tag'      => $item->tag,
                    'name'     => $item->name,
                    'type'     => $item->type,
                    'required' => $item->required,
                ];
            }, $this->mailchimp->get_fields( $wp_rest_request->get_param( 'list_id' ) )->merge_fields
        );

        $field_types = array_column( $fields, 'type' );

        $address_index = array_search( 'address', $field_types );

        if ( false !== $address_index ) {
            $address = [
                [
                    'tag'      => 'ADDRESS.ADDR1',
                    'name'     => 'Street Address',
                    'type'     => 'address.addr1',
                    'required' => true,
                ],
                [
                    'tag'      => 'ADDRESS.ADDR2',
                    'name'     => 'Street Address',
                    'type'     => 'address.addr2',
                    'required' => false,
                ],
                [
                    'tag'      => 'ADDRESS.CITY',
                    'name'     => 'City',
                    'type'     => 'address.city',
                    'required' => true,
                ],
                [
                    'tag'      => 'ADDRESS.STATE',
                    'name'     => 'State/Prov/Region',
                    'type'     => 'address.state',
                    'required' => true,
                ],
                [
                    'tag'      => 'ADDRESS.ZIP',
                    'name'     => 'Postal/Zip',
                    'type'     => 'address.zip',
                    'required' => true,
                ],
                [
                    'tag'      => 'ADDRESS.COUNTRY',
                    'name'     => 'Country',
                    'type'     => 'address.country',
                    'required' => false,
                ],
            ];

            $fields = array_merge( $fields, $address );

            unset( $fields[ $address_index ] );
        }

        $email = [
            "tag"      => "EMAIL",
            "name"     => "Email",
            "type"     => "email",
            'required' => true,
        ];

        $fields = array_merge( [ $email ], $fields );

        return Response::send(
            [
                'fields' => $fields,
            ]
        );
    }

    public function get_tags( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'list_id' => 'required|string',
            ]
        );

        return Response::send(
            [ 
                'tags' => $this->mailchimp->get_tags( $wp_rest_request->get_param( 'list_id' ) )->tags 
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
                "feed_id" => "required|numeric"
            ]
        );

        $this->repository->delete_by_id( $request->get_param( "feed_id" ) );

        return Response::send(
            [
                "message" => esc_html__( "Item was deleted successfully", 'formgent' )
            ]
        );
    }
}