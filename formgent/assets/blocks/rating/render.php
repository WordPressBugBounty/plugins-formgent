<?php defined( 'ABSPATH' ) || exit;

// Ensure attributes exist with defaults
$attributes = wp_parse_args(
    $attributes ?? [],
    [
        'id'              => '',
        'name'            => '',
        'label'           => '',
        'label_alignment' => 'left',
        'required'        => false,
        'rating_limit'    => 5,
        'rating_icon'     => 'star',
        'sub_label'       => '',
    ]
);

// Prepare variables
$rating_amount  = $attributes['rating_limit'] ?? 5;
$selected_icon  = $attributes['rating_icon'];
$default_rating = $attributes['default_rating'] ?? 0;

// Get the rating limit from attributes, default to 5 if not set
$rating_limit = isset( $attributes['rating_limit'] ) ? max( 1, min( 10, intval( $attributes['rating_limit'] ) ) ) : 5;

// Generate rating options dynamically based on rating_limit
$rating_options = [];
for ( $i = 1; $i <= $rating_limit; $i++ ) {
    $rating_options[] = [
        'rating' => $i,
        'normal' => false,
        'solid'  => true,
    ];
}

$context = [
    'name'          => $attributes['name'],
    'ratingState'   => [
        'rating'        => 0,
        'focusedRating' => 1,
    ],
    'ratingOptions' => $rating_options,
    'rating_limit'  => $rating_limit,
];

// External editor star rendering
$is_external_editor_star = function() use ( $rating_amount, $selected_icon ) {
    ?>
    <?php for ( $i = 0; $i < $rating_amount; $i++ ) : ?>
        <span>
            <?php
            $icon_path = formgent_dir( "resources/blocks-icon/{$selected_icon}.svg" );
            if ( is_file( $icon_path ) ) {
                //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                $svg_content = file_get_contents( $icon_path );
                if ( $svg_content !== false ) {
                    echo wp_kses(
                        $svg_content,
                        [
                            'svg'  => [
                                'width'   => [],
                                'height'  => [],
                                'viewBox' => [], // Capital B for SVG compatibility
                                'xmlns'   => [],
                            ],
                            'path' => [
                                'd'    => [],
                                'fill' => [],
                            ],
                        ]
                    );
                }
            }
            ?>
        </span>
    <?php endfor; ?>
    <?php
};

// Frontend star rendering
$is_frontend_star = function() use ( $attributes ) {
    ?>
    <ul class="formgent-rating-wrapper" role="radiogroup" aria-label="<?php echo esc_attr( $attributes['label'] ?: 'Rating' ); ?>">
        <template data-wp-each="state.getRatingOptions">
            <li class="formgent-rating-single">
                <span 
                    style="cursor: pointer;" 
                    data-wp-bind--tabindex="state.getEmptyStarTabIndex"
                    role="radio"
                    data-wp-bind--aria-checked="!context.item.normal"
                    data-wp-bind--aria-label="context.item.rating"
                    data-wp-bind--hidden="context.item.normal" 
                    data-wp-on--click="actions.updateRating"
                    data-wp-on--keydown="actions.handleRatingKeydown"
                    class="formgent-rating-empty">
                    <?php formgent_render_icon( $attributes['rating_icon'] )?>
                </span>
                <span 
                    style="cursor: pointer;" 
                    data-wp-bind--tabindex="state.getSolidStarTabIndex"
                    role="radio"
                    data-wp-bind--aria-checked="!context.item.solid"
                    data-wp-bind--aria-label="context.item.rating"
                    data-wp-bind--hidden="context.item.solid" 
                    data-wp-on--click="actions.updateRating"
                    data-wp-on--keydown="actions.handleRatingKeydown"
                    class="formgent-rating-active">
                    <?php formgent_render_icon( $attributes['rating_icon'] . '-solid' )?>
                </span>
            </li>
        </template>
    </ul>
    <?php
};

// Check if we're in the editor context
$is_editor = ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->editor ?? null, 'is_edit_mode' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->preview ?? null, 'is_preview_mode' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) );

?>

<!-- Frontend rendering with Interactivity API -->
<div
    data-wp-interactive="formgent/form"
    data-wp-context='<?php echo wp_json_encode( $context ); ?>'
    data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>"
>
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                class="formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
                <?php if ( ! empty( $attributes['required'] ) ) : ?>
                    <span class="formgent-field-required">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div class="formgent-field-single__wrapper" data-wp-init="callbacks.initRating">
            <div class="formgent-field-rating">
                <?php
                if ( $is_editor ) {
                    $is_external_editor_star();
                } else {
                    $is_frontend_star();
                }
                ?>
                <div class="formgent-rating-counter" style="<?php echo $is_editor ? 'display: inline-block; margin-left: 10px;' : ''; ?>">
                    <span data-wp-text="context.ratingState.rating"></span> /
                    <?php echo esc_html( $attributes['rating_limit'] ); ?>
                </div>
            </div>
            <input
                type="hidden"
                class="formgent-validate-field-input"
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                name="<?php echo esc_attr( $attributes['name'] ); ?>"
                value=""
            />
            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>