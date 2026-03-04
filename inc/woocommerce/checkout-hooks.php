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

/**
 * Cloudflare Turnstile on the WooCommerce Blocks checkout.
 *
 * Keys are shared with the Sciuuus Reviews plugin — configure them at:
 *   WP Admin → Sciuuus Reviews → Settings
 *   (options: sciuuus_reviews_turnstile_site_key / sciuuus_reviews_turnstile_secret_key)
 *
 * Token delivery: the JS stores the solved token in window.sciuuusTurnstileToken
 * and a window.fetch interceptor injects it as the X-Turnstile-Token header on
 * every Store API checkout request. A cookie is also set as a belt-and-suspenders
 * fallback. The server reads the header first; if missing it falls back to the
 * cookie. This avoids any race between cookie-writing and the fetch call.
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

	// All of the following runs BEFORE the Turnstile script tag in the HTML
	// (position = 'before'), so the globals and listeners are in place from
	// the very first moment the page is interactive.
	wp_add_inline_script( 'cloudflare-turnstile', '
		/* ── Globals ── */
		var sciuuusTurnstileTokenReady = false;
		window.sciuuusTurnstileToken   = null;

		/* ── 1. Block "Place Order" until Turnstile has solved ──
		   Capture phase so it fires before React\'s own handlers. */
		document.addEventListener( "click", function ( e ) {
			var btn = e.target.closest( ".wp-block-woocommerce-checkout-actions-block button" );
			if ( btn && btn.type === "submit" && !sciuuusTurnstileTokenReady ) {
				e.preventDefault();
				e.stopImmediatePropagation();
				var wrap = document.getElementById( "sciuuus-turnstile-wrap" );
				if ( wrap ) {
					wrap.scrollIntoView( { behavior: "smooth", block: "center" } );
					wrap.style.outline      = "2px solid #c00";
					wrap.style.borderRadius = "4px";
					setTimeout( function () {
						wrap.style.outline      = "";
						wrap.style.borderRadius = "";
					}, 2500 );
				}
			}
		}, true );

		/* ── 2. Inject the token into the Store API checkout request ──
		   Wrapping window.fetch means the token is delivered in-memory,
		   with no dependency on cookie timing or credentials mode. */
		( function () {
			var _fetch = window.fetch;
			window.fetch = function ( url, init ) {
				var urlStr = typeof url === "string" ? url : ( url && url.url ? url.url : "" );
				if ( urlStr.indexOf( "wc/store/v1/checkout" ) !== -1 && window.sciuuusTurnstileToken ) {
					init = Object.assign( {}, init || {} );
					if ( init.headers && typeof init.headers.set === "function" ) {
						init.headers = new Headers( init.headers );
						init.headers.set( "X-Turnstile-Token", window.sciuuusTurnstileToken );
					} else {
						init.headers = Object.assign( {}, init.headers || {} );
						init.headers[ "X-Turnstile-Token" ] = window.sciuuusTurnstileToken;
					}
				}
				return _fetch.apply( this, arguments );
			};
		} )();

		/* ── 3. Widget rendering — called by Turnstile after it loads ── */
		function sciuuusTurnstileOnLoad() {
			var siteKey        = ' . wp_json_encode( $site_key ) . ';
			var renderScheduled = false;

			function clearToken() {
				document.cookie              = "cf_turnstile_token=; path=/; SameSite=Strict; max-age=0";
				window.sciuuusTurnstileToken = null;
				sciuuusTurnstileTokenReady   = false;
			}

			function renderWidget() {
				if ( document.getElementById( "sciuuus-turnstile-wrap" ) ) return;
				var target = document.querySelector( ".wp-block-woocommerce-checkout-actions-block" );
				if ( !target ) return;

				var wrap = document.createElement( "div" );
				wrap.id = "sciuuus-turnstile-wrap";
				wrap.style.marginBottom = "1rem";
				target.insertBefore( wrap, target.firstChild );

				turnstile.render( "#sciuuus-turnstile-wrap", {
					sitekey: siteKey,
					callback: function ( token ) {
						/* Store in memory (used by the fetch interceptor) and as a
						   cookie fallback for environments that don\'t reach our fetch wrapper. */
						window.sciuuusTurnstileToken = token;
						document.cookie = "cf_turnstile_token=" + encodeURIComponent( token ) + "; path=/; SameSite=Strict";
						sciuuusTurnstileTokenReady = true;
					},
					"expired-callback": clearToken,
					"error-callback":   clearToken
				} );
			}

			function scheduleRender() {
				if ( renderScheduled ) return;
				renderScheduled = true;
				setTimeout( function () { renderScheduled = false; renderWidget(); }, 200 );
			}

			/* Re-render when React removes the widget or re-adds the actions block. */
			new MutationObserver( function ( mutations ) {
				for ( var i = 0; i < mutations.length; i++ ) {
					var m = mutations[ i ];
					for ( var r = 0; r < m.removedNodes.length; r++ ) {
						var rn = m.removedNodes[ r ];
						if ( rn.id === "sciuuus-turnstile-wrap" ||
						     ( rn.querySelector && rn.querySelector( "#sciuuus-turnstile-wrap" ) ) ) {
							clearToken();
							scheduleRender();
							return;
						}
					}
					for ( var a = 0; a < m.addedNodes.length; a++ ) {
						var an = m.addedNodes[ a ];
						if ( ( an.classList && an.classList.contains( "wp-block-woocommerce-checkout-actions-block" ) ) ||
						     ( an.querySelector && an.querySelector( ".wp-block-woocommerce-checkout-actions-block" ) ) ) {
							scheduleRender();
							return;
						}
					}
				}
			} ).observe( document.body, { childList: true, subtree: true } );

			scheduleRender();
		}
	', 'before' );
}
add_action( 'wp_enqueue_scripts', 'sciuuuskids_checkout_enqueue_turnstile' );

/**
 * Server-side Turnstile validation for the WooCommerce Blocks Store API.
 * Fires before the order is created — an invalid/missing token blocks the purchase.
 *
 * Token lookup order:
 *   1. X-Turnstile-Token request header  (injected by the fetch interceptor, most reliable)
 *   2. cf_turnstile_token cookie          (set by the JS callback, legacy fallback)
 */
add_action( 'woocommerce_store_api_checkout_update_order_from_request', function ( $order, $request ) {
	// 1. Header (injected by window.fetch wrapper — no cookie timing dependency).
	$token = isset( $_SERVER['HTTP_X_TURNSTILE_TOKEN'] )
		? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_TURNSTILE_TOKEN'] ) )
		: '';

	// 2. Cookie fallback.
	if ( empty( $token ) ) {
		$token = isset( $_COOKIE['cf_turnstile_token'] )
			? sanitize_text_field( urldecode( wp_unslash( $_COOKIE['cf_turnstile_token'] ) ) )
			: '';
	}

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

	// Expire the used token cookie immediately (non-HttpOnly so JS can reset it).
	setcookie( 'cf_turnstile_token', '', time() - 3600, '/', '', is_ssl(), false );
}, 10, 2 );
