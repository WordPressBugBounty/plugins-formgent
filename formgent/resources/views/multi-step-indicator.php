<?php

defined( "ABSPATH" ) || exit;

$form_settings      = get_post_meta( $form->ID, '_formgent_form_settings', true );
$page_break_setting = get_post_meta( $form->ID, '_formgent_page_break_settings', true );

$i = 1;
?>

<!-- Progress bar -->
<?php if ( isset( $form_settings['page_break_progress_indicator'] ) && 'progress_bar' === $form_settings['page_break_progress_indicator'] ) {

    $page = [];

    foreach ( $context['blocks_settings'] as $key => $value ) {
        if ( $value['field_type'] === 'page-break' ) {
            $page = $value;
            break;
        }
    }
    ?>
    <div class="formgent-form-progress-bar">
         <span
            class="formgent-form-progress-bar__label"
            style="--formgent-page-break-label-color: <?php echo esc_attr( $form_settings['field_colors']['label']['default'] ?? '#000000' ); ?>;"
        >
            <?php if ( isset( $form_settings['show_page_break_labels'] ) && $form_settings['show_page_break_labels'] ) {?>
                <span class="formgent-form-progress-bar__current-step" data-wp-text="state.pageTitle"><?php echo esc_html( $page['step_text'] ); ?></span>
            <?php }?>
            <span class="formgent-form-progress-bar__total-steps">
            <?php
            echo sprintf(
            /* translators: %1$s is the current page number, %2$s is the total number of pages */
                esc_html__( 'Page %1$s of %2$s', 'formgent' ),
                '<span data-wp-text="state.getCurrentPageNo">1</span>',
                '<span data-wp-text="state.totalPage"></span>'
            );
            ?>
    </span>
    </span>
    <div class="formgent-form-progress-bar__progress">
        <div class="formgent-form-progress-bar__progress-bar" data-wp-style--width="state.progressWidth"></div>
    </div>
</div>
<?php }?>

<!-- Progress step -->
<?php if ( isset( $form_settings['page_break_progress_indicator'] ) && 'steps' === $form_settings['page_break_progress_indicator'] ) {?>
<div class="formgent-form-progress-step">
    <?php foreach ( $context['blocks_settings'] as $key => $block ) {
        if ( 'page-break' !== $block['field_type'] ) {
            continue;
        }
        ?>
    <div class="formgent-form-progress-step__item" data-wp-context='{ "name": "<?php echo esc_attr( $block['name'] ); ?>" }' data-wp-class--formgent-form-progress-step__item--completed="state.isMultiStepCompleted" style="--formgent-page-break-label-color: <?php echo esc_attr( $form_settings['field_colors']['label']['default'] ?? '#000000' ); ?>">
        <span class="formgent-form-progress-step__item-label"
            <?php if ( isset( $form_settings['show_page_break_labels'] ) && $form_settings['show_page_break_labels'] ) : ?>
                title="<?php echo esc_attr( $block['step_text'] ); ?>"
            <?php endif; ?>
        >
            <?php
            if ( isset( $form_settings['show_page_break_labels'] ) && $form_settings['show_page_break_labels'] ) {
                    echo esc_html( $block['step_text'] );
            } else {
                /* translators: %s is the step number */
                echo sprintf( esc_html__( "Step %s", "formgent" ), esc_html( $i ) );
            }
            ?>
        </span>
        <div class="formgent-form-progress-step__item-style">
            <div class="formgent-form-progress-step__item-icon display-none" data-wp-bind--hidden="state.isMultiStepCompleted">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                    <circle cx="11" cy="11" r="10.5" fill="white" stroke="#D2D6DB"/>
                    <circle cx="11" cy="11" r="4" fill="#D2D6DB"/>
                </svg>
            </div>
            <div class="formgent-form-progress-step__item-icon display-none" data-wp-bind--hidden="!state.isMultiStepCompleted">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                    <circle cx="11" cy="11" r="11" fill="#5E53F9"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.7709 8.2291C16.0764 8.53457 16.0764 9.02983 15.7709 9.3353L10.4028 14.7034C10.0973 15.0089 9.60203 15.0089 9.29656 14.7034L6.2291 11.636C5.92363 11.3305 5.92363 10.8352 6.2291 10.5298C6.53457 10.2243 7.02983 10.2243 7.3353 10.5298L9.84966 13.0441L14.6647 8.2291C14.9702 7.92363 15.4654 7.92363 15.7709 8.2291Z" fill="white"/>
                </svg>
            </div>
            <span class="formgent-form-progress-step__item-line"></span>
        </div>
    </div>
        <?php $i++;
    }?>
</div>
<?php }?>
