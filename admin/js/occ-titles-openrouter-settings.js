(function($) {
    "use strict";
        // Catalog filtering never changes the submitted model until a result is chosen.
        let openrouterModels = [];
        const modelSearch = $('#occ_titles-openrouter-search');
        const modelSelect = $('#occ_titles-openrouter-select');
        const modelId = $('#occ_titles_openrouter_model');
        const catalogStatus = $('#occ_titles-openrouter-catalog-status');
        function renderOpenrouterModels() {
            const labels = occ_titles_openrouter_labels;
            const words = modelSearch.val().trim().toLowerCase().split(/\s+/).filter(Boolean);
            const matches = openrouterModels.filter(function(model) {
                const text = (model.name + ' ' + model.id).toLowerCase();
                return words.every(word => text.includes(word));
            });
            modelSelect.empty().append($('<option>').val('').text(labels.choose));
            matches.forEach(function(model) {
                $('<option>').val(model.id).text(model.name + ' — ' + model.id).appendTo(modelSelect);
            });
            modelSelect.val(matches.some(model => model.id === modelId.val()) ? modelId.val() : '');
            modelSelect.prop('disabled', !matches.length);
            catalogStatus.text(matches.length ? labels.matches.replace('%d', matches.length) : labels.noMatches);
        }
        modelSearch.on('input', renderOpenrouterModels).on('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                modelSelect.trigger('focus');
            }
        });
        modelSelect.on('change', function() {
            if (this.value && this.value !== modelId.val()) {
                modelId.val(this.value).trigger('input');
            }
        });
        modelId.on('input change', function() {
            if (openrouterModels.length) renderOpenrouterModels();
        });

        // A test applies only to the exact inputs sent; edits invalidate pending results.
        let openrouterRevision = 0;
        const openrouterBadge = $('#occ_titles-openrouter-badge');
        const openrouterPreview = $('#occ_titles-openrouter-preview');
        function setOpenrouterBadge(state, label) {
            openrouterBadge.attr('data-state', state).text(label);
        }
        $('#occ_titles_openrouter_api_key, #occ_titles_openrouter_model').on('input change', function() {
            openrouterRevision++;
            setOpenrouterBadge('untested', occ_titles_openrouter_labels.untested);
            $('#occ_titles-openrouter-status').text(occ_titles_openrouter_labels.changed);
            openrouterPreview.empty().prop('hidden', true);
        });
        $('#occ_titles-openrouter-load, #occ_titles-openrouter-test').on('click', function() {
            const labels = occ_titles_openrouter_labels;
            const testing = this.id === 'occ_titles-openrouter-test';
            const revision = openrouterRevision;
            const buttons = $('#occ_titles-openrouter-load, #occ_titles-openrouter-test');
            const status = testing ? $('#occ_titles-openrouter-status') : catalogStatus;
            buttons.prop('disabled', true);
            status.text(testing ? labels.testing : labels.loading);
            if (testing) {
                setOpenrouterBadge('testing', labels.testing);
                openrouterPreview.empty().prop('hidden', true);
            }
            $.ajax({
                url: occ_titles_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                timeout: 130000,
                data: {
                    action: 'occ_titles_openrouter',
                    nonce: occ_titles_admin_vars.occ_titles_ajax_nonce,
                    operation: testing ? 'test' : 'models',
                    api_key: testing ? $('#occ_titles_openrouter_api_key').val() : '',
                    model: testing ? $('#occ_titles_openrouter_model').val() : ''
                }
            }).done(function(response) {
                if (testing && revision !== openrouterRevision) return;
                if (!response.success) {
                    status.text(response.data.message || labels.failed);
                    if (testing) setOpenrouterBadge('failed', labels.testFailed);
                } else if (testing) {
                    setOpenrouterBadge('passed', labels.passed);
                    status.text(response.data.message);
                    response.data.titles.forEach(function(point) {
                        $('<li>').text(point.text).appendTo(openrouterPreview);
                    });
                    openrouterPreview.prop('hidden', false);
                } else {
                    openrouterModels = response.data;
                    modelSearch.prop('disabled', false);
                    renderOpenrouterModels();
                    modelSearch.trigger('focus');
                }
            }).fail(function(xhr) {
                if (testing && revision !== openrouterRevision) return;
                const error = xhr.responseJSON && xhr.responseJSON.data;
                status.text(error && error.message ? error.message : labels.network);
                if (testing) setOpenrouterBadge('failed', labels.testFailed);
            }).always(function() {
                buttons.prop('disabled', false);
            });
        });


})(jQuery);
