<?php defined( 'ABSPATH' ) || exit; ?>

<?php
$object_position = isset( $attributes['media']['focalPoint'] )
    ? ( $attributes['media']['focalPoint']['x'] * 100 ) . '% ' . ( $attributes['media']['focalPoint']['y'] * 100 ) . '%'
    : 'center center';

$settings                 = get_post_meta( get_post()->ID, '_formgent_form_settings', true );
$use_label_as_placeholder = ! empty( $settings['use_label_as_placeholder'] ?? null );

/**
 * When "Use Label as Placeholder" is enabled, process the step's inner content:
 * 1. Extract the label text from the core/heading block
 * 2. Set it as placeholder on input/textarea elements (and data-placeholder on select)
 * 3. Hide the heading and description (core/paragraph) blocks
 *
 * This is needed because in conversational forms, the label text lives in a
 * core/heading block (not in the field block's own label attribute which is empty).
 */
// Blocks that don't support placeholders — skip processing for these
$no_placeholder_blocks = [
    'formgent/html',
    'formgent/single-choice',
    'formgent/multiple-choice',
    'formgent/gdpr',
    'formgent/file-upload',
    'formgent/rating',
    'formgent/range-slider',
    'formgent/digital-signature'
];

$has_no_placeholder_block = false;
if ( isset( $block ) && ! empty( $block->inner_blocks ) ) {
    foreach ( $block->inner_blocks as $inner_block ) {
        if ( in_array( $inner_block->name, $no_placeholder_blocks, true ) ) {
            $has_no_placeholder_block = true;
            break;
        }
    }
}

if ( $use_label_as_placeholder && ! empty( $content ) && ! $has_no_placeholder_block ) {
    // Suppress DOMDocument warnings for HTML5 elements/attributes
    $previous_errors = libxml_use_internal_errors( true );

    $dom = new DOMDocument();
    $dom->loadHTML(
        '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $content . '</body></html>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    $xpath      = new DOMXPath( $dom );
    $label_text = '';

    // Extract text from the first heading element (h1-h6) — this is the label
    $headings = $xpath->query( '//h1|//h2|//h3|//h4|//h5|//h6' );
    if ( $headings->length > 0 ) {
        $heading    = $headings->item( 0 );
        $label_text = trim( wp_strip_all_tags( $dom->saveHTML( $heading ) ) );

        // Hide the heading by setting display:none
        $existing_style = $heading->getAttribute( 'style' );
        $heading->setAttribute( 'style', $existing_style . ';display:none !important;' );
    }

    // Hide the first core/paragraph (description) block — identified by wp-block-paragraph class
    $paragraphs = $xpath->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' wp-block-paragraph ')]" );
    if ( $paragraphs->length > 0 ) {
        $paragraph      = $paragraphs->item( 0 );
        $existing_style = $paragraph->getAttribute( 'style' );
        $paragraph->setAttribute( 'style', $existing_style . ';display:none !important;' );
    }

    // Set the label text as placeholder on input and textarea elements
    if ( ! empty( $label_text ) ) {
        $inputs = $xpath->query( '//input[@class and contains(@class, "formgent-field-single__input")]|//textarea[@class and contains(@class, "formgent-field-single__input")]' );
        foreach ( $inputs as $input ) {
            $current_placeholder = $input->getAttribute( 'placeholder' );
            if ( empty( $current_placeholder ) ) {
                $input->setAttribute( 'placeholder', esc_attr( $label_text ) );
            }
        }

        // Set data-placeholder on select elements (used by dropdown)
        $selects = $xpath->query( '//select[@class and contains(@class, "formgent-field-single__input")]' );
        foreach ( $selects as $select_el ) {
            $current_placeholder = $select_el->getAttribute( 'data-placeholder' );
            if ( empty( $current_placeholder ) ) {
                $select_el->setAttribute( 'data-placeholder', esc_attr( $label_text ) );
            }
        }
    }

    // Extract the modified content back from the body
    $body    = $dom->getElementsByTagName( 'body' )->item( 0 );
    $content = '';
    //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
    foreach ( $body->childNodes as $child ) {
        $content .= $dom->saveHTML( $child );
    }

    libxml_clear_errors();
    libxml_use_internal_errors( $previous_errors );
}
?>

<div data-wp-interactive="formgent/form" data-wp-init="callbacks.initConversationalStep"
    class="formgent-step-layout-wrapper" id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
    data-wp-context='{ "root_parent_name": "<?php echo esc_attr( $attributes['id'] ); ?>" }'
    data-wp-bind--hidden="!state.showStep"
    style="--formgent-overlay-opacity: <?php echo esc_attr( $attributes['media_brightness'] ); ?>%">
    <div class="display-none">
        <div class="formgent-step-layout formgent-step-layout--<?php echo esc_attr( $attributes['layout'] ); ?>">
            <div class="formgent-step-layout__blocks">
                <!-- Previous -->
                <div class="formgent-prev-navigation display-none">
                    <button type="button" data-wp-bind--hidden="!state.showPrevButton"
                        data-wp-on--click="actions.prevStep">
                        <?php formgent_render_icon( 'arrow-left' ) ?>
                    </button>
                    <span data-wp-text="state.stepCounter"></span>
                </div>

                <?php
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $content;
                ?>
            </div>
            <?php if ( esc_attr( $attributes['layout'] ) !== 'media_none' ) : ?>
                <div class="formgent-step-layout__media">
                    <?php if ( empty( $attributes['media'] ) || ! isset( $attributes['media']['type'] ) ) : ?>
                        <div class="formgent-media-empty"></div>
                    <?php endif; ?>

                    <?php if ( isset( $attributes['media']['type'] ) && ! empty( $attributes['media']['type'] ) ) : ?>
                        <div class="formgent-media-preview">
                            <div class="formgent-media-src">
                                <?php if ( isset( $attributes['media']['type'] ) ) : ?>
                                    <?php if ( $attributes['media']['type'] === 'image' ) : ?>
                                        <div class="formgent-media-src__image">
                                            <span class="formgent-media-src__overlay"></span>
                                            <?php if ( ! empty( $attributes['media'] ) ) : ?>
                                                <div class="formgent-image-wrapper">
                                                    <picture style="display: block; margin: 0 auto; width: 100%; height: 100%; object-fit: cover; object-position:
                                                        <?php
                                                        echo esc_attr( $object_position );
                                                        ?>;">
                                                        <source media="(prefers-reduced-motion: reduce)"
                                                            srcset="<?php echo esc_url( $attributes['media']['url'] ); ?>" />
                                                        <img src="<?php echo esc_url( $attributes['media']['url'] ); ?>" alt="Media" style="display: block; margin: 0 auto; width: 100%; height: 100%; object-fit: cover; object-position:
                                                            <?php
                                                            echo esc_attr( $object_position );
                                                            ?>;" />
                                                    </picture>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ( $attributes['media']['type'] === 'video' ) : ?>
                                        <div class="formgent-media-src__video">
                                            <?php if ( ! empty( $attributes['media'] ) ) : ?>
                                                <video autoplay loop>
                                                    <source src="<?php echo esc_url( $attributes['media']['url'] ); ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <div class="formgent-video-visualization">
                                                    <span class="formgent-video-visualization__timer">
                                                        00:00 / 00:00
                                                    </span>
                                                    <span class="formgent-video-visualization__fullScreen">
                                                        <?php formgent_render_icon( 'expand-alt' ) ?>
                                                    </span>
                                                </div>
                                                <span class="formgent-video-control">
                                                    <?php formgent_render_icon( 'pause' ) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>