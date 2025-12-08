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