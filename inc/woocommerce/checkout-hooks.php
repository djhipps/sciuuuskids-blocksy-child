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

	if ( ! isset( $locale['IT']['phone'] ) ) {
		$locale['IT']['phone'] = array();
	}
	$locale['IT']['phone']['required'] = true;

	return $locale;
}

/**
 * Force the WooCommerce Blocks checkout to treat phone as required.
 *
 * WooCommerce Blocks is a React app bootstrapped from inline JS data, not from
 * PHP filters evaluated at render time. The React component reads the
 * `woocommerce_checkout_phone_field` option (serialised into the page by the
 * block type registration) to set the required state of the phone field.
 * Intercepting the option read here is the layer that actually reaches the
 * Blocks frontend — for both guests and logged-in users.
 *
 * The locale filter above also remains in place so that server-side field
 * validation and classic-checkout forms stay consistent.
 */
add_filter( 'pre_option_woocommerce_checkout_phone_field', function () {
	return 'required';
} );
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

