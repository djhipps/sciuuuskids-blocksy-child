<?php
/**
 * Empty cart page
 *
 * This template overrides the default WooCommerce empty cart template.
 * Removes the crying emoji and shop link for a cleaner appearance.
 *
 * @package Blocksy_Child_SciuuusKids
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked wc_empty_cart_message - 10 (removed - we're replacing the default)
 */
?>

<div class="cart-empty-container">
    <p class="cart-empty woocommerce-info">
        <?php echo wp_kses_post( apply_filters( 'wc_empty_cart_message', __( 'Il tuo carrello è vuoto.', 'woocommerce' ) ) ); ?>
    </p>
</div>

<?php
/**
 * Note: We deliberately do NOT include the "return to shop" button
 * as per design requirements.
 *
 * The WooCommerce default action woocommerce_cart_is_empty is still fired
 * so any custom content added via that hook will still display.
 */
do_action( 'woocommerce_cart_is_empty' );
