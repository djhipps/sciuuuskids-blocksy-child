<?php
/**
 * Delivery Banner Pattern
 * Homepage top section with free delivery message
 */

return array(
    'title'       => __('Delivery Banner', 'blocksy-child'),
    'description' => __('Free delivery banner with emojis - Full width green banner', 'blocksy-child'),
    'categories'  => array('sciuuuskids-homepage'),
    'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"20px","bottom":"20px","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}},"color":{"background":"#009285"}},"className":"delivery-banner","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull delivery-banner has-background" style="background-color:#009285;margin-top:0;margin-bottom:0;padding-top:20px;padding-right:0;padding-bottom:20px;padding-left:0">
    <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"22px","fontWeight":"600"},"color":{"text":"#ffffff"},"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
    <p class="has-text-align-center has-text-color" style="color:#ffffff;margin-top:0;margin-bottom:0;font-size:22px;font-weight:600">🚚 Consegna gratuita 📦</p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->',
);