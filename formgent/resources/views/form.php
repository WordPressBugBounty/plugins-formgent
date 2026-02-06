<?php defined( 'ABSPATH' ) || exit;

use FormGent\App\Helpers\Form;
use FormGent\WpMVC\View\View;

if ( ! $form instanceof WP_Post || empty( $form->post_content ) || ! formgent_is_form_visible( $form ) ) {
    return;
}

do_action( 'formgent_before_load_form', $form );

wp_enqueue_script( 'lodash' );
wp_enqueue_script( 'wp-api-fetch' );
wp_enqueue_script( 'formgent/jquery-input-mask' );
wp_enqueue_script_module( 'formgent/blocks-frontend' );

$form_type       = formgent_get_form_type( $form->ID );
$blocks          = parse_blocks( $form->post_content );
$form->form_type = $form_type;

$form_helper = new Form;

if ( 'conversational' === $form_type ) {
    $data = $form_helper->get_conversational_form_field_settings( $blocks, true );
} else {
    $data = $form_helper->get_form_field_settings( $blocks, true, 'name', true );
}

// Check if we should show the custom submit button
$form_settings             = get_post_meta( $form->ID, '_formgent_form_settings', true );
$form_settings             = is_array( $form_settings ) ? $form_settings : [];
$show_custom_submit_button = false;
if ( 'general' === $form_type && ! $form_helper->has_inline_submit_button_or_page_break( $blocks ) && ! ( $form_settings['submit_button_disabled'] ?? false ) ) {
    $show_custom_submit_button = true;
}

$unique_id = str_replace( '-', '_', wp_unique_id( 'formgent-store' ) );

$is_enabled_honeypot_protection = 'yes' === formgent_settings_repository()->get_by_key( 'enable_honeypot_protection', 'yes' );

$confirmation  = formgent_form_get_setting( $form->ID, 'confirmation' );
$custom_script = formgent_form_get_setting( $form->ID, 'customScript', ['css' => '', 'js' => ''] );

$message = "";

if ( 'page' === $confirmation['type'] ) {
    $confirmation['page'] = get_page_uri( $confirmation['page'] );
} elseif ( 'message' === $confirmation['type'] ) {
    $message = htmlspecialchars( $confirmation['message'] ?? '', ENT_QUOTES, 'UTF-8' );
}

unset( $confirmation['message'] );

$context = apply_filters(
    'formgent_form_context', [
        'form_id'               => $form->ID,
        'blocks_settings'       => $data,
        'form_type'             => $form_type,
        'data'                  => formgent_form_default_values( $data ),
        'preset_values'         => formgent_get_preset_values( $form->ID ),
        'is_form_loaded'        => false,
        'is_partial_submitting' => false,
        'global'                => [
            'is_response_submitting'         => false,
            'is_response_token_generating'   => false,
            'is_enabled_honeypot_protection' => $is_enabled_honeypot_protection,
            'payment'                        => [
                'enable_summary'  => false,
                'payment_summary' => '',
                'items'           => [],
                'total_amount'    => '',
            ],
        ],
        'quiz_result'           => false,
        'confirmation'          => $confirmation,
        'plugin_url'            => WP_PLUGIN_URL,
        'external_data'         => [],
    ], $form
);

if ( isset( $external_data ) ) {
    $context['external_data'] = $external_data;
}

$context = apply_filters( 'formgent_form_context', $context, $form );

$is_multi_step = false;

if ( 'general' === $form_type ) {
    $types         = array_column( $data, 'field_type' );
    $is_multi_step = in_array( 'page-break', $types, true );
}

if ( $is_multi_step ) {
    $context['is_multi_step'] = $is_multi_step;
    $page                     = null;

    foreach ( $data as $key => $value ) {
        if ( $value['field_type'] === 'page-break' ) {
            $page = $key;
            break;
        }
    }

    $context['global']['page'] = $page;
}

$post            = get_post();
$GLOBALS['post'] = $form;

include __DIR__ . '/form-styles.php';
?>
<style>
    <?php
//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $custom_script['css'];
    ?>
</style>
<div class="formgent-form formgent-form-<?php echo esc_attr( $form->ID ) ?> formgent-form-<?php echo esc_attr( $form_type ) ?> <?php echo isset( $css_class ) ? esc_attr( $css_class ) : ''; ?>">
    <?php if ( 'general' === $form_type ) {?>
        <div class="formgent-confirmation-wrap formgent-confirmation-wrap--hidden">
            <div class="formgent-notices formgent-notices--classic-form" data-message="<?php echo $message; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped              ?>"></div>
            <div class="formgent-quiz-result"></div>
        </div>
    <?php }?>
    <form
        id="formgent-<?php echo esc_attr( $unique_id ) ?>"
        <?php
        if ( isset( $is_block ) ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo get_block_wrapper_attributes();
        }
        ?>
        data-wp-interactive="formgent/form"
        data-wp-context='<?php echo esc_attr( wp_json_encode( $context, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP ) ); ?>'
        data-wp-init="callbacks.<?php echo( 'conversational' !== $form_type ? 'initClassicForm' : 'initConversationalForm' ) ?>"
        data-wp-bind--disabled="context.global.is_response_submitting"
        data-wp-watch="callbacks.watchForm"
    >
        <?php
        do_action( 'formgent_form_top', $form, $context );

        if ( $is_multi_step ) {
            View::render( 'multi-step-indicator', compact( 'form', 'context' ) );
        }
        ?>
        <?php if ( 'conversational' === $form_type ) {?>
            <div data-wp-bind--hidden="context.is_form_loaded">
                <div class="formgent-form-preloader">
                    <span><?php formgent_render_icon( 'spinner' )?></span>
                </div>
            </div>
        <?php }?>
        <div class="formgent-field-list formgent-field-list--frontend" data-wp-class--formgent-field-list-loading="!context.is_form_loaded" data-wp-class--formgent-partial-submitting="!context.is_partial_submitting">
            <?php
//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo do_blocks( $form->post_content );
            ?>

            <?php if ( $show_custom_submit_button ) { ?>
                <!-- Custom Submit Button -->
                <?php View::render( 'submit-button', compact( 'form', 'form_settings' ) ); ?>
            <?php } ?>
        </div>
        <?php do_action( 'formgent_form_bottom', $form ); ?>
        <!-- Honeypot field -->
        <?php if ( $is_enabled_honeypot_protection ) {?>
            <input
                type="hidden"
                name="formgent-honeypot-<?php echo esc_attr( $form->ID ) ?>"
                id="formgent-honeypot-<?php echo esc_attr( $form->ID ) ?>"
            >
        <?php }?>
    </form>
</div>

<?php if ( ! empty( $custom_script['js'] ) ) : ?>
<script>
    document.addEventListener( 'DOMContentLoaded', function () {
        const currentFormId = parseInt( '<?php
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo esc_js( $form->ID ); ?>' );

        window.wp.hooks.addAction( 'formgent_form_before_init', 'formgent_custom_script', function ( id, context, element ) {
            if ( currentFormId !== id ) {
                return;
            }

            <?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $custom_script['js'];
            ?>
        } );
    } );
</script>
<?php endif; ?>

<?php $GLOBALS['post'] = $post; ?>