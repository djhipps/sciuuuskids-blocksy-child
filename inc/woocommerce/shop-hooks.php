<?php
/**
 * WooCommerce Shop/Archive Customizations
 * Category and product listing page hooks
 * 
 * @package Blocksy_Child_SciuuusKids
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Remove default WooCommerce breadcrumbs
 * Uncomment if you want to remove them
 */
// remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/**
 * Customize products per page
 */
function sciuuuskids_products_per_page() {
    return 12; // Show 12 products per page (3x4 grid)
}
add_filter( 'loop_shop_per_page', 'sciuuuskids_products_per_page', 20 );

/**
 * Add category label to product cards
 * This displays the "Scarpe bambini" label on each product
 */
function sciuuuskids_show_product_categories() {
    global $product;
    
    $categories = get_the_terms( $product->get_id(), 'product_cat' );
    
    if ( $categories && ! is_wp_error( $categories ) ) {
        $category = array_shift( $categories );
        echo '<span class="product-category">' . esc_html( $category->name ) . '</span>';
    }
}
add_action( 'woocommerce_before_shop_loop_item_title', 'sciuuuskids_show_product_categories', 5 );

/**
 * Show color-family chips on archive product cards.
 * Helps users understand why a product appears in filtered results,
 * even when the primary image shows only one color variant.
 */
function sciuuuskids_show_product_color_family_chips() {
    global $product;

    // Render only on shop archives, never in single-product related/upsell areas.
    if ( is_product() || ! ( is_shop() || is_product_category() || is_product_tag() ) ) {
        return;
    }

    if ( ! $product || ! taxonomy_exists( 'pa_color-family' ) ) {
        return;
    }

    $terms = get_the_terms( $product->get_id(), 'pa_color-family' );
    if ( ! $terms || is_wp_error( $terms ) ) {
        return;
    }

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $selected  = function_exists( 'sciuuus_get_selected_color_family_slugs' )
        ? sciuuus_get_selected_color_family_slugs()
        : [];

    echo '<div class="sciuuus-color-family-chips" aria-label="' . esc_attr__( 'Famiglie colore prodotto', 'blocksy-child' ) . '">';

    foreach ( $terms as $term ) {
        $attachment_id = (int) get_term_meta( $term->term_id, 'product_attribute_image', true );
        $swatch_url    = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) : '';
        if ( ! $swatch_url ) {
            $bundled = $theme_dir . '/assets/swatches/' . $term->slug . '.png';
            if ( file_exists( $bundled ) ) {
                $swatch_url = $theme_uri . '/assets/swatches/' . $term->slug . '.png';
            }
        }

        $chip_class = 'sciuuus-color-family-chip';
        if ( in_array( $term->slug, $selected, true ) ) {
            $chip_class .= ' is-selected';
        }

        echo '<span class="' . esc_attr( $chip_class ) . '" title="' . esc_attr( $term->name ) . '">';
        if ( $swatch_url ) {
            echo '<img class="sciuuus-color-family-chip__swatch" src="' . esc_url( $swatch_url ) . '" alt="' . esc_attr( $term->name ) . '" width="14" height="14" loading="lazy" decoding="async" />';
        } else {
            echo '<span class="sciuuus-color-family-chip__swatch sciuuus-color-family-chip__swatch--fallback" aria-hidden="true"></span>';
        }
        echo '<span class="sciuuus-color-family-chip__label">' . esc_html( $term->name ) . '</span>';
        echo '</span>';
    }

    echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item_title', 'sciuuuskids_show_product_color_family_chips', 12 );

/**
 * Customize archive page title
 * Example: Change "Scarpe bambini" display format
 */
function sciuuuskids_custom_archive_title( $title ) {
    if ( is_product_category() ) {
        $title = single_term_title( '', false );
    }
    return $title;
}
add_filter( 'woocommerce_page_title', 'sciuuuskids_custom_archive_title' );

/**
 * Add custom content after shop loop
 * Example: Trust badges, info sections, etc.
 */
function sciuuuskids_after_shop_loop() {
    if ( is_product_category() ) {
        // Add custom content here if needed
        // Example: Featured benefits section
    }
}
add_action( 'woocommerce_after_shop_loop', 'sciuuuskids_after_shop_loop', 20 );

/**
 * Customize sale badge text
 */
function sciuuuskids_sale_badge( $html, $post, $product ) {
    return '<span class="onsale">-' . round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 ) . '%</span>';
}
// Uncomment to enable percentage-based sale badges
// add_filter( 'woocommerce_sale_flash', 'sciuuuskids_sale_badge', 10, 3 );

/**
 * Change number of related products
 */
function sciuuuskids_related_products_args( $args ) {
    $args['posts_per_page'] = 3; // Show 3 related products
    $args['columns'] = 3;
    return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'sciuuuskids_related_products_args' );
