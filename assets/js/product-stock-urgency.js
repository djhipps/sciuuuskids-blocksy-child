/**
 * Product Stock Urgency Handler
 * Updates stock urgency message when variation is selected
 */
(function($) {
    'use strict';

    /**
     * Get stock urgency data based on quantity
     * Red (critical) only for 0-1 items, yellow (low) for 2+ items
     */
    function getStockUrgencyData(stockQty) {
        let message = '';
        let cssClass = '';

        if (stockQty === 0) {
            message = '❌ Temporaneamente esaurito';
            cssClass = 'critical';
        } else if (stockQty === 1) {
            message = '⚠️ Solo 1 disponibile!';
            cssClass = 'critical';
        } else if (stockQty >= 2 && stockQty <= 10) {
            message = `⚠️ Solo ${stockQty} disponibili per questa taglia!`;
            cssClass = 'low';
        }

        return { message, cssClass };
    }

    /**
     * Update stock urgency box
     */
    function updateStockUrgency(stockQty, isInStock) {
        const $urgencyBox = $('[data-stock-urgency]');

        if (!$urgencyBox.length) {
            return;
        }

        // Don't show for out of stock without quantity, or if quantity > 10
        if (!isInStock || stockQty > 10) {
            $urgencyBox.hide();
            return;
        }

        const urgencyData = getStockUrgencyData(stockQty);

        // Only show if we have a message
        if (urgencyData.message) {
            // Remove all urgency classes
            $urgencyBox.removeClass('critical low medium');

            // Add new class and update message
            $urgencyBox
                .addClass(urgencyData.cssClass)
                .text(urgencyData.message)
                .show();
        } else {
            $urgencyBox.hide();
        }
    }

    /**
     * Initialize stock urgency for variable products
     */
    function init() {
        const $variationForm = $('.variations_form');

        if (!$variationForm.length) {
            return;
        }

        // Listen for variation found event
        $variationForm.on('found_variation', function(event, variation) {
            // WooCommerce stores stock quantity in max_qty for variations
            const stockQty = variation.max_qty || 0;
            const isInStock = variation.is_in_stock;

            updateStockUrgency(stockQty, isInStock);
        });

        // Listen for variation reset event
        $variationForm.on('reset_data', function() {
            $('[data-stock-urgency]').hide();
        });
    }

    // Initialize when document is ready
    $(document).ready(init);

})(jQuery);
