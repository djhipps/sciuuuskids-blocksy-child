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
 * Output product title ABOVE the gallery for mobile.
 * Hidden on desktop via CSS; on mobile it appears first so the user
 * sees the product name before scrolling through the gallery.
 * Price stays in the summary section below the gallery.
 *
 * Hooked at priority 8 on woocommerce_before_single_product_summary
 * (before sale flash at 10 and gallery images at 20).
 */
function sciuuuskids_mobile_product_header() {
    global $product;

    if ( ! $product ) {
        return;
    }
    ?>
    <div class="mobile-product-header">
        <h1 class="mobile-product-title"><?php echo esc_html( $product->get_name() ); ?></h1>
    </div>
    <?php
}
add_action( 'woocommerce_before_single_product_summary', 'sciuuuskids_mobile_product_header', 8 );

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
 * Uses product short description if available, otherwise shows default text
 */
function sciuuuskids_barefoot_description() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $short_description = $product->get_short_description();

    if ( ! empty( $short_description ) ) {
        ?>
        <div class="barefoot-description">
            <?php echo wp_kses_post( $short_description ); ?>
        </div>
        <?php
    } else {
        $product_name = $product->get_name();
        ?>
        <div class="barefoot-description">
            Le scarpe barefoot <?php echo esc_html( $product_name ); ?> sono realizzate con materiali atossici e di alta qualità per la pelle sensibile dei più piccoli. Progettate per assecondare lo sviluppo naturale del piede, offrono una suola ultra-flessibile e ampio spazio per le dita.
        </div>
        <?php
    }
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
add_action( 'woocommerce_before_add_to_cart_button', 'sciuuuskids_stock_urgency', 10 );

/**
 * Add trust badges after add to cart form
 */
function sciuuuskids_product_trust_badges() {
    ?>
    <div class="product-trust-badges">
        <div class="trust-badge-row">
            <div class="trust-badge">
                <span class="badge-icon">🚚</span>
                <span class="badge-text">Spedizione gratuita</span>
            </div>
            <div class="trust-badge">
                <span class="badge-icon">📦</span>
                <span class="badge-text">Resi 14gg</span>
            </div>
        </div>
        <div class="trust-badge-row">
            <div class="trust-badge">
                <span class="badge-icon">🔒</span>
                <span class="badge-text">Pagamenti sicuri</span>
            </div>
            <div class="trust-badge">
                <span class="badge-icon">✓</span>
                <span class="badge-text">Garanzia soddisfatti</span>
            </div>
        </div>
        <div class="payment-methods">
            <span class="payment-icon">💳</span>
            <span class="payment-text">Visa • Mastercard • PayPal • Apple Pay</span>
        </div>
    </div>
    <?php
}
add_action( 'woocommerce_after_add_to_cart_form', 'sciuuuskids_product_trust_badges', 10 );

/**
 * Add product details toggle (description + attributes + meta) after trust badges
 * Replaces the default WooCommerce tabs with a collapsible accordion
 */
function sciuuuskids_product_details_toggle() {
    global $product;

    if ( ! $product ) {
        return;
    }

    // Get product description
    $description = $product->get_description();

    // Get product attributes (visible ones)
    $attributes = $product->get_attributes();
    $visible_attributes = array();

    foreach ( $attributes as $attribute ) {
        if ( $attribute->get_visible() ) {
            $visible_attributes[] = $attribute;
        }
    }

    // Get product meta
    $sku = $product->get_sku();
    $categories = wc_get_product_category_list( $product->get_id(), ', ' );
    $tags = wc_get_product_tag_list( $product->get_id(), ', ' );

    // Get brand (pa_marchio taxonomy or custom field)
    $brand = '';
    $brand_terms = get_the_terms( $product->get_id(), 'pa_marchio' );
    if ( $brand_terms && ! is_wp_error( $brand_terms ) ) {
        $brand_names = wp_list_pluck( $brand_terms, 'name' );
        $brand = implode( ', ', $brand_names );
    }

    // Check if there's any content to display
    $has_meta = $sku || $categories || $brand;
    if ( empty( $description ) && empty( $visible_attributes ) && ! $has_meta ) {
        return;
    }
    ?>
    <div class="product-details-toggle">
        <button type="button" class="product-details-toggle-btn" aria-expanded="false" aria-controls="product-details-content">
            <span class="toggle-text">Dettagli prodotto</span>
            <span class="toggle-icon">+</span>
        </button>
        <div id="product-details-content" class="product-details-content" hidden>
            <?php if ( ! empty( $description ) ) : ?>
                <div class="product-description-section">
                    <h4>Descrizione</h4>
                    <div class="description-content">
                        <?php echo wp_kses_post( wpautop( $description ) ); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $visible_attributes ) ) : ?>
                <div class="product-attributes-section">
                    <h4>Caratteristiche</h4>
                    <table class="product-attributes-table">
                        <tbody>
                        <?php foreach ( $visible_attributes as $attribute ) :
                            $name = wc_attribute_label( $attribute->get_name() );
                            $values = array();

                            if ( $attribute->is_taxonomy() ) {
                                $terms = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
                                $values = $terms;
                            } else {
                                $values = $attribute->get_options();
                            }
                        ?>
                            <tr>
                                <th><?php echo esc_html( ucfirst( $name ) ); ?></th>
                                <td><?php echo esc_html( implode( ', ', $values ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ( $has_meta ) : ?>
                <div class="product-meta-section">
                    <h4>Informazioni</h4>
                    <table class="product-attributes-table">
                        <tbody>
                        <?php if ( $sku ) : ?>
                            <tr>
                                <th>COD</th>
                                <td><?php echo esc_html( $sku ); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ( $categories ) : ?>
                            <tr>
                                <th>Categorie</th>
                                <td><?php echo wp_kses_post( $categories ); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ( $brand ) : ?>
                            <tr>
                                <th>Marchio</th>
                                <td><?php echo esc_html( $brand ); ?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function() {
        const toggleBtn = document.querySelector('.product-details-toggle-btn');
        const content = document.getElementById('product-details-content');
        const icon = toggleBtn?.querySelector('.toggle-icon');

        if (toggleBtn && content) {
            toggleBtn.addEventListener('click', function() {
                const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                toggleBtn.setAttribute('aria-expanded', !isExpanded);
                content.hidden = isExpanded;
                if (icon) {
                    icon.textContent = isExpanded ? '+' : '−';
                }
                toggleBtn.classList.toggle('is-open', !isExpanded);
            });
        }
    })();
    </script>
    <?php
}
add_action( 'woocommerce_after_add_to_cart_form', 'sciuuuskids_product_details_toggle', 15 );

/**
 * Remove default WooCommerce product tabs
 * Content is now shown in the toggle accordion above
 */
function sciuuuskids_remove_product_tabs( $tabs ) {
    unset( $tabs['description'] );
    unset( $tabs['additional_information'] );
    return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'sciuuuskids_remove_product_tabs', 99 );

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
    // Red (critical) only for 0-1 items, yellow (low) for 2+ items
    if ( $stock_qty === 0 ) {
        $message = '❌ Temporaneamente esaurito';
        $class = 'critical';
    } elseif ( $stock_qty === 1 ) {
        $message = '⚠️ Solo 1 disponibile!';
        $class = 'critical';
    } elseif ( $stock_qty >= 2 && $stock_qty <= 3 ) {
        $message = '⚠️ Solo ' . $stock_qty . ' disponibili per questa taglia!';
        $class = 'low';
    } elseif ( $stock_qty >= 4 && $stock_qty <= 5 ) {
        $message = '⚠️ Solo ' . $stock_qty . ' disponibili per questa taglia!';
        $class = 'low';
    } elseif ( $stock_qty >= 6 && $stock_qty <= 10 ) {
        $message = '⚠️ Solo ' . $stock_qty . ' disponibili per questa taglia!';
        $class = 'low';
    }

    return array(
        'message' => $message,
        'class' => $class
    );
}

/**
 * Change "Select options" button text to "Scegli" for variable products
 * This applies to the shop page and archive pages
 */
function sciuuuskids_variable_product_button_text( $text, $product ) {
    if ( $product->is_type( 'variable' ) ) {
        return __( 'Scegli', 'blocksy-child' );
    }
    return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'sciuuuskids_variable_product_button_text', 10, 2 );
