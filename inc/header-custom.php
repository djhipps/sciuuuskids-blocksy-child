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
        <div class="header-container">
            
            <!-- Cart Icon (Absolute positioned, top-right) -->
            <?php if (class_exists('WooCommerce')) : ?>
            <div class="header-cart-wrapper">
                <?php sciuuuskids_header_cart(); ?>
            </div>
            <?php endif; ?>
            
            <!-- Logo Section (Centered) -->
            <div class="header-logo-section">
                <div class="header-logo">
                    <?php sciuuuskids_logo(); ?>
                </div>
            </div>
            
            <!-- Wavy Decoration Above Navigation -->
            <div class="header-wave-decoration" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 283.5 27.8" preserveAspectRatio="none">
                    <path class="wave-fill" d="M283.5,9.7c0,0-7.3,4.3-14,4.6c-6.8,0.3-12.6,0-20.9-1.5c-11.3-2-33.1-10.1-44.7-5.7s-12.1,4.6-18,7.4c-6.6,3.2-20,9.6-36.6,9.3C131.6,23.5,99.5,7.2,86.3,8c-1.4,0.1-6.6,0.8-10.5,2c-3.8,1.2-9.4,3.8-17,4.7c-3.2,0.4-8.3,1.1-14.2,0.9c-1.5-0.1-6.3-0.4-12-1.6c-5.7-1.2-11-3.1-15.8-3.7C6.5,9.2,0,10.8,0,10.8V0h283.5V9.7z M260.8,11.3c-0.7-1-2-0.4-4.3-0.4c-2.3,0-6.1-1.2-5.8-1.1c0.3,0.1,3.1,1.5,6,1.9C259.7,12.2,261.4,12.3,260.8,11.3z M242.4,8.6c0,0-2.4-0.2-5.6-0.9c-3.2-0.8-10.3-2.8-15.1-3.5c-8.2-1.1-15.8,0-15.1,0.1c0.8,0.1,9.6-0.6,17.6,1.1c3.3,0.7,9.3,2.2,12.4,2.7C239.9,8.7,242.4,8.6,242.4,8.6z M185.2,8.5c1.7-0.7-13.3,4.7-18.5,6.1c-2.1,0.6-6.2,1.6-10,2c-3.9,0.4-8.9,0.4-8.8,0.5c0,0.2,5.8,0.8,11.2,0c5.4-0.8,5.2-1.1,7.6-1.6C170.5,14.7,183.5,9.2,185.2,8.5z M199.1,6.9c0.2,0-0.8-0.4-4.8,1.1c-4,1.5-6.7,3.5-6.9,3.7c-0.2,0.1,3.5-1.8,6.6-3C197,7.5,199,6.9,199.1,6.9z M283,6c-0.1,0.1-1.9,1.1-4.8,2.5s-6.9,2.8-6.7,2.7c0.2,0,3.5-0.6,7.4-2.5C282.8,6.8,283.1,5.9,283,6z M31.3,11.6c0.1-0.2-1.9-0.2-4.5-1.2s-5.4-1.6-7.8-2C15,7.6,7.3,8.5,7.7,8.6C8,8.7,15.9,8.3,20.2,9.3c2.2,0.5,2.4,0.5,5.7,1.6S31.2,11.9,31.3,11.6z M73,9.2c0.4-0.1,3.5-1.6,8.4-2.6c4.9-1.1,8.9-0.5,8.9-0.8c0-0.3-1-0.9-6.2-0.3S72.6,9.3,73,9.2z M71.6,6.7C71.8,6.8,75,5.4,77.3,5c2.3-0.3,1.9-0.5,1.9-0.6c0-0.1-1.1-0.2-2.7,0.2C74.8,5.1,71.4,6.6,71.6,6.7z M93.6,4.4c0.1,0.2,3.5,0.8,5.6,1.8c2.1,1,1.8,0.6,1.9,0.5c0.1-0.1-0.8-0.8-2.4-1.3C97.1,4.8,93.5,4.2,93.6,4.4z M65.4,11.1c-0.1,0.3,0.3,0.5,1.9-0.2s2.6-1.3,2.2-1.2s-0.9,0.4-2.5,0.8C65.3,10.9,65.5,10.8,65.4,11.1z M34.5,12.4c-0.2,0,2.1,0.8,3.3,0.9c1.2,0.1,2,0.1,2-0.2c0-0.3-0.1-0.5-1.6-0.4C36.6,12.8,34.7,12.4,34.5,12.4z M152.2,21.1c-0.1,0.1-2.4-0.3-7.5-0.3c-5,0-13.6-2.4-17.2-3.5c-3.6-1.1,10,3.9,16.5,4.1C150.5,21.6,152.3,21,152.2,21.1z"/>
                    <path class="wave-fill" d="M269.6,18c-0.1-0.1-4.6,0.3-7.2,0c-7.3-0.7-17-3.2-16.6-2.9c0.4,0.3,13.7,3.1,17,3.3C267.7,18.8,269.7,18,269.6,18z"/>
                    <path class="wave-fill" d="M227.4,9.8c-0.2-0.1-4.5-1-9.5-1.2c-5-0.2-12.7,0.6-12.3,0.5c0.3-0.1,5.9-1.8,13.3-1.2S227.6,9.9,227.4,9.8z"/>
                    <path class="wave-fill" d="M204.5,13.4c-0.1-0.1,2-1,3.2-1.1c1.2-0.1,2,0,2,0.3c0,0.3-0.1,0.5-1.6,0.4C206.4,12.9,204.6,13.5,204.5,13.4z"/>
                    <path class="wave-fill" d="M201,10.6c0-0.1-4.4,1.2-6.3,2.2c-1.9,0.9-6.2,3.1-6.1,3.1c0.1,0.1,4.2-1.6,6.3-2.6S201,10.7,201,10.6z"/>
                    <path class="wave-fill" d="M154.5,26.7c-0.1-0.1-4.6,0.3-7.2,0c-7.3-0.7-17-3.2-16.6-2.9c0.4,0.3,13.7,3.1,17,3.3C152.6,27.5,154.6,26.8,154.5,26.7z"/>
                    <path class="wave-fill" d="M41.9,19.3c0,0,1.2-0.3,2.9-0.1c1.7,0.2,5.8,0.9,8.2,0.7c4.2-0.4,7.4-2.7,7-2.6c-0.4,0-4.3,2.2-8.6,1.9c-1.8-0.1-5.1-0.5-6.7-0.4S41.9,19.3,41.9,19.3z"/>
                    <path class="wave-fill" d="M75.5,12.6c0.2,0.1,2-0.8,4.3-1.1c2.3-0.2,2.1-0.3,2.1-0.5c0-0.1-1.8-0.4-3.4,0C76.9,11.5,75.3,12.5,75.5,12.6z"/>
                    <path class="wave-fill" d="M15.6,13.2c0-0.1,4.3,0,6.7,0.5c2.4,0.5,5,1.9,5,2c0,0.1-2.7-0.8-5.1-1.4C19.9,13.7,15.7,13.3,15.6,13.2z"/>
                </svg>
            </div>

            <!-- Navigation Section (Centered, below logo) -->
            <div class="header-navigation-section">
                <div class="header-navigation" id="primary-menu">
                    <?php sciuuuskids_primary_navigation(); ?>
                </div>
                
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
        
        <!-- Orange Wave Divider -->
        <div class="header-wave-divider">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 12" preserveAspectRatio="none">
                <path class="wave-fill" d="M0,8 Q150,5 300,7 T600,6 T900,8 T1200,7 L1200,12 L0,12 Z"/>
            </svg>
        </div>
    </header>
    <?php
}
add_action('blocksy:header:before', 'sciuuuskids_custom_header');

/**
 * Display logo
 */
/**
 * Display logo
 */
function sciuuuskids_logo() {
    $logo_url = get_stylesheet_directory_uri() . '/assets/images/logo.webp';
    ?>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="custom-logo-link" rel="home">
        <img src="<?php echo esc_url($logo_url); ?>" 
             class="custom-logo" 
             alt="<?php echo esc_attr(get_bloginfo('name')); ?>" 
             width="683" 
             height="180">
    </a>
    <?php
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
    $cart_count_class = $cart_count > 0 ? 'cart-count' : 'cart-count is-empty';
    $cart_count_display = $cart_count > 0 ? $cart_count : '';
    ?>
    <div class="header-cart">
        <a href="<?php echo esc_url($cart_url); ?>" class="cart-link" aria-label="<?php esc_attr_e('Shopping Cart', 'blocksy-child'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
            </svg>
            <span class="<?php echo esc_attr($cart_count_class); ?>" data-count="<?php echo esc_attr($cart_count); ?>">
                <?php echo esc_html($cart_count_display); ?>
            </span>
        </a>
    </div>
    <?php
}
