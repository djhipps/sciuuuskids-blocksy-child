<?php
/**
 * Hero Section - Background Image (Elementor Style)
 * Main hero with background image, white text overlay, and CTA button
 */

return array(
    'title'       => __('Hero Section - Background Image', 'blocksy-child'),
    'description' => __('Hero with background image, white text overlay, and orange CTA button - matches live site', 'blocksy-child'),
    'categories'  => array('sciuuuskids-homepage'),
    'content'     => '<!-- wp:cover {"url":"https://sciuuuskids.it/wp-content/uploads/2025/02/home-page-pic-lira.webp","dimRatio":20,"overlayColor":"white","minHeight":75,"minHeightUnit":"vh","align":"full","className":"hero-section-bg","style":{"spacing":{"padding":{"top":"100px","bottom":"100px","left":"20px","right":"20px"}}}} -->
<div class="wp-block-cover alignfull hero-section-bg" style="padding-top:100px;padding-right:20px;padding-bottom:100px;padding-left:20px;min-height:75vh"><span aria-hidden="true" class="wp-block-cover__background has-white-background-color has-background-dim-20 has-background-dim"></span><img class="wp-block-cover__image-background" alt="Scarpe barefoot per bambini" src="https://sciuuuskids.it/wp-content/uploads/2025/02/home-page-pic-lira.webp" data-object-fit="cover"/><div class="wp-block-cover__inner-container">

    <!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"48px","fontWeight":"800","textTransform":"uppercase","letterSpacing":"3px"},"color":{"text":"#ffffff"},"spacing":{"margin":{"top":"0","bottom":"20px"}}}} -->
    <h1 class="wp-block-heading has-text-align-center has-text-color" style="color:#ffffff;margin-top:0;margin-bottom:20px;font-size:48px;font-weight:800;letter-spacing:3px;text-transform:uppercase">SCARPE BAREFOOT</h1>
    <!-- /wp:heading -->

    <!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"32px","fontWeight":"500","fontStyle":"italic","textTransform":"lowercase","letterSpacing":"2.5px"},"color":{"text":"#ffffff"},"spacing":{"margin":{"top":"0","bottom":"20px"}}}} -->
    <h2 class="wp-block-heading has-text-align-center has-text-color" style="color:#ffffff;margin-top:0;margin-bottom:20px;font-size:32px;font-style:italic;font-weight:500;letter-spacing:2.5px;text-transform:lowercase">che rispettano lo sviluppo dei bambini</h2>
    <!-- /wp:heading -->

    <!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"32px","fontWeight":"500","fontStyle":"italic","textTransform":"lowercase","letterSpacing":"2.5px"},"color":{"text":"#ffffff"},"spacing":{"margin":{"top":"0","bottom":"20px"}}}} -->
    <h2 class="wp-block-heading has-text-align-center has-text-color" style="color:#ffffff;margin-top:0;margin-bottom:20px;font-size:32px;font-style:italic;font-weight:500;letter-spacing:2.5px;text-transform:lowercase">scopri le nostre barefoot</h2>
    <!-- /wp:heading -->

    <!-- wp:separator {"backgroundColor":"white","className":"hero-divider","style":{"spacing":{"margin":{"top":"20px","bottom":"20px"}}}} -->
    <hr class="wp-block-separator has-text-color has-white-color has-alpha-channel-opacity has-white-background-color has-background hero-divider" style="margin-top:20px;margin-bottom:20px"/>
    <!-- /wp:separator -->

    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"15px","bottom":"0"}}}} -->
    <div class="wp-block-buttons" style="margin-top:15px;margin-bottom:0">
        <!-- wp:button {"style":{"border":{"radius":"30px","width":"2px","color":"#D86A01"},"typography":{"fontSize":"14px","fontWeight":"600","textTransform":"uppercase","letterSpacing":"1.8px"},"spacing":{"padding":{"top":"15px","bottom":"15px","left":"45px","right":"45px"}},"color":{"text":"#ffffff","background":"#009285"}}} -->
        <div class="wp-block-button has-custom-font-size" style="font-size:14px"><a class="wp-block-button__link has-text-color has-background has-border-color wp-element-button" href="#" style="border-color:#D86A01;border-width:2px;border-radius:30px;color:#ffffff;background-color:#009285;padding-top:15px;padding-right:45px;padding-bottom:15px;padding-left:45px;font-weight:600;letter-spacing:1.8px;text-transform:uppercase">SCOPRI DI PIù</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->

</div></div>
<!-- /wp:cover -->',
);