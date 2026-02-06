<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\Address\Address;
use FormGent\App\Fields\DatePicker\DatePicker;
use FormGent\App\Fields\Dropdown\Dropdown;
use FormGent\App\Fields\Email\Email;
use FormGent\App\Fields\FileUpload\FileUpload;
use FormGent\App\Fields\GDPR\GDPR;
use FormGent\App\Fields\GoogleMap\GoogleMap;
use FormGent\App\Fields\InputMasking\InputMasking;
use FormGent\App\Fields\MultipleChoice\MultipleChoice;
use FormGent\App\Fields\Name\Name;
use FormGent\App\Fields\Number\Number;
use FormGent\App\Fields\RangeSlider\RangeSlider;
use FormGent\App\Fields\PhoneNumber\PhoneNumber;
use FormGent\App\Fields\Rating\Rating;
use FormGent\App\Fields\Repeater\Repeater;
use FormGent\App\Fields\SingleChoice\SingleChoice;
use FormGent\App\Fields\TextArea\TextArea;
use FormGent\App\Fields\Text\Text;
use FormGent\App\Fields\Website\Website;

return apply_filters(
    'formgent_fields', [
        Address::get_key()        => [
            'class'                     => Address::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        Text::get_key()           => [
            'class'                     => Text::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        TextArea::get_key()       => [
            'class'                     => TextArea::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        Name::get_key()           => [
            'class'                     => Name::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        Email::get_key()          => [
            'class'                     => Email::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        Number::get_key()         => [
            'class'                     => Number::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        RangeSlider::get_key()    => [
            'class'                     => RangeSlider::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        GDPR::get_key()           => [
            'class'                     => GDPR::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => false,
        ],
        PhoneNumber::get_key()    => [
            'class'                     => PhoneNumber::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        Website::get_key()        => [
            'class'                     => Website::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        SingleChoice::get_key()   => [
            'class'                     => SingleChoice::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        MultipleChoice::get_key() => [
            'class'                     => MultipleChoice::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        Dropdown::get_key()       => [
            'class'                     => Dropdown::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        InputMasking::get_key()   => [
            'class'                     => InputMasking::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        FileUpload::get_key()     => [
            'class'                     => FileUpload::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => false,
        ],
        GoogleMap::get_key()      => [
            'class'                     => GoogleMap::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
        Repeater::get_key()       => [
            'class'                     => Repeater::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => false,
        ],
        Rating::get_key()         => [
            'class'                     => Rating::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => false,
        ],
        DatePicker::get_key()     => [
            'class'                     => DatePicker::class,
            'allowed_in_response'       => true,
            'allowed_in_response_table' => true,
        ],
    ]
);