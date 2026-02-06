<?php defined( 'ABSPATH' ) || exit; ?>
<style>
    /* .display-none {
        display: none;
    } */
</style>

<?php
    $object_position = isset( $attributes['media']['focalPoint'] )
    ? ( $attributes['media']['focalPoint']['x'] * 100 ) . '% ' . ( $attributes['media']['focalPoint']['y'] * 100 ) . '%'
    : 'center center';
?>

<div
    data-wp-interactive="formgent/form"
    data-wp-init="callbacks.initConversationalStep"
    class="formgent-step-layout-wrapper"
    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
    data-wp-context='{ "root_parent_name": "<?php echo esc_attr( $attributes['id'] ); ?>" }'
    data-wp-bind--hidden="!state.showStep"
    style="--formgent-overlay-opacity: <?php echo esc_attr( $attributes['media_brightness'] ); ?>%"
>
    <div class="formgent-step-layout formgent-step-layout--<?php echo esc_attr( $attributes['layout'] ); ?> display-none">
        <div class="formgent-step-layout__blocks">
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
                                                <picture
                                                    style="display: block; margin: 0 auto; width: 100%; height: 100%; object-fit: cover; object-position:
                                                    <?php
                                                        echo esc_attr( $object_position );
                                                    ?>;"
                                                >
                                                    <source
                                                        media="(prefers-reduced-motion: reduce)"
                                                        srcset="<?php echo esc_url( $attributes['media']['url'] ); ?>"
                                                    />
                                                    <img
                                                        src="<?php echo esc_url( $attributes['media']['url'] ); ?>"
                                                        alt="Media"
                                                        style="display: block; margin: 0 auto; width: 100%; height: 100%; object-fit: cover; object-position:
                                                        <?php
                                                            echo esc_attr( $object_position );
                                                        ?>;"
                                                    />
                                                </picture>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ( $attributes['media']['type'] === 'video' ) : ?>
                                    <div class="formgent-media-src__video">
                                        <?php if ( ! empty( $attributes['media'] ) ) : ?>
                                            <video
                                                autoplay
                                                loop
                                            >
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
                                            <span
                                                class="formgent-video-control"
                                            >
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