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
    // Depends on wc-blocks-style to ensure it loads AFTER WooCommerce block CSS
    $content_deps = array('blocksy-style', 'google-fonts-quicksand');
    if (wp_style_is('wc-blocks-style', 'registered') || wp_style_is('wc-blocks-style', 'enqueued')) {
        $content_deps[] = 'wc-blocks-style';
    }
    wp_enqueue_style(
        'custom-content',
        get_stylesheet_directory_uri() . '/assets/css/content-custom.css',
        $content_deps,
        '1.1.2'
    );
    
    // Homepage patterns CSS
    wp_enqueue_style(
        'homepage-patterns',
        get_stylesheet_directory_uri() . '/assets/css/homepage-patterns.css',
        array('blocksy-style', 'google-fonts-quicksand'),
        '1.0.2'
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
        '1.0.4',
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
 * Inject high-priority inline CSS for empty cart product grid.
 * This runs at wp_head priority 999 to ensure it loads AFTER all WooCommerce block CSS.
 * Fixes the legacy wc-block-grid product cards appearing as thin columns.
 */
function sciuuuskids_empty_cart_grid_fix() {
    if ( ! is_cart() ) {
        return;
    }
    ?>
    <style id="sciuuuskids-empty-cart-grid-fix">
        /* Force legacy WooCommerce product grid to use CSS Grid layout */
        body.woocommerce-cart .wc-block-grid.has-3-columns,
        body.woocommerce-cart .wp-block-woocommerce-empty-cart-block .wc-block-grid {
            display: block !important;
            width: 100% !important;
            max-width: 900px !important;
            margin: 0 auto !important;
        }
        body.woocommerce-cart .wc-block-grid.has-3-columns .wc-block-grid__products,
        body.woocommerce-cart .wc-block-grid .wc-block-grid__products,
        body.woocommerce-cart .wp-block-woocommerce-empty-cart-block .wc-block-grid__products,
        body.woocommerce-cart ul.wc-block-grid__products {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 25px !important;
            width: 100% !important;
            max-width: 100% !important;
            list-style: none !important;
            padding: 20px 0 !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }
        body.woocommerce-cart .wc-block-grid.has-3-columns .wc-block-grid__product,
        body.woocommerce-cart .wc-block-grid .wc-block-grid__product,
        body.woocommerce-cart .wp-block-woocommerce-empty-cart-block .wc-block-grid__product {
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            flex: unset !important;
            float: none !important;
            margin: 0 !important;
        }
        body.woocommerce-cart .wc-block-grid__product .wc-block-grid__product-link {
            display: block !important;
            width: 100% !important;
        }
        body.woocommerce-cart .wc-block-grid__product .wc-block-grid__product-image {
            display: block !important;
            width: 100% !important;
        }
    </style>
    <?php
}
add_action('wp_head', 'sciuuuskids_empty_cart_grid_fix', 999);

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
    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_count_class = $cart_count > 0 ? 'cart-count' : 'cart-count is-empty';
    $cart_count_display = $cart_count > 0 ? $cart_count : '';
    ob_start();
    ?>
    <span class="<?php echo esc_attr($cart_count_class); ?>" data-count="<?php echo esc_attr($cart_count); ?>">
        <?php echo esc_html($cart_count_display); ?>
    </span>
    <?php
    $fragments['span.cart-count'] = ob_get_clean();
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'sciuuuskids_cart_count_fragments');

/**
 * AJAX endpoint to get current cart count
 * Used by WooCommerce Blocks cart page which doesn't trigger traditional events
 */
function sciuuuskids_ajax_get_cart_count() {
    if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
        wp_send_json_error( 'WooCommerce not available' );
    }

    $cart_count = WC()->cart->get_cart_contents_count();
    wp_send_json_success( array(
        'count' => $cart_count,
        'display' => $cart_count > 0 ? $cart_count : '',
        'class' => $cart_count > 0 ? 'cart-count' : 'cart-count is-empty',
    ) );
}
add_action( 'wp_ajax_sciuuuskids_get_cart_count', 'sciuuuskids_ajax_get_cart_count' );
add_action( 'wp_ajax_nopriv_sciuuuskids_get_cart_count', 'sciuuuskids_ajax_get_cart_count' );

/**
 * Localize script with AJAX URL for cart count updates
 */
function sciuuuskids_localize_cart_ajax() {
    wp_localize_script( 'custom-scripts', 'sciuuuskidsCartAjax', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'action'  => 'sciuuuskids_get_cart_count',
    ) );
}
add_action( 'wp_enqueue_scripts', 'sciuuuskids_localize_cart_ajax', 20 );

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

/**
 * Customize WooCommerce Blocks Cart - Empty Cart State
 *
 * Replaces default empty cart with custom astronaut image
 */
function sciuuuskids_customize_empty_cart_block( $block_content, $block ) {
    // Only modify on cart page
    if ( ! is_cart() ) {
        return $block_content;
    }

    // Check if this contains the empty cart block
    if ( strpos( $block_content, 'wp-block-woocommerce-empty-cart-block' ) !== false ) {

        // Get the custom astronaut image URL
        $astronaut_image = get_stylesheet_directory_uri() . '/assets/images/cart/empty-cart-astronaut.png';

        // Create custom empty cart HTML with image and centered text
        $custom_image_html = '<div class="sciuuuskids-empty-cart-image-wrapper">
            <img src="' . esc_url( $astronaut_image ) . '" alt="Carrello vuoto" class="sciuuuskids-empty-cart-image" />
            <span class="sciuuuskids-empty-cart-text">Carrello vuoto</span>
        </div>';

        // Replace the entire h2 title with just the image
        $block_content = preg_replace(
            '/<h2[^>]*class="[^"]*wc-block-cart__empty-cart__title[^"]*"[^>]*>.*?<\/h2>/s',
            $custom_image_html,
            $block_content
        );

        // Hide "Browse store" link via string replacement
        $block_content = preg_replace(
            '/<p class="has-text-align-center"><a href="[^"]*">Browse store<\/a><\/p>/',
            '',
            $block_content
        );
    }

    return $block_content;
}
add_filter( 'render_block_woocommerce/cart', 'sciuuuskids_customize_empty_cart_block', 10, 2 );

/**
 * Customize WooCommerce Product New block - change to 3 columns and limit to 3 products
 */
function sciuuuskids_customize_product_new_block( $block_content, $block ) {
    // Only modify on cart page
    if ( ! is_cart() ) {
        return $block_content;
    }

    // Change columns from 4 to 3
    $block_content = str_replace( 'data-columns="4"', 'data-columns="3"', $block_content );
    $block_content = str_replace( 'has-4-columns', 'has-3-columns', $block_content );

    // Limit to 3 products by removing the 4th <li> element
    // Count how many products are in the grid
    preg_match_all( '/<li class="wc-block-grid__product">.*?<\/li>/s', $block_content, $matches );

    if ( isset( $matches[0] ) && count( $matches[0] ) > 3 ) {
        // Remove products beyond the 3rd
        $products = $matches[0];
        $products_to_keep = array_slice( $products, 0, 3 );

        // Replace the entire ul content with just 3 products
        $new_products_html = implode( '', $products_to_keep );
        $block_content = preg_replace(
            '/<ul class="wc-block-grid__products">.*?<\/ul>/s',
            '<ul class="wc-block-grid__products">' . $new_products_html . '</ul>',
            $block_content
        );
    }

    return $block_content;
}
add_filter( 'render_block_woocommerce/product-new', 'sciuuuskids_customize_product_new_block', 10, 2 );

/**
 * Translate "New in store" heading to Italian on cart page
 */
function sciuuuskids_translate_new_in_store( $block_content, $block ) {
    if ( ! is_cart() ) {
        return $block_content;
    }

    // Translate "New in store" to Italian
    $block_content = str_replace( 'New in store', 'Novità in negozio', $block_content );
    $block_content = str_replace( 'New in Store', 'Novità in negozio', $block_content );

    return $block_content;
}
add_filter( 'render_block_core/heading', 'sciuuuskids_translate_new_in_store', 10, 2 );
