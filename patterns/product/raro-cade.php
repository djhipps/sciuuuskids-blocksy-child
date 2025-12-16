<?php
/**
 * Title: Raro Cade Section
 * Slug: blocksy-child/raro-cade
 * Categories: featured
 * Description: Two column section with beach boy image and product features
 */
?>

<!-- wp:group {"align":"full","className":"raro-cade-section","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull raro-cade-section">

    <!-- wp:columns {"verticalAlignment":"stretch","className":"raro-cade-columns"} -->
    <div class="wp-block-columns are-vertically-aligned-stretch raro-cade-columns">

        <!-- wp:column {"verticalAlignment":"stretch","width":"50%","className":"raro-cade-image-column"} -->
        <div class="wp-block-column is-vertically-aligned-stretch raro-cade-image-column" style="flex-basis:50%">
            <!-- wp:heading {"textAlign":"center","level":2} -->
            <h2 class="wp-block-heading has-text-align-center">RARO CADE<br>CHI BEN CAMMINA</h2>
            <!-- /wp:heading -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"stretch","width":"50%","className":"raro-cade-content-column"} -->
        <div class="wp-block-column is-vertically-aligned-stretch raro-cade-content-column" style="flex-basis:50%">

            <!-- wp:columns {"className":"shoe-features-grid"} -->
            <div class="wp-block-columns shoe-features-grid">

                <!-- Feature 1: Comfort -->
                <!-- wp:column {"width":"25%","className":"feature-item"} -->
                <div class="wp-block-column feature-item" style="flex-basis:25%">
                    <!-- wp:image {"className":"feature-image"} -->
                    <figure class="wp-block-image feature-image">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/shoe-slide/slide1.png" alt="comfort icon"/>
                    </figure>
                    <!-- /wp:image -->

                    <!-- wp:heading {"level":3,"className":"feature-title"} -->
                    <h3 class="wp-block-heading feature-title">COMFORT</h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph {"className":"feature-text"} -->
                    <p class="feature-text">Assenza di cuciture interne, per dare il massimo comfort</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:column -->

                <!-- Feature 2: Flessibilità -->
                <!-- wp:column {"width":"25%","className":"feature-item"} -->
                <div class="wp-block-column feature-item" style="flex-basis:25%">
                    <!-- wp:image {"className":"feature-image"} -->
                    <figure class="wp-block-image feature-image">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/shoe-slide/slide2.png" alt="flexibility icon"/>
                    </figure>
                    <!-- /wp:image -->

                    <!-- wp:heading {"level":3,"className":"feature-title"} -->
                    <h3 class="wp-block-heading feature-title">FLESSIBILITÀ</h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph {"className":"feature-text"} -->
                    <p class="feature-text">Suola di 4-5mm molto flessibile, sottile ed estraibile</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:column -->

                <!-- Feature 3: Traspirante -->
                <!-- wp:column {"width":"25%","className":"feature-item"} -->
                <div class="wp-block-column feature-item" style="flex-basis:25%">
                    <!-- wp:image {"className":"feature-image"} -->
                    <figure class="wp-block-image feature-image">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/shoe-slide/slide3.png" alt="breathable icon"/>
                    </figure>
                    <!-- /wp:image -->

                    <!-- wp:heading {"level":3,"className":"feature-title"} -->
                    <h3 class="wp-block-heading feature-title">TRASPIRANTE</h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph {"className":"feature-text"} -->
                    <p class="feature-text">Fodera in pelle completamente traspirante</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:column -->

                <!-- Feature 4: Qualità -->
                <!-- wp:column {"width":"25%","className":"feature-item"} -->
                <div class="wp-block-column feature-item" style="flex-basis:25%">
                    <!-- wp:image {"className":"feature-image"} -->
                    <figure class="wp-block-image feature-image">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/shoe-slide/slide4.png" alt="quality icon"/>
                    </figure>
                    <!-- /wp:image -->

                    <!-- wp:heading {"level":3,"className":"feature-title"} -->
                    <h3 class="wp-block-heading feature-title">QUALITÀ</h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph {"className":"feature-text"} -->
                    <p class="feature-text">Cura in ogni dettaglio: confortevoli, resistenti, affidabili e durature</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:column -->

            </div>
            <!-- /wp:columns -->

        </div>
        <!-- /wp:column -->

    </div>
    <!-- /wp:columns -->

</div>
<!-- /wp:group -->
