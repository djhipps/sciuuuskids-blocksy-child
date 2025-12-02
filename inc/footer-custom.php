<?php
/**
 * Custom Footer Functions for SciuuuS Kids
 * 
 * @package Blocksy_Child_SciuuusKids
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register footer widget areas
 */
function sciuuuskids_register_footer_widgets() {
    // Footer Column 1 - About/Company Info
    register_sidebar(array(
        'name'          => __('Footer Column 1', 'blocksy-child'),
        'id'            => 'footer-1',
        'description'   => __('Appears in the first footer column. Typically for company info.', 'blocksy-child'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Footer Column 2 - Quick Links
    register_sidebar(array(
        'name'          => __('Footer Column 2', 'blocksy-child'),
        'id'            => 'footer-2',
        'description'   => __('Appears in the second footer column. Typically for quick links.', 'blocksy-child'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Footer Column 3 - Customer Service
    register_sidebar(array(
        'name'          => __('Footer Column 3', 'blocksy-child'),
        'id'            => 'footer-3',
        'description'   => __('Appears in the third footer column. Typically for customer service.', 'blocksy-child'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Footer Column 4 - Contact/Newsletter
    register_sidebar(array(
        'name'          => __('Footer Column 4', 'blocksy-child'),
        'id'            => 'footer-4',
        'description'   => __('Appears in the fourth footer column. Typically for contact/newsletter.', 'blocksy-child'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'sciuuuskids_register_footer_widgets');

/**
 * Custom footer output
 */
function sciuuuskids_custom_footer() {
    ?>
    <footer class="sciuuuskids-custom-footer" role="contentinfo">
        
        <!-- Main Footer Widgets -->
        <div class="footer-widgets">
            <div class="footer-container container">
                <div class="footer-row">
                    
                    <?php if (is_active_sidebar('footer-1')) : ?>
                    <div class="footer-column footer-col-1">
                        <?php dynamic_sidebar('footer-1'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-2')) : ?>
                    <div class="footer-column footer-col-2">
                        <?php dynamic_sidebar('footer-2'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-3')) : ?>
                    <div class="footer-column footer-col-3">
                        <?php dynamic_sidebar('footer-3'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-4')) : ?>
                    <div class="footer-column footer-col-4">
                        <?php dynamic_sidebar('footer-4'); ?>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom / Copyright -->
        <div class="footer-bottom">
            <div class="footer-container container">
                <div class="footer-bottom-row">
                    
                    <div class="footer-copyright">
                        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('Tutti i diritti riservati.', 'blocksy-child'); ?></p>
                    </div>
                    
                    <div class="footer-social">
                        <?php sciuuuskids_social_links(); ?>
                    </div>
                    
                    <div class="footer-links">
                        <?php sciuuuskids_footer_menu(); ?>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </footer>
    <?php
}
add_action('blocksy:footer:before', 'sciuuuskids_custom_footer');

/**
 * Footer menu
 */
function sciuuuskids_footer_menu() {
    if (has_nav_menu('footer')) {
        wp_nav_menu(array(
            'theme_location' => 'footer',
            'menu_class'     => 'footer-menu',
            'container'      => false,
            'fallback_cb'    => false,
            'depth'          => 1,
        ));
    }
}

/**
 * Social media links
 */
function sciuuuskids_social_links() {
    $social_links = array(
        'facebook'  => get_theme_mod('sciuuuskids_facebook_url', ''),
        'instagram' => get_theme_mod('sciuuuskids_instagram_url', ''),
        'twitter'   => get_theme_mod('sciuuuskids_twitter_url', ''),
        'pinterest' => get_theme_mod('sciuuuskids_pinterest_url', ''),
        'youtube'   => get_theme_mod('sciuuuskids_youtube_url', ''),
    );
    
    // Filter out empty links
    $social_links = array_filter($social_links);
    
    if (empty($social_links)) {
        return;
    }
    ?>
    <div class="social-links">
        <?php foreach ($social_links as $platform => $url) : ?>
            <a href="<?php echo esc_url($url); ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="social-link social-<?php echo esc_attr($platform); ?>"
               aria-label="<?php echo esc_attr(ucfirst($platform)); ?>">
                <span class="screen-reader-text"><?php echo ucfirst($platform); ?></span>
                <?php echo sciuuuskids_get_social_icon($platform); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Get social media icon SVG
 */
function sciuuuskids_get_social_icon($platform) {
    $icons = array(
        'facebook' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        
        'instagram' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
        
        'twitter' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>',
        
        'pinterest' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>',
        
        'youtube' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
    );
    
    return isset($icons[$platform]) ? $icons[$platform] : '';
}
