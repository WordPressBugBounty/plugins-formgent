<?php defined( 'ABSPATH' ) || exit;

$form_settings = get_post_meta( $form->ID, '_formgent_form_settings', true );

$process_dimensions = function ( $dimensions ) {
    if ( ! is_array( $dimensions ) ) {
        return '0';
    }

    $top    = $dimensions['top'] ?? '0';
    $right  = $dimensions['right'] ?? '0';
    $bottom = $dimensions['bottom'] ?? '0';
    $left   = $dimensions['left'] ?? '0';

    return "$top $right $bottom $left";
};

$default_padding = [
    'top'    => '20px',
    'right'  => '20px',
    'bottom' => '40px',
    'left'   => '20px',
];

$process_border_radius = function ( $border_radius, $default_radius = '16px' ) {
    // If not an array, apply default radius to all corners
    if ( ! is_array( $border_radius ) ) {
        return "{$default_radius} {$default_radius} {$default_radius} {$default_radius}";
    }

    // Normalize each corner with fallback to default
    $top_left     = $border_radius['topLeft']     ?? $default_radius;
    $top_right    = $border_radius['topRight']    ?? $default_radius;
    $bottom_right = $border_radius['bottomRight'] ?? $default_radius;
    $bottom_left  = $border_radius['bottomLeft']  ?? $default_radius;

    return "{$top_left} {$top_right} {$bottom_right} {$bottom_left}";
};

?>

<style>
    :root {
        /* Form variables */
        --formgent-form-background-color: <?php echo esc_attr( $form_settings['form_background_color'] ?? '#ffffff' ); ?>;
        --formgent-form-background-image: <?php echo esc_attr( ( isset( $form_settings['form_background'] ) && $form_settings['form_background'] === 'image' ) ? 'url(' . $form_settings['form_background_image']['url'] . ')' : '' ); ?>;
        --formgent-form-background-image-position:
        <?php
        $x = isset( $form_settings['form_background_image']['focalPoint']['x'] )
        ? ( $form_settings['form_background_image']['focalPoint']['x'] * 100 ) . '%'
        : 'center';

        $y = isset( $form_settings['form_background_image']['focalPoint']['y'] )
        ? ( $form_settings['form_background_image']['focalPoint']['y'] * 100 ) . '%'
        : 'center';

        echo esc_attr( "$x $y" );
        ?>;

        --formgent-form-padding-desktop: <?php echo esc_attr( $process_dimensions( $form_settings['form_padding']['desktop'] ?? $default_padding ) ); ?>;
        --formgent-form-padding-tablet: <?php echo esc_attr( $process_dimensions( $form_settings['form_padding']['tablet'] ?? $default_padding ) ); ?>;
        --formgent-form-padding-mobile: <?php echo esc_attr( $process_dimensions( $form_settings['form_padding']['mobile'] ?? $default_padding ) ); ?>;
        --formgent-form-margin-desktop: <?php echo esc_attr( $process_dimensions( $form_settings['form_margin']['desktop'] ?? [] ) ); ?>;
        --formgent-form-margin-tablet: <?php echo esc_attr( $process_dimensions( $form_settings['form_margin']['tablet'] ?? [] ) ); ?>;
        --formgent-form-margin-mobile: <?php echo esc_attr( $process_dimensions( $form_settings['form_margin']['mobile'] ?? [] ) ); ?>;
        --formgent-form-border-width: <?php echo esc_attr( $form_settings['form_border']['border']['width'] ?? '0' ); ?>;
        --formgent-form-border: <?php echo esc_attr( $form_settings['form_border']['border']['width'] ?? '0' ); ?> <?php echo esc_attr( $form_settings['form_border']['border']['style'] ?? 'solid' ); ?> <?php echo esc_attr( $form_settings['form_border']['border']['color'] ?? 'transparent' ); ?>;
        --formgent-form-border-radius: <?php echo esc_attr( $process_border_radius( $form_settings['form_border']['radius'] ?? [], '16px' ) ); ?>;

        /* Field variables */
        --formgent-field-vertical-spacing-desktop: <?php echo esc_attr( $form_settings['field_vertical_spacing']['desktop'] ?? '35px' ); ?>;
        --formgent-field-vertical-spacing-tablet: <?php echo esc_attr( $form_settings['field_vertical_spacing']['tablet'] ?? '35px' ); ?>;
        --formgent-field-vertical-spacing-mobile: <?php echo esc_attr( $form_settings['field_vertical_spacing']['mobile'] ?? '35px' ); ?>;
        --formgent-field-horizontal-spacing-desktop: <?php echo esc_attr( $form_settings['field_horizontal_spacing']['desktop'] ?? '15px' ); ?>;
        --formgent-field-horizontal-spacing-tablet: <?php echo esc_attr( $form_setting['field_horizontal_spacing']['tablet'] ?? '15px' ) ?>;
        --formgent-field-horizontal-spacing-mobile: <?php echo esc_attr( $form_settings['field_horizontal_spacing']['mobile'] ?? '15px' ); ?>;
        --formgent-field-background-color: <?php echo esc_attr( $form_settings['field_colors']['background']['default'] ?? 'transparent' ); ?>;
        --formgent-field-background-color-hover: <?php echo esc_attr( $form_settings['field_colors']['background']['hover'] ?? 'transparent' ); ?>;
        --formgent-field-placeholder-color: <?php echo esc_attr( $form_settings['field_colors']['placeholder']['default'] ?? '#4d5761' ); ?>;
        --formgent-field-label-color: <?php echo esc_attr( $form_settings['field_colors']['label']['default'] ?? '#000000' ); ?>;
        --formgent-field-description-color: <?php echo esc_attr( $form_settings['field_colors']['description']['default'] ?? '#747c89' ); ?>;
        --formgent-field-error-message-color: <?php echo esc_attr( $form_settings['field_colors']['error']['default'] ?? '#c83a3a' ); ?>;
        --formgent-field-input-color: <?php echo esc_attr( $form_settings['field_colors']['input_color']['default'] ?? '#4d5761' ) ?> ;
        --formgent-field-options-color: <?php echo esc_attr( $form_settings['field_colors']['options_color']['default'] ?? '#4d5761' ); ?>;
        --formgent-field-border: <?php echo esc_attr( $form_settings['field_border']['border']['width'] ?? '1px' ); ?> <?php echo esc_attr( $form_settings['field_border']['border']['style'] ?? 'solid' ); ?> <?php echo esc_attr( $form_settings['field_border']['border']['color'] ?? '#d2d6db' ); ?>;
        --formgent-field-border-radius: <?php echo esc_attr( $process_border_radius( $form_settings['field_border']['radius'] ?? [], '8px' ) ); ?>;
        --formgent-field-rating-color: <?php echo esc_attr( $form_settings['field_colors']['rating_color']['default'] ?? '#a1a9b2' ); ?>;
        --formgent-field-rating-color-hover: <?php echo esc_attr( $form_settings['field_colors']['rating_color']['hover'] ?? '#5e53f9' ); ?>;
        --formgent-field-range-slider-color: <?php echo esc_attr( $form_settings['field_colors']['range_slider_color']['default'] ?? '#000000' ); ?>;
        /* Fixed submit button variables */
        --formgent-fixed-btn-bg-color: <?php echo esc_attr( $form_settings['submit_button_background_color'] ?? '#000000' ); ?>;
        --formgent-fixed-btn-text-color: <?php echo esc_attr( $form_settings['submit_button_text_color'] ?? '#ffffff' ); ?>;
        --formgent-fixed-btn-border-color: <?php echo esc_attr( $form_settings['submit_button_border_color'] ?? '#000000' ); ?>;
        --formgent-fixed-btn-style: <?php echo esc_attr( $form_settings['submit_button_style'] ?? 'default' ); ?>;
        --formgent-fixed-btn-alignment: <?php echo esc_attr( $form_settings['submit_button_alignment'] ?? 'left' ); ?>;
    }
    .display-none {
        display: none;
    }
</style>