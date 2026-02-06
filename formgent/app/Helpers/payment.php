<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Contracts\PaymentInterface;
use FormGent\App\Repositories\OrderItemRepository;
use FormGent\App\Repositories\OrderRepository;
use FormGent\App\Repositories\PaymentRepository;

function formgent_get_payment_gateways(): array {
    return formgent_config( 'payment-gateways' );
}

function formgent_order_repository(): OrderRepository {
    return formgent_singleton( OrderRepository::class );
}

function formgent_order_item_repository(): OrderItemRepository {
    return formgent_singleton( OrderItemRepository::class );
}

function formgent_payment_repository(): PaymentRepository {
    return formgent_singleton( PaymentRepository::class );
}

function formgent_payment_processor( $payment_gateway ): PaymentInterface {
    $payment_gateways = formgent_get_payment_gateways();

    if ( ! isset( $payment_gateways[ $payment_gateway ] ) ) {
        throw new Exception( esc_html__( "Payment gateway not found.", 'formgent' ) );
    }

    return formgent_singleton( $payment_gateways[ $payment_gateway ]['processor'] );
}

function formgent_is_payment_form( array $fields_data ) {
    $payment_gateways    = formgent_get_payment_gateways();
    $payment_field_types = array_keys( $payment_gateways );

    // Add other payment-related field types
    $payment_field_types = array_merge( $payment_field_types, ['custom-payment-amount', 'payment-item', 'quantity'] );

    foreach ( $fields_data as $field_data ) {
        if ( in_array( $field_data['field_type'], $payment_field_types ) ) {
            return true;
        }
    }
    return false;
}

function formgent_is_payment_enabled() {
    $settings = formgent_get_setting( "payment" );

    if ( ! $settings['status'] ) {
        return false;
    }

    return true;
}

function formgent_get_currencies() {
    static $currencies;

    if ( ! isset( $currencies ) ) {
        $currencies = array_unique(
            apply_filters(
                'formgent_currencies',
                [
                    'AED' => __( 'United Arab Emirates dirham', 'formgent' ),
                    'AFN' => __( 'Afghan afghani', 'formgent' ),
                    'ALL' => __( 'Albanian lek', 'formgent' ),
                    'AMD' => __( 'Armenian dram', 'formgent' ),
                    'ANG' => __( 'Netherlands Antillean guilder', 'formgent' ),
                    'AOA' => __( 'Angolan kwanza', 'formgent' ),
                    'ARS' => __( 'Argentine peso', 'formgent' ),
                    'AUD' => __( 'Australian dollar', 'formgent' ),
                    'AWG' => __( 'Aruban florin', 'formgent' ),
                    'AZN' => __( 'Azerbaijani manat', 'formgent' ),
                    'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'formgent' ),
                    'BBD' => __( 'Barbadian dollar', 'formgent' ),
                    'BDT' => __( 'Bangladeshi taka', 'formgent' ),
                    'BGN' => __( 'Bulgarian lev', 'formgent' ),
                    'BHD' => __( 'Bahraini dinar', 'formgent' ),
                    'BIF' => __( 'Burundian franc', 'formgent' ),
                    'BMD' => __( 'Bermudian dollar', 'formgent' ),
                    'BND' => __( 'Brunei dollar', 'formgent' ),
                    'BOB' => __( 'Bolivian boliviano', 'formgent' ),
                    'BRL' => __( 'Brazilian real', 'formgent' ),
                    'BSD' => __( 'Bahamian dollar', 'formgent' ),
                    'BTC' => __( 'Bitcoin', 'formgent' ),
                    'BTN' => __( 'Bhutanese ngultrum', 'formgent' ),
                    'BWP' => __( 'Botswana pula', 'formgent' ),
                    'BYR' => __( 'Belarusian ruble (old)', 'formgent' ),
                    'BYN' => __( 'Belarusian ruble', 'formgent' ),
                    'BZD' => __( 'Belize dollar', 'formgent' ),
                    'CAD' => __( 'Canadian dollar', 'formgent' ),
                    'CDF' => __( 'Congolese franc', 'formgent' ),
                    'CHF' => __( 'Swiss franc', 'formgent' ),
                    'CLP' => __( 'Chilean peso', 'formgent' ),
                    'CNY' => __( 'Chinese yuan', 'formgent' ),
                    'COP' => __( 'Colombian peso', 'formgent' ),
                    'CRC' => __( 'Costa Rican col&oacute;n', 'formgent' ),
                    'CUC' => __( 'Cuban convertible peso', 'formgent' ),
                    'CUP' => __( 'Cuban peso', 'formgent' ),
                    'CVE' => __( 'Cape Verdean escudo', 'formgent' ),
                    'CZK' => __( 'Czech koruna', 'formgent' ),
                    'DJF' => __( 'Djiboutian franc', 'formgent' ),
                    'DKK' => __( 'Danish krone', 'formgent' ),
                    'DOP' => __( 'Dominican peso', 'formgent' ),
                    'DZD' => __( 'Algerian dinar', 'formgent' ),
                    'EGP' => __( 'Egyptian pound', 'formgent' ),
                    'ERN' => __( 'Eritrean nakfa', 'formgent' ),
                    'ETB' => __( 'Ethiopian birr', 'formgent' ),
                    'EUR' => __( 'Euro', 'formgent' ),
                    'FJD' => __( 'Fijian dollar', 'formgent' ),
                    'FKP' => __( 'Falkland Islands pound', 'formgent' ),
                    'GBP' => __( 'Pound sterling', 'formgent' ),
                    'GEL' => __( 'Georgian lari', 'formgent' ),
                    'GGP' => __( 'Guernsey pound', 'formgent' ),
                    'GHS' => __( 'Ghana cedi', 'formgent' ),
                    'GIP' => __( 'Gibraltar pound', 'formgent' ),
                    'GMD' => __( 'Gambian dalasi', 'formgent' ),
                    'GNF' => __( 'Guinean franc', 'formgent' ),
                    'GTQ' => __( 'Guatemalan quetzal', 'formgent' ),
                    'GYD' => __( 'Guyanese dollar', 'formgent' ),
                    'HKD' => __( 'Hong Kong dollar', 'formgent' ),
                    'HNL' => __( 'Honduran lempira', 'formgent' ),
                    'HRK' => __( 'Croatian kuna', 'formgent' ),
                    'HTG' => __( 'Haitian gourde', 'formgent' ),
                    'HUF' => __( 'Hungarian forint', 'formgent' ),
                    'IDR' => __( 'Indonesian rupiah', 'formgent' ),
                    'ILS' => __( 'Israeli new shekel', 'formgent' ),
                    'IMP' => __( 'Manx pound', 'formgent' ),
                    'INR' => __( 'Indian rupee', 'formgent' ),
                    'IQD' => __( 'Iraqi dinar', 'formgent' ),
                    'IRR' => __( 'Iranian rial', 'formgent' ),
                    'IRT' => __( 'Iranian toman', 'formgent' ),
                    'ISK' => __( 'Icelandic kr&oacute;na', 'formgent' ),
                    'JEP' => __( 'Jersey pound', 'formgent' ),
                    'JMD' => __( 'Jamaican dollar', 'formgent' ),
                    'JOD' => __( 'Jordanian dinar', 'formgent' ),
                    'JPY' => __( 'Japanese yen', 'formgent' ),
                    'KES' => __( 'Kenyan shilling', 'formgent' ),
                    'KGS' => __( 'Kyrgyzstani som', 'formgent' ),
                    'KHR' => __( 'Cambodian riel', 'formgent' ),
                    'KMF' => __( 'Comorian franc', 'formgent' ),
                    'KPW' => __( 'North Korean won', 'formgent' ),
                    'KRW' => __( 'South Korean won', 'formgent' ),
                    'KWD' => __( 'Kuwaiti dinar', 'formgent' ),
                    'KYD' => __( 'Cayman Islands dollar', 'formgent' ),
                    'KZT' => __( 'Kazakhstani tenge', 'formgent' ),
                    'LAK' => __( 'Lao kip', 'formgent' ),
                    'LBP' => __( 'Lebanese pound', 'formgent' ),
                    'LKR' => __( 'Sri Lankan rupee', 'formgent' ),
                    'LRD' => __( 'Liberian dollar', 'formgent' ),
                    'LSL' => __( 'Lesotho loti', 'formgent' ),
                    'LYD' => __( 'Libyan dinar', 'formgent' ),
                    'MAD' => __( 'Moroccan dirham', 'formgent' ),
                    'MDL' => __( 'Moldovan leu', 'formgent' ),
                    'MGA' => __( 'Malagasy ariary', 'formgent' ),
                    'MKD' => __( 'Macedonian denar', 'formgent' ),
                    'MMK' => __( 'Burmese kyat', 'formgent' ),
                    'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'formgent' ),
                    'MOP' => __( 'Macanese pataca', 'formgent' ),
                    'MRU' => __( 'Mauritanian ouguiya', 'formgent' ),
                    'MUR' => __( 'Mauritian rupee', 'formgent' ),
                    'MVR' => __( 'Maldivian rufiyaa', 'formgent' ),
                    'MWK' => __( 'Malawian kwacha', 'formgent' ),
                    'MXN' => __( 'Mexican peso', 'formgent' ),
                    'MYR' => __( 'Malaysian ringgit', 'formgent' ),
                    'MZN' => __( 'Mozambican metical', 'formgent' ),
                    'NAD' => __( 'Namibian dollar', 'formgent' ),
                    'NGN' => __( 'Nigerian naira', 'formgent' ),
                    'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'formgent' ),
                    'NOK' => __( 'Norwegian krone', 'formgent' ),
                    'NPR' => __( 'Nepalese rupee', 'formgent' ),
                    'NZD' => __( 'New Zealand dollar', 'formgent' ),
                    'OMR' => __( 'Omani rial', 'formgent' ),
                    'PAB' => __( 'Panamanian balboa', 'formgent' ),
                    'PEN' => __( 'Sol', 'formgent' ),
                    'PGK' => __( 'Papua New Guinean kina', 'formgent' ),
                    'PHP' => __( 'Philippine peso', 'formgent' ),
                    'PKR' => __( 'Pakistani rupee', 'formgent' ),
                    'PLN' => __( 'Polish z&#x142;oty', 'formgent' ),
                    'PRB' => __( 'Transnistrian ruble', 'formgent' ),
                    'PYG' => __( 'Paraguayan guaran&iacute;', 'formgent' ),
                    'QAR' => __( 'Qatari riyal', 'formgent' ),
                    'RON' => __( 'Romanian leu', 'formgent' ),
                    'RSD' => __( 'Serbian dinar', 'formgent' ),
                    'RUB' => __( 'Russian ruble', 'formgent' ),
                    'RWF' => __( 'Rwandan franc', 'formgent' ),
                    'SAR' => __( 'Saudi riyal', 'formgent' ),
                    'SBD' => __( 'Solomon Islands dollar', 'formgent' ),
                    'SCR' => __( 'Seychellois rupee', 'formgent' ),
                    'SDG' => __( 'Sudanese pound', 'formgent' ),
                    'SEK' => __( 'Swedish krona', 'formgent' ),
                    'SGD' => __( 'Singapore dollar', 'formgent' ),
                    'SHP' => __( 'Saint Helena pound', 'formgent' ),
                    'SLL' => __( 'Sierra Leonean leone', 'formgent' ),
                    'SOS' => __( 'Somali shilling', 'formgent' ),
                    'SRD' => __( 'Surinamese dollar', 'formgent' ),
                    'SSP' => __( 'South Sudanese pound', 'formgent' ),
                    'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'formgent' ),
                    'SYP' => __( 'Syrian pound', 'formgent' ),
                    'SZL' => __( 'Swazi lilangeni', 'formgent' ),
                    'THB' => __( 'Thai baht', 'formgent' ),
                    'TJS' => __( 'Tajikistani somoni', 'formgent' ),
                    'TMT' => __( 'Turkmenistan manat', 'formgent' ),
                    'TND' => __( 'Tunisian dinar', 'formgent' ),
                    'TOP' => __( 'Tongan pa&#x2bb;anga', 'formgent' ),
                    'TRY' => __( 'Turkish lira', 'formgent' ),
                    'TTD' => __( 'Trinidad and Tobago dollar', 'formgent' ),
                    'TWD' => __( 'New Taiwan dollar', 'formgent' ),
                    'TZS' => __( 'Tanzanian shilling', 'formgent' ),
                    'UAH' => __( 'Ukrainian hryvnia', 'formgent' ),
                    'UGX' => __( 'Ugandan shilling', 'formgent' ),
                    'USD' => __( 'United States (US) dollar', 'formgent' ),
                    'UYU' => __( 'Uruguayan peso', 'formgent' ),
                    'UZS' => __( 'Uzbekistani som', 'formgent' ),
                    'VEF' => __( 'Venezuelan bol&iacute;var (2008–2018)', 'formgent' ),
                    'VES' => __( 'Venezuelan bol&iacute;var', 'formgent' ),
                    'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'formgent' ),
                    'VUV' => __( 'Vanuatu vatu', 'formgent' ),
                    'WST' => __( 'Samoan t&#x101;l&#x101;', 'formgent' ),
                    'XAF' => __( 'Central African CFA franc', 'formgent' ),
                    'XCD' => __( 'East Caribbean dollar', 'formgent' ),
                    'XOF' => __( 'West African CFA franc', 'formgent' ),
                    'XPF' => __( 'CFP franc', 'formgent' ),
                    'YER' => __( 'Yemeni rial', 'formgent' ),
                    'ZAR' => __( 'South African rand', 'formgent' ),
                    'ZMW' => __( 'Zambian kwacha', 'formgent' ),
                ]
            )
        );
    }

    return $currencies;
}

/**
 * Get all available Currency symbols.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols)
 *
 * @since 4.1.0
 * @return array
 */
function formgent_get_currency_symbols() {
    $symbols = apply_filters(
        'formgent_currency_symbols',
        [
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => 'Afl.',
            'AZN' => '&#8380;',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BYN' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'kr.',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x20be;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x62f;.&#x639;',
            'IRR' => '&#xfdfc;',
            'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => '&#8376;',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x62f;.&#x644;',
            'MAD' => '&#x62f;.&#x645;.',
            'MDL' => 'MDL',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRU' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => 'N&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#1088;&#1089;&#1076;',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STN' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'E',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VES' => 'Bs.',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'CFA',
            'XCD' => '&#36;',
            'XOF' => 'CFA',
            'XPF' => 'XPF',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        ]
    );

    return $symbols;
}

function formgent_get_currency_symbol() {
    $currency_symbol  = formgent_settings_repository()->get_by_key( 'payment', [] )['currency'] ?? 'USD';
    $currency_symbols = formgent_get_currency_symbols();
    return $currency_symbols[$currency_symbol] ?? '$';
}

function formgent_get_currency_position() {
    return formgent_get_nested_value( 'symbol_position', formgent_get_setting( "payment" ), "left_space" );
}

function formgent_get_price_format() {
    $currency_pos = formgent_get_currency_position();
    $format       = '%1$s%2$s';

    switch ( $currency_pos ) {
        case 'left':
            $format = '%1$s%2$s';
            break;
        case 'right':
            $format = '%2$s%1$s';
            break;
        case 'left_space':
            $format = '%1$s&nbsp;%2$s';
            break;
        case 'right_space':
            $format = '%2$s&nbsp;%1$s';
            break;
    }

    return apply_filters( 'formgent_price_format', $format, $currency_pos );
}

function formgent_price( $price, array $args = [] ) {
    $args = wp_parse_args(
        $args, [
            'currency' => 'USD'
        ]
    );

    $currency_symbols = formgent_get_currency_symbols();
    $currency_symbol  = $currency_symbols[$args['currency']] ?? '$';
    $price_format     = formgent_get_price_format();

    return sprintf( $price_format, $currency_symbol, $price );
};