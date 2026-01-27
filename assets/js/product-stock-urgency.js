/**
 * Product Stock Urgency Handler & Variation Availability Fix
 * Updates stock urgency message when variation is selected
 * Also ensures variation availability (disabled states) are correctly applied
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
     * Update variation swatch availability based on product variations data
     * This ensures disabled states are applied correctly for products
     * that may have had their variation data loaded via AJAX
     */
    function updateVariationAvailability($form) {
        const variationsData = $form.data('product_variations');

        // If variations data is false or empty, it's using AJAX - can't process on frontend
        if (!variationsData || variationsData === false) {
            return;
        }

        // Get all attribute wrappers
        const $attributeWrappers = $form.find('.woo-variation-items-wrapper');

        $attributeWrappers.each(function() {
            const $wrapper = $(this);
            const $items = $wrapper.find('.variable-item');
            const attributeName = $wrapper.find('ul').data('attribute_name');

            if (!attributeName) {
                return;
            }

            // Get current selections from other attributes
            const currentSelections = {};
            $form.find('.woo-variation-items-wrapper').each(function() {
                const $otherWrapper = $(this);
                const $selectedItem = $otherWrapper.find('.variable-item.selected');
                const otherAttrName = $otherWrapper.find('ul').data('attribute_name');

                if (otherAttrName && $selectedItem.length && otherAttrName !== attributeName) {
                    currentSelections[otherAttrName] = $selectedItem.data('value');
                }
            });

            // Check each item in this attribute
            $items.each(function() {
                const $item = $(this);
                const value = $item.data('value');

                // Check if this value has any available variation
                const hasAvailableVariation = variationsData.some(function(variation) {
                    // Check if this variation matches the current value
                    if (variation.attributes[attributeName] !== '' &&
                        variation.attributes[attributeName] !== value) {
                        return false;
                    }

                    // Check if variation is in stock
                    if (!variation.is_in_stock) {
                        return false;
                    }

                    // Check if variation matches all other current selections
                    for (const [otherAttr, otherValue] of Object.entries(currentSelections)) {
                        if (variation.attributes[otherAttr] !== '' &&
                            variation.attributes[otherAttr] !== String(otherValue)) {
                            return false;
                        }
                    }

                    return true;
                });

                // Update disabled state
                if (hasAvailableVariation) {
                    $item.removeClass('disabled').attr('tabindex', '0');
                } else {
                    $item.addClass('disabled').attr('tabindex', '-1');
                }
            });
        });
    }

    /**
     * Initialize stock urgency for variable products
     */
    function init() {
        const $variationForm = $('.variations_form');

        if (!$variationForm.length) {
            return;
        }

        // Initial availability check after form is loaded
        setTimeout(function() {
            updateVariationAvailability($variationForm);
        }, 100);

        // Listen for variation found event
        $variationForm.on('found_variation', function(event, variation) {
            // WooCommerce stores stock quantity in max_qty for variations
            const stockQty = variation.max_qty || 0;
            const isInStock = variation.is_in_stock;

            updateStockUrgency(stockQty, isInStock);
        });

        // Update availability when any swatch is clicked
        $variationForm.on('click', '.variable-item:not(.disabled)', function() {
            // Small delay to let WooCommerce process the selection first
            setTimeout(function() {
                updateVariationAvailability($variationForm);
            }, 50);
        });

        // Listen for variation reset event
        $variationForm.on('reset_data', function() {
            $('[data-stock-urgency]').hide();
            // Re-check availability on reset
            setTimeout(function() {
                updateVariationAvailability($variationForm);
            }, 50);
        });

        // Also listen for WooCommerce variation swatches update events
        $variationForm.on('wvs-selected', function() {
            setTimeout(function() {
                updateVariationAvailability($variationForm);
            }, 50);
        });
    }

    // Initialize when document is ready
    $(document).ready(init);

})(jQuery);
