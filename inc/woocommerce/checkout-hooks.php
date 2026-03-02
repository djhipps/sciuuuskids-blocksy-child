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

/**
 * Cloudflare Turnstile on the WooCommerce Blocks checkout.
 *
 * Keys are shared with the Sciuuus Reviews plugin — configure them at:
 *   WP Admin → Sciuuus Reviews → Settings
 *   (options: sciuuus_reviews_turnstile_site_key / sciuuus_reviews_turnstile_secret_key)
 */

/**
 * Enqueue the Turnstile script and inject the widget before the Place Order button.
 */
function sciuuuskids_checkout_enqueue_turnstile() {
    if ( ! is_checkout() ) {
        return;
    }

    // ⚠ Keys shared with the Sciuuus Reviews plugin settings page.
    $site_key = get_option( 'sciuuus_reviews_turnstile_site_key' );
    if ( ! $site_key ) {
        return;
    }

    wp_enqueue_script(
        'cloudflare-turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=sciuuusTurnstileOnLoad&render=explicit',
        [],
        null,
        true
    );

    // Add async + defer for performance.
    add_filter( 'script_loader_tag', function ( $tag, $handle ) {
        if ( 'cloudflare-turnstile' === $handle ) {
            return str_replace( ' src=', ' async defer src=', $tag );
        }
        return $tag;
    }, 10, 2 );

    // Define the onload callback BEFORE the Turnstile script so it exists when Turnstile initialises.
    wp_add_inline_script( 'cloudflare-turnstile', '
        function sciuuusTurnstileOnLoad() {
            var siteKey = ' . wp_json_encode( $site_key ) . ';
            // Poll until the Blocks checkout actions container is in the DOM.
            var timer = setInterval( function () {
                var target = document.querySelector( ".wp-block-woocommerce-checkout-actions-block" );
                if ( target && !document.getElementById( "sciuuus-turnstile-wrap" ) ) {
                    clearInterval( timer );
                    var wrap = document.createElement( "div" );
                    wrap.id = "sciuuus-turnstile-wrap";
                    wrap.style.marginBottom = "1rem";
                    target.insertBefore( wrap, target.firstChild );
                    turnstile.render( "#sciuuus-turnstile-wrap", {
                        sitekey: siteKey,
                        callback: function ( token ) {
                            document.cookie = "cf_turnstile_token=" + encodeURIComponent( token ) + "; path=/; SameSite=Strict";
                        },
                        "expired-callback": function () {
                            document.cookie = "cf_turnstile_token=; path=/; SameSite=Strict; max-age=0";
                        },
                        "error-callback": function () {
                            document.cookie = "cf_turnstile_token=; path=/; SameSite=Strict; max-age=0";
                        }
                    } );
                }
            }, 300 );
        }
    ', 'before' );
}
add_action( 'wp_enqueue_scripts', 'sciuuuskids_checkout_enqueue_turnstile' );

/**
 * Server-side Turnstile validation for the WooCommerce Blocks Store API.
 * Fires before the order is created — an invalid/missing token blocks the purchase.
 */
add_action( 'woocommerce_store_api_checkout_update_order_from_request', function ( $order, $request ) {
    $token = isset( $_COOKIE['cf_turnstile_token'] )
        ? sanitize_text_field( urldecode( wp_unslash( $_COOKIE['cf_turnstile_token'] ) ) )
        : '';

    if ( empty( $token ) ) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'turnstile_missing',
            'Completa la verifica di sicurezza prima di procedere.',
            400
        );
    }

    // ⚠ Secret key shared with the Sciuuus Reviews plugin settings page.
    $secret   = get_option( 'sciuuus_reviews_turnstile_secret_key' );
    $response = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'body' => [
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
        ],
    ] );

    if ( is_wp_error( $response ) ) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'turnstile_error',
            'Errore di verifica sicurezza. Riprova.',
            400
        );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $body['success'] ) ) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'turnstile_failed',
            'Verifica di sicurezza fallita. Ricarica la pagina e riprova.',
            400
        );
    }

    // Expire the used token cookie immediately.
    setcookie( 'cf_turnstile_token', '', time() - 3600, '/', '', is_ssl(), true );
}, 10, 2 );
