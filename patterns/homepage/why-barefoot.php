<?php
/**
 * Why Barefoot Section - Light Blue Background with Orange Header
 * Feature section explaining benefits of barefoot shoes
 */

$theme_uri = get_stylesheet_directory_uri();

return array(
    'title'       => __('Why Barefoot Section', 'blocksy-child'),
    'description' => __('Light blue section with orange header and feature cards explaining barefoot shoe benefits', 'blocksy-child'),
    'categories'  => array('sciuuuskids-homepage'),
    'content'     => '<!-- wp:cover {"overlayColor":"custom-orange-overlay","customOverlayColor":"#ff7c00","dimRatio":50,"align":"full","className":"why-barefoot-section","style":{"spacing":{"padding":{"top":"100px","bottom":"20px","left":"20px","right":"20px"}},"color":{"background":"#ebf6ff"}}} -->
<div class="wp-block-cover alignfull why-barefoot-section has-custom-orange-overlay-background-color has-background-dim has-background" style="background-color:#ebf6ff;padding-top:100px;padding-right:20px;padding-bottom:20px;padding-left:20px"><span aria-hidden="true" class="wp-block-cover__background has-custom-orange-overlay-background-color has-background-dim" style="background-color:#ff7c00"></span><div class="wp-block-cover__inner-container">

    <!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"42px","fontWeight":"700"},"color":{"text":"#000000","background":"#ff7c00"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"40px","right":"40px"},"margin":{"top":"0","bottom":"40px"}}}} -->
    <h2 class="wp-block-heading has-text-align-center has-text-color has-background" style="color:#000000;background-color:#ff7c00;margin-top:0;margin-bottom:40px;padding-top:20px;padding-right:40px;padding-bottom:20px;padding-left:40px;font-size:42px;font-weight:700">Perchè barefoot?</h2>
    <!-- /wp:heading -->

    <!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"30px","left":"30px"},"margin":{"top":"0"}}}} -->
    <div class="wp-block-columns" style="margin-top:0">
        
        <!-- wp:column {"style":{"border":{"width":"3px","color":"#009285","radius":"20px"},"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"}},"color":{"background":"#ffffff"}}} -->
        <div class="wp-block-column has-border-color has-background" style="border-color:#009285;border-width:3px;border-radius:20px;background-color:#ffffff;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px">
            <!-- wp:image {"align":"center","width":"120px","height":"120px","sizeSlug":"full","className":"perche-icon"} -->
            <figure class="wp-block-image aligncenter size-full is-resized perche-icon"><img src="' . $theme_uri . '/assets/images/perche-images/perche-1.png" alt="Punta Larga" style="width:120px;height:120px;object-fit:contain"/></figure>
            <!-- /wp:image -->
            
            <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"24px","fontWeight":"700"}}} -->
            <h3 class="wp-block-heading has-text-align-center" style="font-size:24px;font-weight:700">PUNTA LARGA</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"}}} -->
            <p class="has-text-align-center" style="font-size:16px">Punto rotondo e largo per non stringere le dita dei nostri piccoli. Le dita possono così muoversi liberamente e rafforzano la stabilità delle ginocchia e di tutto il corpo.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column {"style":{"border":{"width":"3px","color":"#FC7D06","radius":"20px"},"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"}},"color":{"background":"#ffffff"}}} -->
        <div class="wp-block-column has-border-color has-background" style="border-color:#FC7D06;border-width:3px;border-radius:20px;background-color:#ffffff;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px">
            <!-- wp:image {"align":"center","width":"120px","height":"120px","sizeSlug":"full","className":"perche-icon"} -->
            <figure class="wp-block-image aligncenter size-full is-resized perche-icon"><img src="' . $theme_uri . '/assets/images/perche-images/perche-2.png" alt="Suola Sottile" style="width:120px;height:120px;object-fit:contain"/></figure>
            <!-- /wp:image -->
            
            <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"24px","fontWeight":"700"}}} -->
            <h3 class="wp-block-heading has-text-align-center" style="font-size:24px;font-weight:700">SUOLA SOTTILE</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"}}} -->
            <p class="has-text-align-center" style="font-size:16px">La suola sottile, spessa tra i 3mm e i 5mm, consente di avere un contatto quasi diretto con il suolo. Si ricevono così tutte le informazioni dal terreno, si asseconda una camminata più cosciente e stabile.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column {"style":{"border":{"width":"3px","color":"#009285","radius":"20px"},"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"}},"color":{"background":"#ffffff"}}} -->
        <div class="wp-block-column has-border-color has-background" style="border-color:#009285;border-width:3px;border-radius:20px;background-color:#ffffff;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px">
            <!-- wp:image {"align":"center","width":"120px","height":"120px","sizeSlug":"full","className":"perche-icon"} -->
            <figure class="wp-block-image aligncenter size-full is-resized perche-icon"><img src="' . $theme_uri . '/assets/images/perche-images/perche-3.png" alt="Flessibilità" style="width:120px;height:120px;object-fit:contain"/></figure>
            <!-- /wp:image -->
            
            <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"24px","fontWeight":"700"}}} -->
            <h3 class="wp-block-heading has-text-align-center" style="font-size:24px;font-weight:700">FLESSIBILITÀ</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"}}} -->
            <p class="has-text-align-center" style="font-size:16px">La flessibilità lascia una totale libertà di movimento. I piedi si sviluppano e si fortificano in modo naturale.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
    </div>
    <!-- /wp:columns -->
    
    <!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"30px","left":"30px"},"margin":{"top":"30px"}}}} -->
    <div class="wp-block-columns" style="margin-top:30px">
        
        <!-- wp:column {"style":{"border":{"width":"3px","color":"#FC7D06","radius":"20px"},"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"}},"color":{"background":"#ffffff"}}} -->
        <div class="wp-block-column has-border-color has-background" style="border-color:#FC7D06;border-width:3px;border-radius:20px;background-color:#ffffff;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px">
            <!-- wp:image {"align":"center","width":"120px","height":"120px","sizeSlug":"full","className":"perche-icon"} -->
            <figure class="wp-block-image aligncenter size-full is-resized perche-icon"><img src="' . $theme_uri . '/assets/images/perche-images/perche-4.png" alt="Leggerezza" style="width:120px;height:120px;object-fit:contain"/></figure>
            <!-- /wp:image -->
            
            <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"24px","fontWeight":"700"}}} -->
            <h3 class="wp-block-heading has-text-align-center" style="font-size:24px;font-weight:700">LEGGEREZZA</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"}}} -->
            <p class="has-text-align-center" style="font-size:16px">Il peso leggero delle scarpe barefoot permette movimenti naturali e liberi.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column {"style":{"border":{"width":"3px","color":"#009285","radius":"20px"},"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"}},"color":{"background":"#ffffff"}}} -->
        <div class="wp-block-column has-border-color has-background" style="border-color:#009285;border-width:3px;border-radius:20px;background-color:#ffffff;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px">
            <!-- wp:image {"align":"center","width":"120px","height":"120px","sizeSlug":"full","className":"perche-icon"} -->
            <figure class="wp-block-image aligncenter size-full is-resized perche-icon"><img src="' . $theme_uri . '/assets/images/perche-images/perche-5.png" alt="Suola Piatta" style="width:120px;height:120px;object-fit:contain"/></figure>
            <!-- /wp:image -->
            
            <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"24px","fontWeight":"700"}}} -->
            <h3 class="wp-block-heading has-text-align-center" style="font-size:24px;font-weight:700">SUOLA PIATTA</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"}}} -->
            <p class="has-text-align-center" style="font-size:16px">La suola piatta garantisce una postura corretta e naturale dalla testa ai piedi.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column {"style":{"border":{"width":"3px","color":"#FC7D06","radius":"20px"},"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"}},"color":{"background":"#ffffff"}}} -->
        <div class="wp-block-column has-border-color has-background" style="border-color:#FC7D06;border-width:3px;border-radius:20px;background-color:#ffffff;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px">
            <!-- wp:image {"align":"center","width":"120px","height":"120px","sizeSlug":"full","className":"perche-icon"} -->
            <figure class="wp-block-image aligncenter size-full is-resized perche-icon"><img src="' . $theme_uri . '/assets/images/perche-images/perche-6.png" alt="Forma Naturale del Piede" style="width:120px;height:120px;object-fit:contain"/></figure>
            <!-- /wp:image -->
            
            <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"24px","fontWeight":"700"}}} -->
            <h3 class="wp-block-heading has-text-align-center" style="font-size:24px;font-weight:700">FORMA NATURALE DEL PIEDE</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"}}} -->
            <p class="has-text-align-center" style="font-size:16px">La forma anatomica rispetta la struttura naturale del piede in crescita.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
    </div>
    <!-- /wp:columns -->

</div></div>
<!-- /wp:cover -->',
);