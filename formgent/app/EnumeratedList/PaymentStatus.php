<?php

namespace FormGent\App\EnumeratedList;

defined( 'ABSPATH' ) || exit;

/**
 * Enum for Payment Statuses
 */
class PaymentStatus extends EnumBase {
    const PENDING   = 'pending';
    const PAID      = 'paid';
    const FAILED    = 'failed';
    const CANCELLED = 'cancelled';
    const REFUNDED  = 'refunded';
    const UNPAID    = 'unpaid';
    const EXPIRED   = 'expired';

    /**
     * Labels for display.
     *
     * @return array<string, string>
     */
    public static function labels(): array {
        return [
            self::PENDING   => __( 'Pending', 'formgent' ),
            self::PAID      => __( 'Paid', 'formgent' ),
            self::FAILED    => __( 'Failed', 'formgent' ),
            self::CANCELLED => __( 'Cancelled', 'formgent' ),
            self::REFUNDED  => __( 'Refunded', 'formgent' ),
            self::UNPAID    => __( 'Unpaid', 'formgent' ),
            self::EXPIRED   => __( 'Expired', 'formgent' ),
        ];
    }
}
