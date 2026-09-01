<?php defined( 'ABSPATH' ) || exit;

$is_editor = formgent_is_editor_context();
$info      = $attributes['info'] ?? 'both';
$summary   = [
    'question_count'   => '',
    'time_to_complete' => '',
];

if ( $is_editor ) {
    $form = get_post();

    if ( $form instanceof WP_Post && formgent_post_type() === $form->post_type ) {
        $form_helper = new \FormGent\App\Helpers\Form();
        $summary     = $form_helper->get_conversational_summary( parse_blocks( $form->post_content ) );
    }
}
?>

<div class="formgent-field-single formgent-field-single--info">
    <?php if ( in_array( $info, [ 'question_count', 'both' ], true ) ) : ?>
        <span>
            <?php formgent_render_icon( 'clipboard', 'general' ) ?>
            <span
                <?php if ( ! $is_editor ) : ?>
                    data-wp-text="state.totalStep"
                <?php endif; ?>
            ><?php echo esc_html( $summary['question_count'] ); ?></span>
        </span>
    <?php endif; ?>

    <?php if ( in_array( $info, [ 'time_to_complete', 'both' ], true ) ) : ?>
        <span>
            <?php formgent_render_icon( 'clock', 'general' ) ?>
            <span
                <?php if ( ! $is_editor ) : ?>
                    data-wp-text="state.timeToComplete"
                <?php endif; ?>
            ><?php echo esc_html( $summary['time_to_complete'] ); ?></span>
        </span>
    <?php endif; ?>
</div>
