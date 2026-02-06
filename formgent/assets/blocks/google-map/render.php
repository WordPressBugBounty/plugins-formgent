<?php defined( 'ABSPATH' ) || exit;

$settings_repository = formgent_settings_repository();
$google_map_api_key  = $settings_repository->get_by_key( "google_map_api_key" );
?>

<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>", "map": {} }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                class= "formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
            </label>
        <?php endif; ?>
        <div class="formgent-field-single__wrapper formgent-field-single__map-wrap">
            <div class="formgent-field-single__map-input-wrap">
                <input
                    class="formgent-field-single__input"
                    type="text"
                    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                    placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>"
                    data-wp-on--input="actions.updateLocationInput"
                    data-wp-bind--value="state.getMapAddress"
                />
            </div>
            <div class="formgent-field-single-autocomplete-dropdown formgent-field-single-autocomplete-dropdown-<?php echo esc_attr( $attributes['name'] ); ?>">
                <template data-wp-each--suggestion="context.map.suggestions">
                    <div
                        class="formgent-field-single-autocomplete-dropdown__item"
                        data-wp-on--click="actions.selectMapLocation"
                        data-wp-text="context.suggestion.description"
                        data-wp-bind--data-placeid="context.suggestion.place_id"
                    ></div>
                </template>
            </div>
            <div
                class="formgent-field-single-map"
                id="formgent-map-<?php echo esc_attr( $attributes['id'] ); ?>"
                data-wp-init="callbacks.initGoogleMap"
                data-apikey="<?php echo esc_attr( $google_map_api_key ) ?>"
                style="min-height: 300px; width: 100%; border: 1px solid #ddd; border-radius: var(--formgent-field-border-radius);"
            ></div>
            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>