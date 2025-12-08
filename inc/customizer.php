<?php
/**
 * Customizer Settings for SciuuuS Kids
 * 
 * @package Blocksy_Child_SciuuusKids
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add customizer settings for social media and other options
 */
function sciuuuskids_customize_register($wp_customize) {
    
    //==============================================
    // Social Media Section
    //==============================================
    $wp_customize->add_section('sciuuuskids_social', array(
        'title'    => __('Social Media Links', 'blocksy-child'),
        'priority' => 130,
    ));
    
    // Facebook
    $wp_customize->add_setting('sciuuuskids_facebook_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('sciuuuskids_facebook_url', array(
        'label'   => __('Facebook URL', 'blocksy-child'),
        'section' => 'sciuuuskids_social',
        'type'    => 'url',
    ));
    
    // Instagram
    $wp_customize->add_setting('sciuuuskids_instagram_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('sciuuuskids_instagram_url', array(
        'label'   => __('Instagram URL', 'blocksy-child'),
        'section' => 'sciuuuskids_social',
        'type'    => 'url',
    ));
    
    // Twitter
    $wp_customize->add_setting('sciuuuskids_twitter_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('sciuuuskids_twitter_url', array(
        'label'   => __('Twitter URL', 'blocksy-child'),
        'section' => 'sciuuuskids_social',
        'type'    => 'url',
    ));
    
    // Pinterest
    $wp_customize->add_setting('sciuuuskids_pinterest_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('sciuuuskids_pinterest_url', array(
        'label'   => __('Pinterest URL', 'blocksy-child'),
        'section' => 'sciuuuskids_social',
        'type'    => 'url',
    ));
    
    // YouTube
    $wp_customize->add_setting('sciuuuskids_youtube_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('sciuuuskids_youtube_url', array(
        'label'   => __('YouTube URL', 'blocksy-child'),
        'section' => 'sciuuuskids_social',
        'type'    => 'url',
    ));
    
    //==============================================
    // Header Options Section
    //==============================================
    $wp_customize->add_section('sciuuuskids_header_options', array(
        'title'    => __('Header Options', 'blocksy-child'),
        'priority' => 131,
    ));
    
    // Show Search in Header
    $wp_customize->add_setting('sciuuuskids_show_search', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('sciuuuskids_show_search', array(
        'label'   => __('Show Search in Header', 'blocksy-child'),
        'section' => 'sciuuuskids_header_options',
        'type'    => 'checkbox',
    ));
    
    // Sticky Header
    $wp_customize->add_setting('sciuuuskids_sticky_header', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('sciuuuskids_sticky_header', array(
        'label'   => __('Enable Sticky Header', 'blocksy-child'),
        'section' => 'sciuuuskids_header_options',
        'type'    => 'checkbox',
    ));
    
    //==============================================
    // Footer Options Section
    //==============================================
    $wp_customize->add_section('sciuuuskids_footer_options', array(
        'title'    => __('Footer Options', 'blocksy-child'),
        'priority' => 132,
    ));
    
    // Footer Copyright Text
    $wp_customize->add_setting('sciuuuskids_footer_copyright', array(
        'default'           => '© ' . date('Y') . ' SciuuuS Kids. Tutti i diritti riservati.',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('sciuuuskids_footer_copyright', array(
        'label'   => __('Copyright Text', 'blocksy-child'),
        'section' => 'sciuuuskids_footer_options',
        'type'    => 'textarea',
    ));
    
    //==============================================
    // Colors Section
    //==============================================
    $wp_customize->add_section('sciuuuskids_colors', array(
        'title'    => __('Theme Colors', 'blocksy-child'),
        'priority' => 133,
    ));
    
    // Primary Color
    $wp_customize->add_setting('sciuuuskids_primary_color', array(
        'default'           => '#ff6b6b',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sciuuuskids_primary_color', array(
        'label'   => __('Primary Color', 'blocksy-child'),
        'section' => 'sciuuuskids_colors',
    )));
    
    // Secondary Color
    $wp_customize->add_setting('sciuuuskids_secondary_color', array(
        'default'           => '#2c3e50',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sciuuuskids_secondary_color', array(
        'label'   => __('Secondary Color', 'blocksy-child'),
        'section' => 'sciuuuskids_colors',
    )));
}
add_action('customize_register', 'sciuuuskids_customize_register');

/**
 * Output custom CSS from customizer
 */
function sciuuuskids_customizer_css() {
    $primary_color = get_theme_mod('sciuuuskids_primary_color', '#ff6b6b');
    $secondary_color = get_theme_mod('sciuuuskids_secondary_color', '#2c3e50');
    $sticky_header = get_theme_mod('sciuuuskids_sticky_header', true);
    ?>
    <style type="text/css">
        :root {
            --sciuuuskids-primary: <?php echo esc_attr($primary_color); ?>;
            --sciuuuskids-secondary: <?php echo esc_attr($secondary_color); ?>;
        }
        
        /* Primary color usage */
        .primary-menu li a:hover,
        .primary-menu li.current-menu-item a,
        .cart-link:hover,
        .account-link:hover,
        .cart-count,
        .social-link:hover {
            color: <?php echo esc_attr($primary_color); ?>;
        }
        
        .cart-count,
        .social-link:hover {
            background-color: <?php echo esc_attr($primary_color); ?>;
        }
        
        /* Secondary color usage */
        .sciuuuskids-custom-footer {
            background-color: <?php echo esc_attr($secondary_color); ?>;
        }
        
        /* Sticky header */
        <?php if ($sticky_header) : ?>
        .sciuuuskids-custom-header {
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        <?php else : ?>
        .sciuuuskids-custom-header {
            position: relative;
        }
        <?php endif; ?>
    </style>
    <?php
}
add_action('wp_head', 'sciuuuskids_customizer_css');
