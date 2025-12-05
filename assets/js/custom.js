/**
 * Custom JavaScript for SciuuuS Kids
 * 
 * @package Blocksy_Child_SciuuusKids
 * @version 1.0.3
 */

(function($) {
    'use strict';

    /**
     * Document Ready
     */
    $(document).ready(function() {
        
        // Initialize all functions
        initMobileMenu();
        initSearchToggle();
        initStickyHeader();
        initSmoothScroll();
        initDropdownMenus();
        
    });

/**
     * Mobile Menu Toggle
     */
    function initMobileMenu() {
        // Use more specific selector
        var menuToggle = jQuery('.mobile-menu-toggle .menu-toggle');
        var navigation = jQuery('.header-navigation-section .header-navigation');
        
        console.log('Menu toggle found:', menuToggle.length);
        console.log('Navigation found:', navigation.length);
        
        menuToggle.on('click', function(e) {
            e.preventDefault();
            console.log('Button clicked!');
            
            var isExpanded = jQuery(this).attr('aria-expanded') === 'true';
            
            // Toggle aria-expanded
            jQuery(this).attr('aria-expanded', !isExpanded);
            
            // Toggle active class
            navigation.toggleClass('active');
            
            console.log('Active class toggled. Has active:', navigation.hasClass('active'));
        });
        
        // Handle submenu clicks
        jQuery('.primary-menu .menu-item-has-children > a').on('click', function(e) {
            if (jQuery(window).width() <= 768) {
                e.preventDefault();
                jQuery(this).parent().toggleClass('active');
                jQuery(this).parent().siblings('.menu-item-has-children').removeClass('active');
            }
        });
        
        // Close on window resize
        jQuery(window).on('resize', function() {
            if (jQuery(window).width() > 768) {
                menuToggle.attr('aria-expanded', 'false');
                navigation.removeClass('active');
                jQuery('.primary-menu .menu-item-has-children').removeClass('active');
            }
        });
    }

    /**
     * Search Toggle
     */
    function initSearchToggle() {
        const searchToggle = $('.search-toggle');
        const searchWrapper = $('.search-form-wrapper');
        
        searchToggle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            $('.header-search').toggleClass('active');
            
            if ($('.header-search').hasClass('active')) {
                searchWrapper.find('input[type="search"]').focus();
            }
        });
        
        // Close search when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.header-search').length) {
                $('.header-search').removeClass('active');
            }
        });
        
        // Prevent closing when clicking inside search form
        searchWrapper.on('click', function(e) {
            e.stopPropagation();
        });
    }

    /**
     * Sticky Header on Scroll
     */
    function initStickyHeader() {
        const header = $('.sciuuuskids-custom-header');
        let lastScroll = 0;
        
        $(window).on('scroll', function() {
            const currentScroll = $(this).scrollTop();
            
            if (currentScroll > 100) {
                header.addClass('scrolled');
            } else {
                header.removeClass('scrolled');
            }
            
            // Optional: Hide header on scroll down, show on scroll up
            // if (currentScroll > lastScroll && currentScroll > 500) {
            //     header.addClass('header-hidden');
            // } else {
            //     header.removeClass('header-hidden');
            // }
            
            lastScroll = currentScroll;
        });
    }

    /**
     * Smooth Scroll for Anchor Links
     */
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            
            if (target.length) {
                e.preventDefault();
                
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            }
        });
    }

    /**
     * Dropdown Menu Accessibility
     */
    function initDropdownMenus() {
        const menuItems = $('.primary-menu .menu-item-has-children');
        
        // Keyboard navigation
        menuItems.find('> a').on('keydown', function(e) {
            const $this = $(this);
            const $parent = $this.parent();
            
            // Open submenu on Enter or Space
            if (e.key === 'Enter' || e.key === ' ') {
                if (window.innerWidth > 768) {
                    e.preventDefault();
                    $parent.toggleClass('focus');
                    $this.next('.sub-menu').find('a').first().focus();
                }
            }
            
            // Close submenu on Escape
            if (e.key === 'Escape') {
                $parent.removeClass('focus');
                $this.focus();
            }
        });
        
        // Handle focus out
        menuItems.find('.sub-menu').on('focusout', function(e) {
            const $this = $(this);
            const $parent = $this.parent();
            
            // Small delay to check if focus moved to another element in the same submenu
            setTimeout(function() {
                if (!$parent.find(':focus').length) {
                    $parent.removeClass('focus');
                }
            }, 100);
        });
    }

    /**
     * Window Resize Handler
     */
    $(window).on('resize', function() {
        // Close mobile menu on resize to desktop
        if (window.innerWidth > 768) {
            $('.menu-toggle').attr('aria-expanded', 'false');
            $('.header-navigation').removeClass('active');
            $('body').removeClass('mobile-menu-open');
            $('.primary-menu .menu-item-has-children').removeClass('active');
            $('.primary-menu .sub-menu').removeAttr('style');
        }
    });

    /**
     * Add to Cart AJAX Update (WooCommerce)
     */
    if (typeof wc_add_to_cart_params !== 'undefined') {
        $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
            // Cart count is already updated via WordPress fragments
            // Add animation to cart icon
            $('.cart-link').addClass('cart-updated');
            
            setTimeout(function() {
                $('.cart-link').removeClass('cart-updated');
            }, 1000);
        });
    }

    /**
     * Scroll to Top Button (Optional)
     */
    function initScrollToTop() {
        const scrollBtn = $('<button class="scroll-to-top" aria-label="Scroll to top"><svg width="24" height="24" viewBox="0 0 24 24"><path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z" fill="currentColor"/></svg></button>');
        
        $('body').append(scrollBtn);
        
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 500) {
                scrollBtn.fadeIn();
            } else {
                scrollBtn.fadeOut();
            }
        });
        
        scrollBtn.on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 600);
        });
    }
    
    // Uncomment to enable scroll to top button
    // initScrollToTop();

})(jQuery);
