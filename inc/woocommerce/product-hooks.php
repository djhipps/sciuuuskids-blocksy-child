<?php
/**
 * WooCommerce Single Product Customizations
 * All single product page hooks in one place
 * 
 * @package Blocksy_Child_SciuuusKids
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Example: Add size guide after product description
 * Uncomment and customize as needed
 */
/*
function sciuuuskids_size_guide() {
    ?>
    <div class="size-guide-link">
        <a href="#size-guide">📏 Guida alle taglie</a>
    </div>
    <?php
}
add_action( 'woocommerce_single_product_summary', 'sciuuuskids_size_guide', 21 );
*/

/**
 * Example: Add trust badges after add to cart
 * Uncomment and customize as needed
 */
/*
function sciuuuskids_trust_badges() {
    ?>
    <div class="trust-badges">
        <div class="badge">✓ Spedizione gratuita oltre 50€</div>
        <div class="badge">↩️ Reso gratuito entro 30 giorni</div>
        <div class="badge">✓ Garanzia 2 anni</div>
    </div>
    <?php
}
add_action( 'woocommerce_single_product_summary', 'sciuuuskids_trust_badges', 31 );
*/

/**
 * Example: Add custom product tab
 * Uncomment and customize as needed
 */
/*
function sciuuuskids_custom_tabs( $tabs ) {
    $tabs['barefoot'] = array(
        'title'    => 'Benefici Barefoot',
        'priority' => 25,
        'callback' => 'sciuuuskids_barefoot_tab_content'
    );
    return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'sciuuuskids_custom_tabs' );

function sciuuuskids_barefoot_tab_content() {
    echo '<h2>Perché scegliere scarpe barefoot?</h2>';
    echo '<ul>
        <li>Sviluppo naturale del piede</li>
        <li>Maggiore equilibrio e postura</li>
        <li>Rinforzo muscolare</li>
        <li>Libertà di movimento</li>
    </ul>';
}
*/

/**
 * Limit related products to 3 items
 */
function sciuuuskids_related_products_limit( $args ) {
    $args['posts_per_page'] = 3;
    $args['columns'] = 3;
    return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'sciuuuskids_related_products_limit' );
