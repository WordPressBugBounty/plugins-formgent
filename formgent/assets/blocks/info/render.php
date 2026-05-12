<?php defined( 'ABSPATH' ) || exit; ?>

<div class="formgent-field-single formgent-field-single--info">
    <?php if ( isset( $attributes['info'] ) && $attributes['info'] === 'question_count' || $attributes['info'] === 'both' ) : ?>
        <span>
            <?php formgent_render_icon( 'clipboard', 'general' ) ?>
            <span data-wp-text="state.totalStep"></span>
        </span>
    <?php endif; ?>

    <?php if ( isset( $attributes['info'] ) && $attributes['info'] === 'time_to_complete' || $attributes['info'] === 'both' ) : ?>
        <span>
            <?php formgent_render_icon( 'clock', 'general' ) ?>
            <span data-wp-text="state.timeToComplete"></span>
        </span>
    <?php endif; ?>
</div>
