<?php
/**
 * Custom Header Functions for SciuuuS Kids
 * 
 * @package Blocksy_Child_SciuuusKids
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom header widget area
 */
function sciuuuskids_register_header_widgets() {
    register_sidebar(array(
        'name'          => __('Header Top Bar', 'blocksy-child'),
        'id'            => 'header-top-bar',
        'description'   => __('Widgets in this area will be shown in the header top bar.', 'blocksy-child'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'sciuuuskids_register_header_widgets');

/**
 * Custom header output
 */
function sciuuuskids_custom_header() {
    ?>
    <header class="sciuuuskids-custom-header" role="banner">
        <div class="header-container container">
            <div class="header-row">
                
                <!-- Logo Section -->
                <div class="header-logo">
                    <?php sciuuuskids_logo(); ?>
                </div>
                
                <!-- Navigation Section -->
                <div class="header-navigation" id="primary-menu">
                    <?php sciuuuskids_primary_navigation(); ?>
                </div>
                
                <!-- WooCommerce Section -->
                <?php if (class_exists('WooCommerce')) : ?>
                <div class="header-woo-elements">
                    <?php sciuuuskids_header_search(); ?>
                    <?php sciuuuskids_header_account(); ?>
                    <?php sciuuuskids_header_cart(); ?>
                </div>
                <?php endif; ?>
                
                <!-- Mobile Menu Toggle -->
                <div class="mobile-menu-toggle">
                    <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle menu', 'blocksy-child'); ?>">
                        <span class="menu-icon"></span>
                        <span class="menu-icon"></span>
                        <span class="menu-icon"></span>
                    </button>
                </div>
                
            </div>
        </div>
    </header>
    <?php
}
add_action('blocksy:header:before', 'sciuuuskids_custom_header');

/**
 * Display logo
 */
function sciuuuskids_logo() {
    if (has_custom_logo()) {
        the_custom_logo();
    } else {
        ?>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title" rel="home">
            <?php bloginfo('name'); ?>
        </a>
        <?php
        $description = get_bloginfo('description', 'display');
        if ($description || is_customize_preview()) :
            ?>
            <p class="site-description"><?php echo $description; ?></p>
            <?php
        endif;
    }
}

/**
 * Display primary navigation
 */
function sciuuuskids_primary_navigation() {
    if (has_nav_menu('primary')) {
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_id'        => 'primary-menu',
            'menu_class'     => 'primary-menu',
            'container'      => 'nav',
            'container_class'=> 'main-navigation',
            'fallback_cb'    => false,
            'depth'          => 2,
        ));
    } else {
        // Fallback if no menu is assigned
        ?>
        <nav class="main-navigation">
            <ul class="primary-menu">
                <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'blocksy-child'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/shop')); ?>"><?php _e('Shop', 'blocksy-child'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/contatti')); ?>"><?php _e('Contatti', 'blocksy-child'); ?></a></li>
            </ul>
        </nav>
        <?php
    }
}

/**
 * Header search
 */
function sciuuuskids_header_search() {
    ?>
    <div class="header-search">
        <button class="search-toggle" aria-label="<?php esc_attr_e('Toggle search', 'blocksy-child'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </button>
        <div class="search-form-wrapper">
            <?php get_search_form(); ?>
        </div>
    </div>
    <?php
}

/**
 * Header account link
 */
function sciuuuskids_header_account() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    $account_url = get_permalink(get_option('woocommerce_myaccount_page_id'));
    ?>
    <div class="header-account">
        <a href="<?php echo esc_url($account_url); ?>" class="account-link" aria-label="<?php esc_attr_e('Account', 'blocksy-child'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <?php if (is_user_logged_in()) : ?>
                <span class="account-text"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
            <?php endif; ?>
        </a>
    </div>
    <?php
}

/**
 * Header cart
 */
function sciuuuskids_header_cart() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    $cart_url = wc_get_cart_url();
    $cart_count = WC()->cart->get_cart_contents_count();
    ?>
    <div class="header-cart">
        <a href="<?php echo esc_url($cart_url); ?>" class="cart-link" aria-label="<?php esc_attr_e('Shopping Cart', 'blocksy-child'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
            </svg>
            <?php if ($cart_count > 0) : ?>
                <span class="cart-count"><?php echo esc_html($cart_count); ?></span>
            <?php endif; ?>
        </a>
    </div>
    <?php
}
