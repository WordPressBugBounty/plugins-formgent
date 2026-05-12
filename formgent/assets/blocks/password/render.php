<?php defined( 'ABSPATH' ) || exit; ?>

<?php
$settings                  = get_post_meta( get_post()->ID, '_formgent_form_settings', true );
$use_label_as_placeholder  = ! empty( $settings['use_label_as_placeholder'] ?? null );
$password_input_id         = formgent_field_id_prefix( $attributes['id'] );
$confirmation_input_id     = $password_input_id . '_confirmation';
$password_wrapper_id       = $password_input_id . '-wrapper';
$confirmation_wrapper_id   = $confirmation_input_id . '-wrapper';
$strength_feedback_id      = $password_input_id . '-strength-feedback';
$strength_hint_id          = $password_input_id . '-strength-hint';
$minimum_strength          = isset( $attributes['minimum_strength'] ) ? (string) $attributes['minimum_strength'] : 'medium';
$password_strength_enabled = ! empty( $attributes['enable_password_strength'] );
$strength_hint             = '';
$render_password_toggle    = static function( string $target_id, string $label ) {
    ?>
    <button
        type="button"
        class="formgent-login-password-toggle"
        data-formgent-password-toggle="<?php echo esc_attr( $target_id ); ?>"
        aria-controls="<?php echo esc_attr( $target_id ); ?>"
        aria-label="<?php echo esc_attr( $label ); ?>"
        aria-pressed="false"
    >
        <span class="formgent-login-eye-open">
            <?php formgent_render_icon( 'eye', 'general' ) ?>
        </span>
        <span class="formgent-login-eye-closed formgent-display-none">
            <?php formgent_render_icon( 'eye-off', 'general' ) ?>
        </span>
    </button>
    <?php
};

if ( $password_strength_enabled ) {
    switch ( $minimum_strength ) {
        case 'weak':
            $strength_hint = esc_html__( 'Use at least 6 characters.', 'formgent' );
            break;
        case 'strong':
            $strength_hint = esc_html__( 'Use at least 10 characters with uppercase, lowercase, a number, and a symbol.', 'formgent' );
            break;
        case 'medium':
        default:
            $strength_hint = esc_html__( 'Use at least 8 characters with letters and a number.', 'formgent' );
            break;
    }
}
?>

<div data-wp-interactive="formgent/form" data-wp-context='<?php echo wp_json_encode( [ 'name' => $attributes['name'] ] ); ?>' data-wp-bind--hidden="state.hideField" class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?> formgent-field-password">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) && ! $use_label_as_placeholder ) : ?>
            <label
                for="<?php echo esc_attr( $password_input_id ); ?>"
                class= "formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
                <?php if ( $attributes['required'] ) : ?>
                    <span class="formgent-field-required">
                        *
                    </span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div class="formgent-field-single__wrapper">
            <div id="<?php echo esc_attr( $password_wrapper_id ); ?>" class="formgent-login-password-wrapper">
                <input
                    class="formgent-field-single__input"
                    type="password"
                    id="<?php echo esc_attr( $password_input_id ); ?>"
                    placeholder="<?php echo esc_attr( $use_label_as_placeholder ? $attributes['label'] : $attributes['placeholder'] ); ?>"
                    autocomplete="new-password"
                    data-wp-on--input="actions.updateInput"
                    data-wp-bind--value="state.getValue"
                    <?php if ( $password_strength_enabled ) : ?>
                        data-formgent-password-strength="1"
                        data-formgent-password-minimum="<?php echo esc_attr( $minimum_strength ); ?>"
                        aria-describedby="<?php echo esc_attr( $strength_feedback_id . ' ' . $strength_hint_id ); ?>"
                    <?php endif; ?>
                />
                <?php $render_password_toggle( $password_input_id, __( 'Show or hide password', 'formgent' ) ); ?>
            </div>

            <?php if ( $password_strength_enabled && '' !== $strength_hint ) : ?>
                <span
                    id="<?php echo esc_attr( $strength_hint_id ); ?>"
                    class="formgent-field-sub-label"
                >
                    <?php echo esc_html( $strength_hint ); ?>
                </span>
            <?php endif; ?>

            <?php if ( $password_strength_enabled ) : ?>
                <div
                    id="<?php echo esc_attr( $strength_feedback_id ); ?>"
                    class="formgent-field-error formgent-danger-text"
                    hidden
                ></div>
            <?php endif; ?>

            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $attributes['enable_confirmation_field'] ) :
        $confirmation_name = $attributes['name'] . '_confirmation'; ?>
        <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
            <?php if ( ! empty( $attributes['confirm_label'] ) && ! $use_label_as_placeholder ) : ?>
                <label
                    for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>_confirmation"
                    class= "formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
                >
                    <?php echo wp_kses_post( $attributes['confirm_label'] ); ?>
                    <span class="formgent-field-required">*</span>
                </label>
            <?php endif; ?>

            <div class="formgent-field-single__wrapper">
                <div id="<?php echo esc_attr( $confirmation_wrapper_id ); ?>" class="formgent-login-password-wrapper">
                    <input
                        class="formgent-field-single__input"
                        type="password"
                        id="<?php echo esc_attr( $confirmation_input_id ); ?>"
                        data-wp-context='<?php echo wp_json_encode( [ 'name' => $confirmation_name ] ); ?>'
                        placeholder="<?php echo esc_attr( $use_label_as_placeholder ? $attributes['confirm_label'] : $attributes['confirm_placeholder'] ); ?>"
                        autocomplete="new-password"
                        data-wp-on--input="actions.updateInput"
                        data-wp-bind--value="state.getValue"
                    />
                    <?php $render_password_toggle( $confirmation_input_id, __( 'Show or hide confirm password', 'formgent' ) ); ?>
                </div>

                <?php if ( ! empty( $attributes['confirm_sub_label'] ) ) : ?>
                    <span class="formgent-field-sub-label">
                        <?php echo wp_kses_post( $attributes['confirm_sub_label'] ); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    ( function() {
        const wrapper = document.getElementById( <?php echo wp_json_encode( $password_wrapper_id ); ?> );
        const confirmationWrapper = document.getElementById( <?php echo wp_json_encode( $confirmation_wrapper_id ); ?> );
        const scopes = [ wrapper, confirmationWrapper ].filter( Boolean );
        const toggles = [];

        scopes.forEach( ( element ) => {
            toggles.push( ...element.querySelectorAll( '[data-formgent-password-toggle]' ) );
        } );

        const syncPasswordToggle = ( button, input ) => {
            const isVisible = input.type === 'text';
            const openIcon = button.querySelector( '.formgent-login-eye-open' );
            const closedIcon = button.querySelector( '.formgent-login-eye-closed' );

            if ( openIcon ) {
                openIcon.classList.toggle( 'formgent-display-none', isVisible );
            }

            if ( closedIcon ) {
                closedIcon.classList.toggle( 'formgent-display-none', ! isVisible );
            }

            button.setAttribute( 'aria-pressed', isVisible ? 'true' : 'false' );
        };

        toggles.forEach( ( button ) => {
            const targetId = button.getAttribute( 'data-formgent-password-toggle' );
            const input = document.getElementById( targetId );

            if ( ! input ) {
                return;
            }

            if ( button.dataset.formgentPasswordToggleBound === '1' ) {
                return;
            }

            syncPasswordToggle( button, input );
            button.dataset.formgentPasswordToggleBound = '1';

            button.addEventListener( 'click', () => {
                input.type = input.type === 'password' ? 'text' : 'password';
                syncPasswordToggle( button, input );
            } );
        } );
    } )();
</script>

<?php if ( $password_strength_enabled ) : ?>
    <script>
        ( function() {
            const input = document.getElementById( <?php echo wp_json_encode( $password_input_id ); ?> );
            const feedback = document.getElementById( <?php echo wp_json_encode( $strength_feedback_id ); ?> );
            const minimumStrength = <?php echo wp_json_encode( $minimum_strength ); ?>;
            const message = <?php echo wp_json_encode( esc_html__( 'Password strength is too weak.', 'formgent' ) ); ?>;

            if ( ! input || ! feedback ) {
                return;
            }

            const strengthMap = {
                invalid: -1,
                weak: 0,
                medium: 1,
                strong: 2,
            };

            const getPasswordStrengthLevel = ( password ) => {
                const hasLower = /[a-z]/.test( password );
                const hasUpper = /[A-Z]/.test( password );
                const hasNumber = /[0-9]/.test( password );
                const hasSymbol = /[^a-zA-Z0-9]/.test( password );

                if (
                    password.length >= 10 &&
                    hasLower &&
                    hasUpper &&
                    hasNumber &&
                    hasSymbol
                ) {
                    return 'strong';
                }

                if (
                    password.length >= 8 &&
                    ( hasLower || hasUpper ) &&
                    hasNumber
                ) {
                    return 'medium';
                }

                if ( password.length >= 6 ) {
                    return 'weak';
                }

                return 'invalid';
            };

            const updatePasswordStrengthMessage = () => {
                const value = input.value || '';
                const level = getPasswordStrengthLevel( value );
                const isStrongEnough =
                    value === '' ||
                    ( strengthMap[ level ] ?? strengthMap.invalid ) >=
                        ( strengthMap[ minimumStrength ] ?? strengthMap.medium );

                if ( isStrongEnough ) {
                    feedback.hidden = true;
                    feedback.textContent = '';
                    input.removeAttribute( 'aria-invalid' );
                    return;
                }

                feedback.hidden = false;
                feedback.textContent = message;
                input.setAttribute( 'aria-invalid', 'true' );
            };

            input.addEventListener( 'input', updatePasswordStrengthMessage );
            input.addEventListener( 'blur', updatePasswordStrengthMessage );
            updatePasswordStrengthMessage();
        } )();
    </script>
<?php endif; ?>
