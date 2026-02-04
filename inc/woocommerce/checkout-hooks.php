<?php
/**
 * Checkout Page Hooks & Customizations
 *
 * @package Blocksy_Child_SciuuusKids
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Customize address_2 field label for Italian locale.
 *
 * "Appartamento, suite, ecc." is an unnatural literal translation from English.
 * "Interno, scala, palazzina" uses standard Italian address terminology.
 *
 * This filter feeds into the WooCommerce Blocks checkout (client-side rendered).
 */
function sciuuuskids_custom_country_locale( $locale ) {
    if ( ! isset( $locale['IT']['address_2'] ) ) {
        $locale['IT']['address_2'] = array();
    }
    $locale['IT']['address_2']['label'] = 'Interno, scala, palazzina';
    return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'sciuuuskids_custom_country_locale', 20 );

/**
 * Also override the default address fields for classic WooCommerce forms
 * (e.g. My Account address editing).
 */
function sciuuuskids_custom_address_fields( $fields ) {
    $fields['address_2']['label']       = 'Interno, scala, palazzina';
    $fields['address_2']['placeholder'] = 'Interno, scala, palazzina';
    return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'sciuuuskids_custom_address_fields', 20 );
