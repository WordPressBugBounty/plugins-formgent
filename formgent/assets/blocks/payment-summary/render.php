<?php defined( 'ABSPATH' ) || exit;

if ( ! formgent_is_payment_enabled() ) {
    return;
}

?>

<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?> formgent-field-payment-summary">
    <div class="formgent-field-single">
        <div class="formgent-field-single__wrapper formgent-field-payment-summary-wrap">
            <span class="formgent-payment-summary-info" data-wp-bind--hidden="state.enableSummary">
                <?php echo esc_attr( $attributes['info_text'] ) ?>
            </span>
            <div class="formgent-payment-summary-table" data-wp-bind--hidden="!state.enableSummary">
                <table>
                    <thead>
                        <tr>
                            <th colspan="2" class="formgent-payment-table-th">
                                <div class="formgent-grid-combined">
                                    <div class="formgent-grid-row">
                                        <span><?php echo esc_html__( 'Item', 'formgent' ) ?></span>
                                        <span><?php echo esc_html__( 'Price', 'formgent' ) ?></span>
                                    </div>
                                </div>
                            </th>
                            <th><?php echo esc_html__( 'Quantity', 'formgent' ) ?></th>
                            <th class="formgent-payment-th-total"><?php echo esc_html__( 'Total', 'formgent' ) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template data-wp-each="state.paymentItems">
                            <tr>
                                <td colspan="2">
                                    <span data-wp-text="context.item.field_label"></span>
                                    <ul class="formgent-grid-combined">
                                        <template data-wp-each--option="context.item.options">
                                            <li class="formgent-grid-row">
                                                <div class="formgent-payment-item--label">
                                                    <span data-wp-text="context.option.name"></span>
                                                </div>
                                                <div class="formgent-payment-item--price formgent-payment-currency-position formgent-payment-currency-position-<?php echo esc_attr( formgent_get_currency_position() ) ?>">
                                                    <span><?php echo esc_html( formgent_get_currency_symbol() ); ?></span>
                                                    <span data-wp-text="context.option.price"></span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </td>
                                <td data-wp-text="context.item.quantity"></td>
                                <td>
                                    <div class="formgent-payment-currency-position formgent-payment-currency-position-<?php echo esc_attr( formgent_get_currency_position() ) ?>">
                                        <span><?php echo esc_html( formgent_get_currency_symbol() ); ?></span>
                                        <span data-wp-text="context.item.line_total"></span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr>
                            <td colspan="3" class="formgent-payment-summary-total">
                                <span class="formgent-payment-summary-total-label"><?php echo esc_html__( 'Total:', 'formgent' ) ?></span>
                            </td>
                            <td class="formgent-payment-summary-total-amount-wrapper">
                                <div class="formgent-payment-item-price formgent-payment-currency-position-<?php echo esc_attr( formgent_get_currency_position() ) ?>">
                                    <span><?php echo esc_html( formgent_get_currency_symbol() ); ?></span>
                                    <span
                                        class="formgent-payment-summary-total-amount"
                                        data-wp-text="context.global.payment.total_amount"
                                    >0</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>