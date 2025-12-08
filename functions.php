<?php
/**
 * Blocksy Child Theme Functions
 * SciuuuS Kids Customizations
 * 
 * @package Blocksy_Child_SciuuusKids
 * @version 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue parent and child theme styles
 */
function blocksy_child_enqueue_styles() {
    // Google Fonts - Quicksand
    wp_enqueue_style(
        'google-fonts-quicksand',
        'https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap',
        array(),
        null
    );
    
    // Parent theme style
    wp_enqueue_style(
        'blocksy-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->parent()->get('Version')
    );
    
    // Child theme style
    wp_enqueue_style(
        'blocksy-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('blocksy-style'),
        wp_get_theme()->get('Version')
    );
    
    // Custom header CSS
    wp_enqueue_style(
        'custom-header',
        get_stylesheet_directory_uri() . '/assets/css/header-custom.css',
        array('blocksy-style', 'google-fonts-quicksand'),
        '1.2.0'
    );
    
    // Custom footer CSS
    wp_enqueue_style(
        'custom-footer',
        get_stylesheet_directory_uri() . '/assets/css/footer-custom.css',
        array('blocksy-style'),
        '1.1.0'
    );
    
    // Custom content CSS (pages, posts, WooCommerce)
    wp_enqueue_style(
        'custom-content',
        get_stylesheet_directory_uri() . '/assets/css/content-custom.css',
        array('blocksy-style', 'google-fonts-quicksand'),
        '1.0.0'
    );
    
    // Homepage patterns CSS
    wp_enqueue_style(
        'homepage-patterns',
        get_stylesheet_directory_uri() . '/assets/css/homepage-patterns.css',
        array('blocksy-style', 'google-fonts-quicksand'),
        '1.0.0'
    );
    
    // Load on specific pages by slug
    if ( is_shop() || is_product_category() || is_product_tag() || is_page( array( 'scarpe-bebe', 'scarpe-bambini', 'outlet' ) ) ) {
        wp_enqueue_style(
            'woocommerce-archive',
            get_stylesheet_directory_uri() . '/assets/css/woocommerce-archive.css',
            array('blocksy-style', 'google-fonts-quicksand'),
            '1.0.1'
        );
    }
    
    // Custom JavaScript
    wp_enqueue_script(
        'custom-scripts',
        get_stylesheet_directory_uri() . '/assets/js/custom.js',
        array('jquery'),
        '1.0.3',
        true
    );
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_styles');

/**
 * Hide default Blocksy footer with inline CSS
 */
function sciuuuskids_hide_blocksy_footer() {
    echo '<style>
        /* Hide default Blocksy footer */
        #footer.ct-footer {
            display: none !important;
        }
        
        /* Ensure custom footer displays */
        footer.sciuuuskids-custom-footer {
            display: block !important;
        }
    </style>';
}
add_action('wp_head', 'sciuuuskids_hide_blocksy_footer', 100);

/**
 * Add body classes for custom header/footer
 */
function sciuuuskids_body_classes($classes) {
    $classes[] = 'using-custom-header';
    $classes[] = 'using-custom-footer';
    return $classes;
}
add_action('body_class', 'sciuuuskids_body_classes');

/**
 * Register navigation menus
 */
function sciuuuskids_register_menus() {
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'blocksy-child'),
        'footer'  => __('Footer Menu', 'blocksy-child'),
    ));
}
add_action('after_setup_theme', 'sciuuuskids_register_menus');

/**
 * Add theme support
 */
function sciuuuskids_theme_support() {
    // Add support for custom logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    // Add support for WooCommerce
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'sciuuuskids_theme_support');

/**
 * Include custom header functionality
 */
require_once get_stylesheet_directory() . '/inc/header-custom.php';

/**
 * Include custom footer functionality
 */
require_once get_stylesheet_directory() . '/inc/footer-custom.php';

/**
 * Include customizer settings
 */
require_once get_stylesheet_directory() . '/inc/customizer.php';

/**
 * WooCommerce: Update cart count via AJAX
 */
function sciuuuskids_cart_count_fragments($fragments) {
    ob_start();
    ?>
    <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    <?php
    $fragments['span.cart-count'] = ob_get_clean();
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'sciuuuskids_cart_count_fragments');

/**
 * Remove default Blocksy header/footer hooks
 */
function sciuuuskids_remove_default_header_footer() {
    // Remove Blocksy's default footer rendering
    remove_action('blocksy:footer:before', 'blocksy_output_footer', 10);
    remove_action('blocksy:footer:after', 'blocksy_output_footer', 10);
    remove_all_actions('blocksy:footer:render');
}
add_action('wp', 'sciuuuskids_remove_default_header_footer', 20);

/**
 * Custom excerpt length
 */
function sciuuuskids_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'sciuuuskids_excerpt_length');

/**
 * Custom excerpt more
 */
function sciuuuskids_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'sciuuuskids_excerpt_more');


/**
 * Custom Product Page Elements via Hooks
 */

// Before product summary (right column)
add_action( 'blocksy:woocommerce:product:before', function() {
    echo '<div class="custom-badge">Barefoot Shoes</div>';
});

// After product title
add_action( 'woocommerce_single_product_summary', function() {
    echo '<div class="custom-subtitle">Scarpine per bambini liberi</div>';
}, 6 ); // Priority 6 = after title (5)

// After short description
add_action( 'woocommerce_single_product_summary', function() {
    echo '<div class="size-guide-link">
        <a href="#size-guide">📏 Guida alle taglie</a>
    </div>';
}, 21 ); // Priority 21 = after excerpt (20)

// After add to cart button
add_action( 'woocommerce_single_product_summary', function() {
    echo '<div class="custom-benefits">
        ✓ Spedizione gratuita<br>
        ✓ Reso entro 30 giorni<br>
        ✓ Garanzia 2 anni
    </div>';
}, 31 ); // Priority 31 = after add to cart (30)

// After product tabs
add_action( 'woocommerce_after_single_product_summary', function() {
    echo '<div class="custom-trust-badges">
        <img src="..." alt="Sicuro">
        <img src="..." alt="Bio">
    </div>';
}, 15 );

// Before related products
add_action( 'woocommerce_after_single_product_summary', function() {
    echo '<h2>Potrebbero interessarti anche</h2>';
}, 19 ); // Priority 19 = just before related (20)

/**
 * Custom Product Page Elements
 * Add to functions.php
 */

// 1. Size guide button after short description
add_action( 'woocommerce_single_product_summary', function() {
    ?>
    <div class="custom-size-guide">
        <a href="#" class="size-guide-toggle">
            📏 Guida alle taglie
        </a>
        <div class="size-guide-popup" style="display:none;">
            <h3>Guida alle Taglie</h3>
            <table>
                <tr>
                    <th>Età</th>
                    <th>EU</th>
                    <th>CM</th>
                </tr>
                <tr>
                    <td>1-2 anni</td>
                    <td>20-22</td>
                    <td>12-13.5</td>
                </tr>
                <!-- Add more rows -->
            </table>
        </div>
    </div>
    <?php
}, 21 );

// 2. Trust badges after add to cart
add_action( 'woocommerce_single_product_summary', function() {
    ?>
    <div class="trust-badges">
        <div class="badge">
            <span class="icon">🚚</span>
            <span class="text">Spedizione gratuita oltre 50€</span>
        </div>
        <div class="badge">
            <span class="icon">↩️</span>
            <span class="text">Reso gratuito entro 30 giorni</span>
        </div>
        <div class="badge">
            <span class="icon">✓</span>
            <span class="text">Garanzia 2 anni</span>
        </div>
    </div>
    <?php
}, 31 );

// 3. Materials info as custom tab
add_filter( 'woocommerce_product_tabs', function( $tabs ) {
    $tabs['materials'] = [
        'title'    => 'Materiali',
        'priority' => 25,
        'callback' => function() {
            echo '<h2>Materiali Ecologici</h2>';
            echo '<p>Le nostre scarpe sono realizzate con materiali naturali e sostenibili...</p>';
        }
    ];
    return $tabs;
});

/**
 * Load modular WooCommerce customization files
 */
// Shop/Archive page hooks
if ( file_exists( get_stylesheet_directory() . '/inc/woocommerce/shop-hooks.php' ) ) {
    require_once get_stylesheet_directory() . '/inc/woocommerce/shop-hooks.php';
}

// Single product page hooks
if ( file_exists( get_stylesheet_directory() . '/inc/woocommerce/product-hooks.php' ) ) {
    require_once get_stylesheet_directory() . '/inc/woocommerce/product-hooks.php';
}


// Set custom WooCommerce image sizes
add_filter( 'woocommerce_get_image_size_thumbnail', function( $size ) {
    return array(
        'width'  => 360,
        'height' => 270,
        'crop'   => 1,
    );
});

/**
 * Register Custom Block Pattern Categories
 */
function sciuuuskids_register_block_pattern_categories() {
    // Homepage patterns category
    register_block_pattern_category(
        'sciuuuskids-homepage',
        array(
            'label' => __('SciuuuS Kids - Homepage', 'blocksy-child'),
            'description' => __('Block patterns for the SciuuuS Kids homepage', 'blocksy-child'),
        )
    );
}
add_action('init', 'sciuuuskids_register_block_pattern_categories');

/**
 * Register Block Patterns from files
 */
function sciuuuskids_register_block_patterns() {
    $pattern_directory = get_stylesheet_directory() . '/patterns/homepage/';
    
    if (!is_dir($pattern_directory)) {
        return;
    }
    
    // Get all PHP files in the patterns directory
    $pattern_files = glob($pattern_directory . '*.php');
    
    foreach ($pattern_files as $pattern_file) {
        register_block_pattern(
            'sciuuuskids/' . basename($pattern_file, '.php'),
            require $pattern_file
        );
    }
}
add_action('init', 'sciuuuskids_register_block_patterns');