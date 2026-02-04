<?php
/**
 * About Story Section - Ispirati dall'amore per i nostri figli
 * Two-column layout with text on left, image on right
 */

$theme_uri = get_stylesheet_directory_uri();

return array(
    'title'       => __('About Story Section', 'blocksy-child'),
    'description' => __('Light blue section with founder story - text on left, image on right', 'blocksy-child'),
    'categories'  => array('sciuuuskids-homepage'),
    'content'     => '<!-- wp:cover {"overlayColor":"custom-green-bg","customOverlayColor":"#009285","dimRatio":0,"align":"full","className":"about-story-section","style":{"spacing":{"padding":{"top":"80px","bottom":"80px","left":"40px","right":"40px"}}}} -->
<div class="wp-block-cover alignfull about-story-section" style="padding-top:80px;padding-right:40px;padding-bottom:80px;padding-left:40px"><span aria-hidden="true" class="wp-block-cover__background has-custom-green-bg-background-color has-background-dim-0 has-background-dim" style="background-color:#009285"></span><div class="wp-block-cover__inner-container">

    <!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"60px","left":"60px"}}}} -->
    <div class="wp-block-columns are-vertically-aligned-center">

        <!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
        <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">

            <!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"36px","fontWeight":"700","textTransform":"uppercase"},"spacing":{"margin":{"bottom":"30px"}},"color":{"text":"#000000"}}} -->
            <h2 class="wp-block-heading has-text-color" style="color:#000000;margin-bottom:30px;font-size:36px;font-weight:700;text-transform:uppercase">ISPIRATI DALL\'AMORE PER I NOSTRI FIGLI</h2>
            <!-- /wp:heading -->

            <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"15px"}},"color":{"text":"#333333"}}} -->
            <p class="has-text-color" style="color:#333333;margin-bottom:15px;font-size:18px;line-height:1.8">Quando sono diventata mamma, ho sentito di voler dedicare completamente il tempo a mio figlio. Godermi intensamente ogni giorno la vita con lui, vederlo crescere e prendermi cura di ogni sua necessità.</p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"15px"}},"color":{"text":"#333333"}}} -->
            <p class="has-text-color" style="color:#333333;margin-bottom:15px;font-size:18px;line-height:1.8">Amo giocare con lui, vivermi i suoi progressi ed imparare ad essere madre.</p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"15px"}},"color":{"text":"#333333"}}} -->
            <p class="has-text-color" style="color:#333333;margin-bottom:15px;font-size:18px;line-height:1.8">Grazie a lui ho deciso di intraprendere questo nuovo percorso. Come ogni genitore cerco di mostrargli il bello, provo ad offrirgli ciò che credo possa essere importante per la sua crescita.</p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"15px"}},"color":{"text":"#333333"}}} -->
            <p class="has-text-color" style="color:#333333;margin-bottom:15px;font-size:18px;line-height:1.8">Ho così conosciuto il mondo delle scarpe minimaliste, un mondo che mi ha affascinata sin da subito. Allora ho deciso di combinare la mia esperienza professionale con quella più recente di madre. Ho sentito che volevo intraprendere un percorso che avesse un significato più profondo, qualcosa che mi coinvolgesse più da vicino e che portasse magari dei benefici a mio figlio e quindi anche ad altri bambini.</p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"0"}},"color":{"text":"#333333"}}} -->
            <p class="has-text-color" style="color:#333333;margin-bottom:0;font-size:18px;line-height:1.8">Ed ecco che nasce SciuuuS Kids. Con il supporto di un\'azienda iberica, specializzata da oltre 30 anni nelle calzature per bambini, abbiamo sviluppato e portato il progetto delle scarpe minimaliste in Italia.</p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"top":"15px","bottom":"0"}},"color":{"text":"#333333"}}} -->
            <p class="has-text-color" style="color:#333333;margin-top:15px;margin-bottom:0;font-size:18px;line-height:1.8">Il sostegno prezioso di Damian, il mio compagno, è stato fondamentale in questo percorso. Siamo pronti a condividere con voi un mondo di scarpe colorate e salutari per i bambini, insieme ad un messaggio carico di cura e attenzione per i nostri piccoli.</p>
            <!-- /wp:paragraph -->

        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
        <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">

            <!-- wp:image {"id":1234,"sizeSlug":"large","linkDestination":"none","className":"story-image","style":{"border":{"radius":"20px"}}} -->
            <figure class="wp-block-image size-large has-custom-border story-image"><img src="' . $theme_uri . '/assets/images/homepage/about-story.webp" alt="Madre e bambino" style="border-radius:20px"/></figure>
            <!-- /wp:image -->

        </div>
        <!-- /wp:column -->

    </div>
    <!-- /wp:columns -->

</div></div>
<!-- /wp:cover -->',
);
