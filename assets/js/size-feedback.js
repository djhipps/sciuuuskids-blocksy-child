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

    function clearFeedback() {
        var container = getContainer();
        if (!container) {
            return;
        }
        container.replaceChildren();
    }

    function appendText(parent, value) {
        parent.appendChild(document.createTextNode(value));
    }

    function buildMeasurementNode(slug, entry) {
        var wrapper = document.createElement('span');
        wrapper.className = 'sciuuus-sfb__measurement';

        var strong = document.createElement('strong');
        var soleLengthNum = Number(entry.lunghezza_suola_cm);
        var soleWidthNum = Number(entry.larghezza_suola_cm);

        if (!Number.isNaN(soleLengthNum) && soleLengthNum > 0) {
            appendText(wrapper, 'EU ' + slug + ' -> suola ');
            strong.textContent = soleLengthNum.toFixed(1) + (
                !Number.isNaN(soleWidthNum) && soleWidthNum > 0
                    ? ' x ' + soleWidthNum.toFixed(1)
                    : ''
            ) + ' cm';
            wrapper.appendChild(strong);
            return wrapper;
        }

        var minNum = Number(entry.cm_min);
        var maxNum = Number(entry.cm_max);
        if (Number.isNaN(minNum) || Number.isNaN(maxNum)) {
            return null;
        }

        appendText(wrapper, 'EU ' + slug + ' -> piede ');
        strong.textContent = minNum.toFixed(1) + '-' + maxNum.toFixed(1) + ' cm';
        wrapper.appendChild(strong);

        return wrapper;
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
        var root = document.createElement('div');
        root.className = 'sciuuus-sfb';

        var measurementNode = buildMeasurementNode(slug, entry);
        if (!measurementNode) {
            clearFeedback();
            return;
        }

        root.appendChild(measurementNode);

        if (entry.note) {
            var note = document.createElement('span');
            note.className = 'sciuuus-sfb__note';
            note.textContent = String(entry.note);
            root.appendChild(note);
        }

        var link = document.createElement('a');
        link.className = 'sciuuus-sfb__guide-link';
        link.href = String(config.guideUrl || '#');
        link.target = '_blank';
        link.rel = 'noopener';
        link.textContent = 'Come misurare il piede';
        root.appendChild(link);

        container.replaceChildren(root);
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
