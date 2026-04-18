(function () {
    'use strict';

    var config = window.sciuuusSizeFeedback;
    if (!config || !config.sizeMap || !config.attributeName) {
        return;
    }

    function getContainer() {
        return document.getElementById('sciuuus-size-feedback');
    }

    function getAttrKey() {
        if (config.attributeName.indexOf('attribute_') === 0) {
            return config.attributeName;
        }
        return 'attribute_' + config.attributeName;
    }

    function getField(form) {
        return form.querySelector('[name="' + getAttrKey() + '"]');
    }

    function getSizeSlug(form) {
        var field = getField(form);
        return field ? field.value : '';
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function clearFeedback() {
        var container = getContainer();
        if (!container) {
            return;
        }
        container.innerHTML = '';
    }

    function render(slug) {
        var container = getContainer();
        if (!container) {
            return;
        }

        if (!slug || !config.sizeMap[slug]) {
            clearFeedback();
            return;
        }

        var entry = config.sizeMap[slug];
        var safeSlug = escapeHtml(slug);
        var safeGuideUrl = escapeHtml(config.guideUrl || '#');
        var note = entry.note ? '<span class="sciuuus-sfb__note">' + escapeHtml(entry.note) + '</span>' : '';
        var measurementHtml = '';

        var soleLengthNum = Number(entry.lunghezza_suola_cm);
        var soleWidthNum = Number(entry.larghezza_suola_cm);
        if (!Number.isNaN(soleLengthNum) && soleLengthNum > 0) {
            var soleLength = soleLengthNum.toFixed(1);
            var soleWidth = !Number.isNaN(soleWidthNum) && soleWidthNum > 0 ? soleWidthNum.toFixed(1) : '';
            measurementHtml =
                'EU ' + safeSlug + ' &rarr; suola <strong>' + soleLength +
                (soleWidth ? ' x ' + soleWidth : '') +
                ' cm</strong>';
        } else {
            var minNum = Number(entry.cm_min);
            var maxNum = Number(entry.cm_max);
            if (Number.isNaN(minNum) || Number.isNaN(maxNum)) {
                clearFeedback();
                return;
            }
            measurementHtml =
                'EU ' + safeSlug + ' &rarr; piede <strong>' + minNum.toFixed(1) + '&ndash;' + maxNum.toFixed(1) + ' cm</strong>';
        }

        container.innerHTML =
            '<div class="sciuuus-sfb">' +
                '<span class="sciuuus-sfb__measurement">' +
                    measurementHtml +
                '</span>' +
                note +
                '<a class="sciuuus-sfb__guide-link" href="' + safeGuideUrl + '" target="_blank" rel="noopener">' +
                    'Come misurare il piede' +
                '</a>' +
            '</div>';
    }

    function getSlugFromVariation(variation) {
        if (!variation || typeof variation !== 'object') {
            return '';
        }

        var attrKey = getAttrKey();
        if (variation.attributes && variation.attributes[attrKey]) {
            return variation.attributes[attrKey];
        }
        if (variation[attrKey]) {
            return variation[attrKey];
        }

        return '';
    }

    function init() {
        var form = document.querySelector('form.variations_form');
        if (!form) {
            return;
        }

        var guideWrapper = form.querySelector('.sciuuus-auto-inserted');
        var feedbackContainer = getContainer();
        if (guideWrapper && feedbackContainer && !feedbackContainer.closest('.sciuuus-size-feedback-row')) {
            var row = document.createElement('div');
            row.className = 'sciuuus-size-feedback-row';
            guideWrapper.parentNode.insertBefore(row, guideWrapper);
            row.appendChild(feedbackContainer);
            row.appendChild(guideWrapper);
        }

        var field = getField(form);
        if (field) {
            field.addEventListener('change', function () {
                render(this.value);
            });

            render(field.value);
        }

        if (window.jQuery && window.jQuery.fn) {
            window.jQuery(form)
                .on('found_variation', function (_event, variation) {
                    var fromVariation = getSlugFromVariation(variation);
                    render(fromVariation || getSizeSlug(form));
                })
                .on('reset_data', function () {
                    clearFeedback();
                });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
