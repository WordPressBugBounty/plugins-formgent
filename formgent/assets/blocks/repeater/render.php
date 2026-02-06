<?php defined( 'ABSPATH' ) || exit;
// Check if we're in the editor context
$is_editor = ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->editor ?? null, 'is_edit_mode' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->preview ?? null, 'is_preview_mode' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) );
?>
<div data-wp-interactive="formgent/form" data-wp-context='{ "repeater_name": "<?php echo esc_attr( $attributes['name'] ); ?>", "repeater_items": [ 0 ] }' data-wp-init="callbacks.initRepeater" data-wp-bind--hidden="state.hideField" class="display-none formgent-field formgent-field-repeater formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>" id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) ) : ?>
            <span class="formgent-field-label formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
                <?php echo wp_kses_post( $attributes['label'] ); ?>
            </span>
        <?php endif; ?>
        <div class="formgent-repeater-items" style="<?php echo $is_editor ? 'border: 1px solid var(--formgent-color-gray-200); padding: 20px 20px 0; border-radius: 8px;' : ''; ?>">
            <?php
            // Render the first repeater item (0) as real DOM (not inside <template>).
            // This avoids a nested <template> edge-case where choice options
            // (single-choice / multiple-choice) don't render for the initial item.
            $repeater_content = function_exists( 'wp_interactivity_process_directives' )
                ? wp_interactivity_process_directives( $content )
                : $content;
            ?>
            <div class="formgent-repeater-item" data-wp-class--formgent-repeater-item-first="state.isRepeaterInitialItem" data-wp-context='{ "repeater_item": 0 }'>
                <?php
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $repeater_content;
                ?>
                <?php if ( ! $is_editor ) : ?>
                <span class="formgent-repeater-remove" data-wp-on--click="actions.onRemoveRepeaterItem" data-wp-on--keydown="actions.handleRepeaterRemoveKeydown" data-wp-class--formgent-repeater-remove-hide="!state.hasMultipleRepeaterItem" tabindex="0">
                    <?php formgent_render_icon( 'trash' ); ?>
                    Remove Item
                </span>
                <?php endif; ?>
            </div>

            <template data-wp-each--repeater_item="context.repeater_items">
                <div class="formgent-repeater-item" data-wp-bind--hidden="state.hideRepeaterTemplateItem">
                    <?php
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo $repeater_content;
                    ?>
                    <?php if ( ! $is_editor ) : ?>
                    <span class="formgent-repeater-remove" data-wp-on--click="actions.onRemoveRepeaterItem" data-wp-on--keydown="actions.handleRepeaterRemoveKeydown" data-wp-class--formgent-repeater-remove-hide="!state.hasMultipleRepeaterItem" tabindex="0">
                        <?php formgent_render_icon( 'trash' ); ?>
                        Remove Item
                    </span>
                    <?php endif; ?>
                </div>
            </template>
        </div>
        <span class="formgent-repeater-add" data-wp-on--click="actions.onAddRepeaterItem" data-wp-on--keydown="actions.handleRepeaterAddKeydown" tabindex="0">
            <?php formgent_render_icon( 'plus-circle' ); ?>
            Add another
        </span>
    </div>
</div>