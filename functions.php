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
        '1.1.1'
    );
    
    // Homepage patterns CSS
    wp_enqueue_style(
        'homepage-patterns',
        get_stylesheet_directory_uri() . '/assets/css/homepage-patterns.css',
        array('blocksy-style', 'google-fonts-quicksand'),
        '1.0.1'
    );

    // Product patterns CSS
    wp_enqueue_style(
        'product-patterns',
        get_stylesheet_directory_uri() . '/assets/css/product-patterns.css',
        array('blocksy-style', 'google-fonts-quicksand'),
        '1.0.0'
    );

    // Load on specific pages by slug (including cart page for "New in store" section)
    if ( is_shop() || is_product_category() || is_product_tag() || is_cart() || is_page( array( 'scarpe-bebe', 'scarpe-bambini', 'outlet' ) ) ) {
        wp_enqueue_style(
            'woocommerce-archive',
            get_stylesheet_directory_uri() . '/assets/css/woocommerce-archive.css',
            array('blocksy-style', 'google-fonts-quicksand'),
            '1.0.2'
        );
    }

    // Single product page styles
    if ( is_product() ) {
        wp_enqueue_style(
            'woocommerce-product',
            get_stylesheet_directory_uri() . '/assets/css/woocommerce-product.css',
            array('blocksy-style', 'google-fonts-quicksand'),
            '1.0.0'
        );

        wp_enqueue_script(
            'product-stock-urgency',
            get_stylesheet_directory_uri() . '/assets/js/product-stock-urgency.js',
            array('jquery', 'wc-add-to-cart-variation'),
            '1.0.0',
            true
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


// Register custom colors for patterns
function blocksy_child_register_custom_colors() {
    add_theme_support('editor-color-palette', array(
        array(
            'name'  => 'Dark Background',
            'slug'  => 'custom-dark',
            'color' => '#1B252F',
        ),
        array(
            'name'  => 'Gray Text',
            'slug'  => 'custom-gray',
            'color' => '#CFCFCF',
        ),
    ));
}
add_action('after_setup_theme', 'blocksy_child_register_custom_colors');


/**
 * Register block patterns
 */
function blocksy_child_register_patterns() {
    // Register pattern category
    register_block_pattern_category(
        'sciuuuskids',
        array(
            'label' => __('SciuuusKids Patterns', 'blocksy-child')
        )
    );

    // Register the Raro Cade pattern with PHP execution
    ob_start();
    include get_stylesheet_directory() . '/patterns/product/raro-cade.php';
    $pattern_content = ob_get_clean();

    register_block_pattern(
        'blocksy-child/raro-cade',
        array(
            'title'       => __('Raro Cade Section', 'blocksy-child'),
            'description' => __('Two column section with beach boy image and product features', 'blocksy-child'),
            'content'     => $pattern_content,
            'categories'  => array('sciuuuskids', 'featured'),
        )
    );

    // Register the Social CTA pattern with PHP execution
    ob_start();
    include get_stylesheet_directory() . '/patterns/product/social-cta.php';
    $pattern_content = ob_get_clean();

    register_block_pattern(
        'blocksy-child/social-cta',
        array(
            'title'       => __('Social Media CTA', 'blocksy-child'),
            'description' => __('Instagram call-to-action section with centered text', 'blocksy-child'),
            'content'     => $pattern_content,
            'categories'  => array('sciuuuskids', 'featured'),
        )
    );

    // Register the Wave Divider pattern with PHP execution
    ob_start();
    include get_stylesheet_directory() . '/patterns/product/wave-divider.php';
    $pattern_content = ob_get_clean();

    register_block_pattern(
        'blocksy-child/wave-divider',
        array(
            'title'       => __('Wave Divider', 'blocksy-child'),
            'description' => __('Orange wavy divider line', 'blocksy-child'),
            'content'     => $pattern_content,
            'categories'  => array('sciuuuskids', 'featured'),
        )
    );

    // Register the Season Description pattern with PHP execution
    ob_start();
    include get_stylesheet_directory() . '/patterns/product/season-description.php';
    $pattern_content = ob_get_clean();

    register_block_pattern(
        'blocksy-child/season-description',
        array(
            'title'       => __('Season Description', 'blocksy-child'),
            'description' => __('Simple seasonal product description with centered text', 'blocksy-child'),
            'content'     => $pattern_content,
            'categories'  => array('sciuuuskids', 'featured'),
        )
    );
}
add_action('init', 'blocksy_child_register_patterns');

/**
 * Add "Return to Shopping" link on order received page
 */
function sciuuuskids_add_return_to_shop_link() {
    echo '<div class="woocommerce-order-return-to-shop">';
    echo '<a href="' . esc_url( home_url('/') ) . '" class="button">' . __('Torna allo shopping', 'blocksy-child') . '</a>';
    echo '</div>';
}
add_action('woocommerce_thankyou', 'sciuuuskids_add_return_to_shop_link', 20);

/**
 * Increase WooCommerce AJAX variation threshold
 *
 * Products with more variations than this threshold load variation data via AJAX
 * instead of embedding it in the page HTML. This can break variation swatch plugins
 * from properly detecting which options should be disabled/out-of-stock.
 *
 * Default is 30. Increase to 100 to support products with up to 100 variations
 * (e.g., 10 sizes × 10 colors = 100 combinations).
 */
function sciuuuskids_increase_variation_threshold( $threshold ) {
    return 100;
}
add_filter( 'woocommerce_ajax_variation_threshold', 'sciuuuskids_increase_variation_threshold' );