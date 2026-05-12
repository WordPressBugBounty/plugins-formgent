<?php defined( 'ABSPATH' ) || exit;

// Get form ID from block context
$form_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();

// Get user registrations from form settings
$user_registrations = function_exists( 'formgent_form_get_setting' ) ? formgent_form_get_setting( $form_id, 'user_registrations', [] ) : [];

// Get the first user registration's hide message
$hide_for_logged_in_message = '';
if ( is_array( $user_registrations ) && ! empty( $user_registrations ) ) {
    $first_registration = reset( $user_registrations );
    if ( isset( $first_registration['hide_for_logged_in_message'] ) ) {
        $hide_for_logged_in_message = $first_registration['hide_for_logged_in_message'];
    }
}

$show_logged_in_message = is_user_logged_in() && ! empty( $hide_for_logged_in_message );

// Replace placeholders in the message
if ( is_user_logged_in() && ! empty( $hide_for_logged_in_message ) ) {
    $current_user = wp_get_current_user();

    $hide_for_logged_in_message = str_replace(
        '{{user_name}}',
        esc_html( $current_user->display_name ),
        $hide_for_logged_in_message
    );

    $hide_for_logged_in_message = str_replace(
        '{{logout_url}}',
        esc_url( wp_logout_url() ),
        $hide_for_logged_in_message
    );
}

?>

<div
    data-wp-interactive="formgent/form"
    data-wp-context='<?php echo wp_json_encode(
        [
            'name'           => $attributes['name'],
            'parent_name'    => $attributes['name'],
            'field_type'     => 'register',
            'register_state' => [
                'is_submitting'   => false,
                'error_message'   => '',
                'success_message' => '',
            ],
        ]
    ); ?>'
    data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>"
    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
>
    <div
        class="formgent-register-logged-in-state"
        <?php if ( ! $show_logged_in_message ) : ?>
            hidden
        <?php endif; ?>
    >
        <p class="formgent-register-logged-in-message">
            <?php if ( is_user_logged_in() && ! empty( $hide_for_logged_in_message ) ) : ?>
                <?php echo wp_kses_post( $hide_for_logged_in_message ); ?>
            <?php endif; ?>
        </p>
    </div>

    <div
        class="formgent-register-block"
        <?php if ( is_user_logged_in() ) : ?>
            hidden
        <?php endif; ?>
    >
        <?php if ( ! is_user_logged_in() ) : ?>
            <div class="formgent-register-error"></div>
            <div class="formgent-register-success"></div>

            <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
                <?php if ( ! empty( $attributes['label'] ) ) : ?>
                    <label class="formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
                        <?php echo wp_kses_post( $attributes['label'] ); ?>
                    </label>
                <?php endif; ?>
                <?php
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $content;
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
