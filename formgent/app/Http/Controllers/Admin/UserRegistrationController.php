<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\DTO\EmailNotificationDTO;
use FormGent\App\Repositories\EmailNotificationRepository;
use FormGent\App\Repositories\FormRepository;
use FormGent\App\Repositories\FormSettingsRepository;
use FormGent\App\Services\UserVerificationService;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

/**
 * REST API for form user-registration feeds (create user on submit).
 * Data is stored in form settings under key 'user_registrations'.
 */
class UserRegistrationController extends Controller {
    public FormRepository $form_repository;

    public FormSettingsRepository $settings_repository;

    public function __construct( FormRepository $form_repository, FormSettingsRepository $settings_repository ) {
        $this->form_repository     = $form_repository;
        $this->settings_repository = $settings_repository;
    }

    /**
     * Display a listing of user registrations for a form.
     *
     * @param Validator       $validator Instance of the Validator.
     * @param WP_REST_Request $request   The REST request instance.
     * @return array
     */
    public function index( Validator $validator, WP_REST_Request $request ): array {
        $form_id = absint( $request->get_param( "id" ) );
        $form    = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( "Form not found", 'formgent' ) );
        }

        $registrations = $this->settings_repository->get_setting_by_key( $form_id, 'user_registrations', [] );
        if ( ! is_array( $registrations ) ) {
            $registrations = [];
        }

        return Response::send(
            [
                'registrations' => $registrations,
            ]
        );
    }

    /**
     * Store a newly created user registration in storage.
     *
     * @param Validator       $validator Instance of the Validator.
     * @param WP_REST_Request $request   The REST request instance.
     * @return array
     */
    public function store( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( $this->get_store_rules() );

        $form_id = absint( $request->get_param( "id" ) );
        $form    = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( "Form not found", 'formgent' ) );
        }

        $registrations = $this->settings_repository->get_setting_by_key( $form_id, 'user_registrations', [] );
        if ( ! is_array( $registrations ) ) {
            $registrations = [];
        }

        $new_id     = $this->next_registration_id( $registrations );
        $body       = $request->get_json_params() ?: $request->get_body_params();
        $item       = $this->sanitize_registration_item( is_array( $body ) ? $body : [] );
        $item['id'] = $new_id;
        $this->validate_registration_item( $item );

        // Auto-create a registration email notification only if none exists yet.
        $existing_notification_id = $this->find_existing_registration_notification_id( $registrations );
        if ( $existing_notification_id ) {
            $item['registration_email_notification_id'] = $existing_notification_id;
        } else {
            $item['registration_email_notification_id'] = $this->create_registration_email_notification( $form_id );
        }

        $registrations[] = $item;
        $this->settings_repository->update_setting( $form_id, 'user_registrations', $registrations );

        return Response::send(
            [
                'message' => esc_html__( "Item was created successfully", 'formgent' ),
                'data'    => [
                    'id' => $new_id,
                ],
            ],
            201
        );
    }

    /**
     * Update the specified user registration in storage.
     *
     * @param Validator       $validator Instance of the Validator.
     * @param WP_REST_Request $request   The REST request instance.
     * @return array
     */
    public function update( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( $this->get_update_rules() );

        $form_id         = absint( $request->get_param( "id" ) );
        $registration_id = $request->get_param( "registration_id" );
        $form            = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( "Form not found", 'formgent' ) );
        }

        $registrations = $this->settings_repository->get_setting_by_key( $form_id, 'user_registrations', [] );
        if ( ! is_array( $registrations ) ) {
            $registrations = [];
        }

        $index = $this->find_registration_index( $registrations, $registration_id );
        if ( $index === null ) {
            throw new Exception( esc_html__( "User registration not found", 'formgent' ) );
        }

        $body       = $request->get_json_params() ?: $request->get_body_params();
        $item       = $this->sanitize_registration_item( is_array( $body ) ? $body : [] );
        $item['id'] = $registrations[ $index ]['id'];
        $this->validate_registration_item( $item );

        // Preserve the linked email notification ID.
        $notification_id                            = $registrations[ $index ]['registration_email_notification_id'] ?? 0;
        $item['registration_email_notification_id'] = absint( $notification_id );

        // Update email notification status based on the switch.
        if ( $notification_id ) {
            $email_repo = formgent_singleton( EmailNotificationRepository::class );
            $status     = ! empty( $item['send_registration_email'] ) ? 'publish' : 'draft';
            $email_repo->update_status( absint( $notification_id ), $status );
        }

        $registrations[ $index ] = $item;
        $this->settings_repository->update_setting( $form_id, 'user_registrations', $registrations );

        return Response::send(
            [
                'message' => esc_html__( "Item was updated successfully", 'formgent' ),
            ]
        );
    }

    /**
     * Remove the specified user registration from storage.
     *
     * @param Validator       $validator Instance of the Validator.
     * @param WP_REST_Request $request   The REST request instance.
     * @return array
     */
    public function delete( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'registration_id' => 'required',
            ]
        );

        $form_id         = absint( $request->get_param( "id" ) );
        $registration_id = $request->get_param( "registration_id" );
        $form            = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( "Form not found", 'formgent' ) );
        }

        $registrations = $this->settings_repository->get_setting_by_key( $form_id, 'user_registrations', [] );
        if ( ! is_array( $registrations ) ) {
            $registrations = [];
        }

        $index = $this->find_registration_index( $registrations, $registration_id );
        if ( $index === null ) {
            throw new Exception( esc_html__( "User registration not found", 'formgent' ) );
        }

        array_splice( $registrations, $index, 1 );
        $this->settings_repository->update_setting( $form_id, 'user_registrations', $registrations );

        return Response::send(
            [
                'message' => esc_html__( "User registration was deleted successfully", 'formgent' ),
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    protected function get_store_rules(): array {
        return [
            'name'                           => 'required|string|max:255',
            'field_mapping'                  => 'array',
            'user_role'                      => 'string|max:100',
            'custom_role'                    => 'string|max:100',
            'custom_meta'                    => 'array',
            'auto_login'                     => 'boolean',
            'verification_method'            => 'string|max:50',
            'verification_email_template'    => 'string',
            'verification_confirmation_page' => 'numeric',
            'hide_for_logged_in_message'     => 'string',
            'send_registration_email'        => 'boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function get_update_rules(): array {
        return array_merge(
            $this->get_store_rules(),
            [
                'registration_id' => 'required',
            ]
        );
    }

    /**
     * @param array<int, array> $registrations
     * @return int
     */
    private function next_registration_id( array $registrations ): int {
        $max = 0;
        foreach ( $registrations as $item ) {
            $id = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
            if ( $id > $max ) {
                $max = $id;
            }
        }
        return $max + 1;
    }

    /**
     * @param array<int, array> $registrations
     * @param mixed              $registration_id
     * @return int|null
     */
    private function find_registration_index( array $registrations, $registration_id ): ?int {
        foreach ( $registrations as $i => $item ) {
            $id = isset( $item['id'] ) ? $item['id'] : null;
            if ( (string) $id === (string) $registration_id ) {
                return $i;
            }
        }
        return null;
    }

    /**
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    private function sanitize_registration_item( array $raw ): array {
        $field_mapping = isset( $raw['field_mapping'] ) && is_array( $raw['field_mapping'] )
            ? map_deep( $raw['field_mapping'], 'sanitize_text_field' )
            : [];
        $custom_meta   = isset( $raw['custom_meta'] ) && is_array( $raw['custom_meta'] )
            ? array_map(
                function ( $row ) {
                    if ( ! is_array( $row ) ) {
                        return [ 'meta_key' => '', 'field' => '' ];
                    }
                    return [
                        'meta_key' => isset( $row['meta_key'] ) ? sanitize_text_field( (string) $row['meta_key'] ) : '',
                        'field'    => isset( $row['field'] ) ? sanitize_text_field( (string) $row['field'] ) : '',
                    ];
                },
                $raw['custom_meta']
            )
            : [];
        $auto_login    = ! array_key_exists( 'auto_login', $raw ) || ! empty( $raw['auto_login'] );
        $method        = isset( $raw['verification_method'] ) ? sanitize_key( (string) $raw['verification_method'] ) : UserVerificationService::METHOD_USER_EMAIL;
        $valid_methods = [
            UserVerificationService::METHOD_USER_EMAIL,
            UserVerificationService::METHOD_MANUAL,
        ];

        if ( ! in_array( $method, $valid_methods, true ) ) {
            $method = UserVerificationService::METHOD_USER_EMAIL;
        }

        return [
            'name'                           => isset( $raw['name'] ) ? sanitize_text_field( (string) $raw['name'] ) : '',
            'field_mapping'                  => $field_mapping,
            'user_role'                      => isset( $raw['user_role'] ) ? sanitize_text_field( (string) $raw['user_role'] ) : 'subscriber',
            'custom_role'                    => isset( $raw['custom_role'] ) ? sanitize_text_field( (string) $raw['custom_role'] ) : '',
            'custom_meta'                    => $custom_meta,
            'auto_login'                     => $auto_login,
            'verification_method'            => $auto_login ? UserVerificationService::METHOD_USER_EMAIL : $method,
            'verification_email_template'    => ! $auto_login && isset( $raw['verification_email_template'] ) ? wp_kses_post( (string) $raw['verification_email_template'] ) : '',
            'verification_confirmation_page' => ! $auto_login && isset( $raw['verification_confirmation_page'] ) ? absint( $raw['verification_confirmation_page'] ) : 0,
            'hide_for_logged_in_message'     => isset( $raw['hide_for_logged_in_message'] ) ? wp_kses_post( (string) $raw['hide_for_logged_in_message'] ) : '',
            'send_registration_email'        => ! empty( $raw['send_registration_email'] ),
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @throws Exception When verification settings are incomplete.
     */
    private function validate_registration_item( array $item ): void {
        if ( ! array_key_exists( 'auto_login', $item ) || ! empty( $item['auto_login'] ) ) {
            return;
        }

        if ( empty( $item['verification_confirmation_page'] ) ) {
            throw new Exception( esc_html__( 'User verification confirmation page is required.', 'formgent' ) );
        }

        $verification_method = (string) ( $item['verification_method'] ?? '' );
        $email_template      = trim( wp_strip_all_tags( (string) ( $item['verification_email_template'] ?? '' ) ) );

        if ( UserVerificationService::METHOD_USER_EMAIL === $verification_method && '' === $email_template ) {
            throw new Exception( esc_html__( 'Verification email template is required.', 'formgent' ) );
        }
    }

    /**
     * Find an existing registration email notification ID from existing feeds.
     *
     * @param array $registrations
     * @return int 0 if none found.
     */
    private function find_existing_registration_notification_id( array $registrations ): int {
        foreach ( $registrations as $item ) {
            if ( ! empty( $item['registration_email_notification_id'] ) ) {
                return absint( $item['registration_email_notification_id'] );
            }
        }
        return 0;
    }

    /**
     * Create a default email notification for user registration.
     *
     * @param int $form_id
     * @return int The created notification ID.
     */
    private function create_registration_email_notification( int $form_id ): int {
        $dto = ( new EmailNotificationDTO )
            ->set_form_id( $form_id )
            ->set_name( 'User Registration Email' )
            ->set_send_to( '{{user_email}}' )
            ->set_subject( 'Your account has been created' )
            ->set_body( 'Hi, your account has been created successfully. You can now log in to your account.' )
            ->set_from_name( '{{site_name}}' )
            ->set_from_email( '{{admin_email}}' )
            ->set_reply_to( '{{admin_email}}' )
            ->set_status( 'draft' );

        $repository = formgent_singleton( EmailNotificationRepository::class );

        return (int) $repository->create( $dto );
    }
}
