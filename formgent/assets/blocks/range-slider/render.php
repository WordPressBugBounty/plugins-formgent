<?php defined( 'ABSPATH' ) || exit;

// Ensure attributes exist with defaults
$attributes = wp_parse_args(
    $attributes ?? [],
    [
        'id'                 => '',
        'name'               => 'range-slider',
        'label'              => 'Range Slider',
        'label_alignment'    => 'top',
        'sub_label'          => '',
        'required'           => false,
        'slider_type'        => 'number',
        'left_label'         => 'Min',
        'right_label'        => 'Max',
        'value_prefix'       => '',
        'value_suffix'       => '',
        'min_value'          => 0,
        'max_value'          => 100,
        'default_value'      => 50,
        'step_increment'     => 1,
        'text_options'       => [],
        'text_default_index' => 0,
        'block_width'        => '100',
    ]
);

$slider_type       = $attributes['slider_type'];
$is_number_type    = $slider_type === 'number';
$text_options      = isset( $attributes['text_options'] ) && is_array( $attributes['text_options'] ) ? $attributes['text_options'] : [];
$text_options_json = ! empty( $text_options ) ? wp_json_encode( $text_options ) : '[]';

// For text type, ensure default index is within bounds
$text_default_index = $attributes['text_default_index'] ?? 0;
if ( ! $is_number_type && ! empty( $text_options ) ) {
    $text_default_index = min( $text_default_index, count( $text_options ) - 1 );
    $text_default_index = max( $text_default_index, 0 );
}

$context = [
    'name'         => $attributes['name'],
    'field_type'   => 'range-slider',
    'slider_type'  => $slider_type,
    'text_options' => $text_options,
    'sliderState'  => [
        'value'      => $is_number_type ? $attributes['default_value'] : $text_default_index,
        'valueLabel' => '',
    ],
];

?>
<div 
    data-wp-interactive="formgent/form" 
    data-wp-context='<?php echo wp_json_encode( $context ); ?>' 
    data-wp-bind--hidden="state.hideField" 
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>"
    data-wp-init="callbacks.initRangeSlider"
>
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                class="formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
                <?php if ( $attributes['required'] ) : ?>
                    <span class="formgent-field-required">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>
        
        <div class="formgent-field-single__wrapper">
            <div class="formgent-range-slider">
                <div 
                    class="formgent-range-slider__control"
                >
                    <div 
                        class="formgent-slider-wrap"
                        id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                        data-slider-min="<?php echo esc_attr( $is_number_type ? $attributes['min_value'] : 0 ); ?>"
                        data-slider-max="<?php echo esc_attr( $is_number_type ? $attributes['max_value'] : max( count( $text_options ) - 1, 0 ) ); ?>"
                        data-slider-step="<?php echo esc_attr( $is_number_type ? $attributes['step_increment'] : 1 ); ?>"
                        data-value-prefix="<?php echo esc_attr( $attributes['value_prefix'] ); ?>"
                        data-value-suffix="<?php echo esc_attr( $attributes['value_suffix'] ); ?>"
                        data-slider-type="<?php echo esc_attr( $slider_type ); ?>"
                        data-default-value="<?php echo esc_attr( $attributes['default_value'] ); ?>"
                        data-default-index="<?php echo esc_attr( $text_default_index ); ?>"
                        data-text-options='<?php echo esc_attr( $text_options_json ); ?>'
                        tabindex="0"
                        role="slider"
                        aria-label="<?php echo esc_attr( $attributes['label'] ?: 'Range Slider' ); ?>"
                        aria-valuemin="<?php echo esc_attr( $is_number_type ? $attributes['min_value'] : 0 ); ?>"
                        aria-valuemax="<?php echo esc_attr( $is_number_type ? $attributes['max_value'] : max( count( $text_options ) - 1, 0 ) ); ?>"
                        aria-valuenow="<?php echo esc_attr( $is_number_type ? $attributes['default_value'] : $text_default_index ); ?>"
                        aria-valuetext="<?php echo esc_attr( $is_number_type ? $attributes['value_prefix'] . $attributes['default_value'] . $attributes['value_suffix'] : ( isset( $text_options[ $text_default_index ]['label'] ) ? $text_options[ $text_default_index ]['label'] : '' ) ); ?>"
                        data-wp-on--pointerdown="actions.rangeSliderPointerDown"
                        data-wp-on--pointermove="actions.rangeSliderPointerMove"
                        data-wp-on--pointerup="actions.rangeSliderPointerUp"
                        data-wp-on--pointercancel="actions.rangeSliderPointerUp"
                        data-wp-on--keydown="actions.rangeSliderKeyDown"
                        data-wp-on--focus="actions.rangeSliderFocus"
                        data-wp-on--blur="actions.rangeSliderBlur"
                    >
                        <div 
                            class="formgent-slider"
                            data-wp-style--width="state.getSliderProgress"
                        ></div>
                            <span 
                                class="formgent-slider-thumb"
                                data-wp-style--left="state.getThumbPosition"
                            ></span>
                        <div 
                            class="formgent-slider-tooltip"
                            data-wp-style--left="state.getSliderProgress"
                        >
                            <span data-wp-text="context.sliderState.valueLabel"></span>
                        </div>
                    </div>
                </div>
                
                <?php if ( $is_number_type && ( ( $attributes['left_label'] ?? '' ) !== '' || ( $attributes['right_label'] ?? '' ) !== '' ) ) : ?>
                    <div class="formgent-range-slider__labels">
                        <?php if ( ( $attributes['left_label'] ?? '' ) !== '' ) : ?>
                            <span class="formgent-range-slider__label-left">
                                <?php echo esc_html( $attributes['left_label'] ); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( ( $attributes['right_label'] ?? '' ) !== '' ) : ?>
                            <span class="formgent-range-slider__label-right">
                                <?php echo esc_html( $attributes['right_label'] ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! $is_number_type && ! empty( $text_options ) && is_array( $text_options ) ) : ?>
                    <div class="formgent-range-slider__text-labels">
                        <?php 
                        $last_index = count( $text_options ) - 1;
                        foreach ( $text_options as $index => $option ) : 
                            // Only show first and last label
                            if ( $index !== 0 && $index !== $last_index ) {
                                continue;
                            }
                            ?>
                            <span 
                                class="formgent-range-slider__text-label"
                                data-option-index="<?php echo esc_attr( $index ); ?>"
                                data-wp-class--formgent-range-slider__text-label--active="state.isRangeOptionActive"
                            >
                                <?php echo esc_html( isset( $option['label'] ) ? $option['label'] : 'Option ' . ( $index + 1 ) ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <input
                type="hidden"
                class="formgent-validate-field-input"
                name="<?php echo esc_attr( $attributes['name'] ); ?>"
                data-wp-bind--value="state.getSliderSubmittedValue"
                <?php echo $attributes['required'] ? 'required' : ''; ?>
            />
            
            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

