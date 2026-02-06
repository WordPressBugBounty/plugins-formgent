<?php

defined( 'ABSPATH' ) || exit;

$quiz_is_enabled = false;
$form            = get_post();

if ( $form instanceof WP_Post ) {
    $quiz_settings   = formgent_form_repository()->get_setting_by_key( $form->ID, 'quiz' );
    $quiz_is_enabled = isset( $quiz_settings['is_enabled'] ) ? $quiz_settings['is_enabled'] : false;
}

?>
<style>
    /* .display-none {
        display: none;
    } */
</style>

<div
    data-wp-interactive="formgent/form"
    data-wp-init="callbacks.initConversationalStep"
    class="formgent-step-layout-wrapper"
    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
    data-wp-context='{ "root_parent_name": "<?php echo esc_attr( $attributes['id'] ); ?>" }'
    data-wp-bind--hidden="!state.showStep"
>
    <div class="formgent-step-layout formgent-step-layout--media_none display-none formgent-step-layout--end">
        <div class="formgent-step-layout__blocks">
            <div class="formgent-step-layout__blocks__content">
                <div data-wp-bind--hidden="!context.global.is_response_submitting" class="formgent-submission-loader__backdrop">
                    <div class="formgent-submission-loader__content">
                        <div class="formgent-submission-loader__content-box">
                            <div class="formgent-submission-loader__spinner">
                                <span class="formgent-submission-loader__icon"></span>
                            </div>
                            <?php if ( $quiz_is_enabled ) : ?>
                                <div class="formgent-submission-loader__text">Quiz submitting</div>
                                <div class="formgent-submission-loader__sub-text">You are about to submit your quiz</div>
                            <?php else : ?>
                                <div class="formgent-submission-loader__text">Submitting ...</div>
                                <div class="formgent-submission-loader__sub-text">Please wait while we process your form</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div data-wp-bind--hidden="context.global.is_response_submitting">
                    <?php
                        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo $content;
                    ?>
                </div>

                <?php if ( $quiz_is_enabled ) : ?>
                    <div class="formgent-quiz-result"></div>
                <?php endif; ?>

                <?php if ( ! function_exists( 'formgent_pro' ) ) : ?>
                    <a href="https://formgent.com" class="formgent-brand-btn">
                        <?php formgent_render_icon( 'logo' ); ?>
                        <span>Make your own form, It's free</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>