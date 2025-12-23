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

/**
 * Add barefoot shoes description to all products
 * Displays after short description and divider
 */
function sciuuuskids_barefoot_description() {
    ?>
    <div class="barefoot-description">
        Scarpe barefoot in pelle certificata LWG. Perfette per lo sviluppo naturale del piede del bambino.
    </div>
    <?php
}
add_action( 'woocommerce_single_product_summary', 'sciuuuskids_barefoot_description', 25 );

/**
 * Display stock urgency message based on available quantity
 * Shows smart messaging with different urgency levels
 * For variable products, updates dynamically when variation is selected
 */
function sciuuuskids_stock_urgency() {
    global $product;

    // Make sure we have a product
    if ( ! $product ) {
        return;
    }

    // For variable products, output placeholder that JS will update
    if ( $product->is_type( 'variable' ) ) {
        ?>
        <div class="stock-urgency-box" style="display: none;" data-stock-urgency></div>
        <?php
        return;
    }

    // For simple products, show stock urgency directly
    $stock_qty = null;

    if ( $product->managing_stock() ) {
        $stock_qty = $product->get_stock_quantity();
    } elseif ( $product->is_in_stock() ) {
        // If not managing stock but in stock, treat as medium stock (no urgency message)
        return;
    } else {
        // Not managing stock and not in stock = out of stock
        $stock_qty = 0;
    }

    // Don't show anything if more than 10 items
    if ( $stock_qty > 10 ) {
        return;
    }

    $urgency_data = sciuuuskids_get_stock_urgency_data( $stock_qty );

    // Output the urgency box
    if ( $urgency_data['message'] ) {
        ?>
        <div class="stock-urgency-box <?php echo esc_attr( $urgency_data['class'] ); ?>">
            <?php echo esc_html( $urgency_data['message'] ); ?>
        </div>
        <?php
    }
}
add_action( 'woocommerce_single_product_summary', 'sciuuuskids_stock_urgency', 28 );

/**
 * Get stock urgency data (message and class) based on quantity
 *
 * @param int $stock_qty Stock quantity
 * @return array Array with 'message' and 'class' keys
 */
function sciuuuskids_get_stock_urgency_data( $stock_qty ) {
    $message = '';
    $class = '';

    // Determine message and class based on stock quantity
    if ( $stock_qty === 0 ) {
        $message = '❌ Temporaneamente esaurito';
        $class = 'critical';
    } elseif ( $stock_qty === 1 ) {
        $message = '⚠️ Solo 1 disponibile!';
        $class = 'critical';
    } elseif ( $stock_qty >= 2 && $stock_qty <= 3 ) {
        $message = '⚠️ Solo ' . $stock_qty . ' disponibili!';
        $class = 'critical';
    } elseif ( $stock_qty >= 4 && $stock_qty <= 5 ) {
        $message = '📦 Disponibilità limitata (' . $stock_qty . ' rimasti)';
        $class = 'low';
    } elseif ( $stock_qty >= 6 && $stock_qty <= 10 ) {
        $message = '✓ Disponibile - ' . $stock_qty . ' in stock';
        $class = 'medium';
    }

    return array(
        'message' => $message,
        'class' => $class
    );
}
