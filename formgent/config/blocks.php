<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\DatePicker\DatePicker;
use FormGent\App\Fields\Rating\Rating;
use FormGent\App\Fields\GoogleMap\GoogleMap;
use FormGent\App\Fields\Address\Address;
use FormGent\App\Fields\Dropdown\Dropdown;
use FormGent\App\Fields\Email\Email;
use FormGent\App\Fields\FileUpload\FileUpload;
use FormGent\App\Fields\GDPR\GDPR;
use FormGent\App\Fields\PhoneNumber\PhoneNumber;
use FormGent\App\Fields\Repeater\Repeater;
use FormGent\App\Fields\Website\Website;
use FormGent\App\Fields\SingleChoice\SingleChoice;
use FormGent\App\Fields\InputMasking\InputMasking;
use FormGent\App\Fields\MultipleChoice\MultipleChoice;
use FormGent\App\Fields\Name\Name;
use FormGent\App\Fields\Number\Number;
use FormGent\App\Fields\TextArea\TextArea;
use FormGent\App\Fields\Text\Text;
use FormGent\App\Fields\RangeSlider\RangeSlider;

$blocks_dir = formgent_dir( "assets/blocks" );

return apply_filters(
    'formgent_gutenberg_blocks', [
        'formgent/form'                  => [
            'dir' => $blocks_dir,
        ],
        'formgent/step'                  => [
            'field_type' => 'step',
            'dir'        => $blocks_dir,
        ],
        'formgent/welcome'               => [
            'field_type' => 'welcome',
            'dir'        => $blocks_dir,
        ],
        'formgent/name'                  => [
            'types'      => ['general', 'conversational'],
            'field_type' => Name::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/email'                 => [
            'types'      => ['general', 'conversational'],
            'field_type' => Email::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/text'                  => [
            'types'      => ['general', 'conversational'],
            'field_type' => Text::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/textarea'              => [
            'types'      => ['general', 'conversational'],
            'field_type' => TextArea::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/number'                => [
            'types'      => ['general', 'conversational'],
            'field_type' => Number::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/next-button'           => [
            'types'      => ['general', 'conversational'],
            'field_type' => '',
            'dir'        => $blocks_dir,
        ],
        'formgent/info'                  => [
            'types'      => ['general', 'conversational'],
            'field_type' => '',
            'dir'        => $blocks_dir,
        ],
        'formgent/html'                  => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'html',
            'dir'        => $blocks_dir,
        ],
        'formgent/phone-number'          => [
            'types'      => ['general', 'conversational'],
            'field_type' => PhoneNumber::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/dropdown'              => [
            'types'      => ['general', 'conversational'],
            'field_type' => Dropdown::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/single-choice'         => [
            'types'      => ['general', 'conversational'],
            'field_type' => SingleChoice::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/multiple-choice'       => [
            'types'      => ['general', 'conversational'],
            'field_type' => MultipleChoice::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/address'               => [
            'types'      => ['general', 'conversational'],
            'field_type' => Address::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/google-map'            => [
            'types'      => ['general', 'conversational'],
            'field_type' => GoogleMap::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/gdpr'                  => [
            'types'      => ['general', 'conversational'],
            'field_type' => GDPR::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/website'               => [
            'types'      => ['general', 'conversational'],
            'field_type' => Website::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/input-masking'         => [
            'types'      => ['general', 'conversational'],
            'field_type' => InputMasking::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/submit-button'         => [
            'types'      => ['general', 'conversational'],
            'field_type' => '',
            'dir'        => $blocks_dir,
        ],
        'formgent/repeater'              => [
            'types'      => ['general', 'conversational'],
            'field_type' => Repeater::get_key(),
            'dir'        => $blocks_dir
        ],
        'formgent/file-upload'           => [
            'types'      => ['general', 'conversational'],
            'field_type' => FileUpload::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/rating'                => [
            'types'      => ['general', 'conversational'],
            'field_type' => Rating::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/range-slider'          => [
            'types'      => ['general', 'conversational'],
            'field_type' => RangeSlider::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/date-picker'           => [
            'types'      => ['general', 'conversational'],
            'field_type' => DatePicker::get_key(),
            'dir'        => $blocks_dir,
        ],
        'formgent/paypal'                => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'paypal',
            'dir'        => $blocks_dir,
        ],
        'formgent/stripe'                => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'stripe',
            'dir'        => $blocks_dir,
        ],
        'formgent/payment-item'          => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'payment-item',
            'dir'        => $blocks_dir,
        ],
        'formgent/quantity'              => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'quantity',
            'dir'        => $blocks_dir,
        ],
        'formgent/custom-payment-amount' => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'custom-payment-amount',
            'dir'        => $blocks_dir,
        ],
        'formgent/payment-summary'       => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'payment-summary',
            'dir'        => $blocks_dir,
        ],
        'formgent/captcha'               => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'captcha',
            'dir'        => $blocks_dir
        ],
        'formgent/page-break'            => [
            'types'      => ['general', 'conversational'],
            'field_type' => 'page-break',
            'dir'        => $blocks_dir
        ],
        'formgent/end'                   => [
            'field_type' => 'end',
            'dir'        => $blocks_dir,
        ],
    ]
);