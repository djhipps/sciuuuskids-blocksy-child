<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue size feedback assets on single product pages.
 */
add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_product() ) {
        return;
    }

    $version = wp_get_theme()->get( 'Version' );
    $product_id = (int) get_the_ID();

    wp_enqueue_style(
        'sciuuus-size-feedback',
        get_stylesheet_directory_uri() . '/assets/css/size-feedback.css',
        array(),
        $version
    );

    wp_enqueue_script(
        'sciuuus-size-feedback',
        get_stylesheet_directory_uri() . '/assets/js/size-feedback.js',
        array(),
        $version,
        true
    );

    $size_map = class_exists( 'SciuuuSize' ) && method_exists( 'SciuuuSize', 'get_size_map' )
        ? SciuuuSize::get_size_map( $product_id )
        : array();

    $default_attribute = (string) apply_filters( 'sciuuus_size_attribute_name', 'pa_taglia', $product_id );
    $attribute_name = $default_attribute;

    $product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
    if ( $product && method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) ) {
        $variation_attributes = array_keys( (array) $product->get_variation_attributes() );
        $normalized = array();
        foreach ( $variation_attributes as $key ) {
            $key = (string) $key;
            $normalized[] = 0 === strpos( $key, 'attribute_' ) ? substr( $key, 10 ) : $key;
        }

        if ( ! in_array( $default_attribute, $normalized, true ) ) {
            if ( in_array( 'pa_taglia', $normalized, true ) ) {
                $attribute_name = 'pa_taglia';
            } elseif ( in_array( 'taglia', $normalized, true ) ) {
                $attribute_name = 'taglia';
            } elseif ( in_array( 'size', $normalized, true ) ) {
                $attribute_name = 'size';
            } else {
                foreach ( $normalized as $candidate ) {
                    if ( false !== strpos( $candidate, 'taglia' ) || false !== strpos( $candidate, 'size' ) ) {
                        $attribute_name = $candidate;
                        break;
                    }
                }
            }
        }
    }

    wp_localize_script(
        'sciuuus-size-feedback',
        'sciuuusSizeFeedback',
        array(
            'sizeMap'       => $size_map,
            'guideUrl'      => apply_filters( 'sciuuus_size_guide_url', home_url( '/guida-alle-taglie/' ) ),
            'attributeName' => $attribute_name,
        )
    );
} );

/**
 * Output feedback container close to the purchase actions.
 */
add_action( 'woocommerce_before_add_to_cart_button', function () {
    echo '<div id="sciuuus-size-feedback" aria-live="polite" aria-atomic="true"></div>';
}, 9 );
