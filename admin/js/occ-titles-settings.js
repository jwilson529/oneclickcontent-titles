(function($) {
    'use strict';

    let autoValidateRan = false;

    initializeAutoSave();

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // Initialize auto-save functionality for settings fields (excluding API key fields)
    function initializeAutoSave() {
        $('.occ_titles-settings-form')
            .find('input, select, textarea')
            .not('[name="occ_titles_openai_api_key"], [name="occ_titles_google_api_key"]')
            .on('input change', debounce(function() {
                autoSaveField($(this));
            }, 500));
    }

    let isProcessing = false; // Prevent multiple simultaneous AJAX requests

    // Auto-save the field value via AJAX
    function autoSaveField($field) {
        if (isProcessing) return;

        isProcessing = true;
        var payload = getFieldPayload($field);

        setSaveState('Saving...', 'saving');
        showNotification('Saving changes...', 'info');

        $.ajax({
            url: occ_titles_admin_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'occ_titles_auto_save',
                nonce: occ_titles_admin_vars.occ_titles_ajax_nonce,
                field_name: payload.fieldName,
                field_value: payload.fieldValue
            }
        })
        .done(function(response) {
            if (response.success) {
                setSaveState('Saved just now', 'saved');
                showNotification(response.data.message || 'Settings saved successfully.', 'success');
                if (response.data.refresh) {
                    setSaveState('Refreshing page...', 'saving');
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                }
            } else {
                setSaveState('Save failed', 'error');
                showNotification(response.data.message || 'Failed to save settings.', 'error');
            }
        })
        .fail(function() {
            setSaveState('Save failed', 'error');
            showNotification('Error saving settings.', 'error');
        })
        .always(function() {
            isProcessing = false;
        });
    }

    /**
     * Show a notification message.
     *
     * @param {String} message The message to display.
     * @param {String} type The type of notification (success, error).
     */
    function showNotification(message, type = 'success') {
        $('.occ_titles-notification').fadeOut('fast', function() {
            $(this).remove();
        });

        var $notification = $('<div class="occ_titles-notification ' + type + '">' + message + '</div>');
        $('body').append($notification);
        $notification.fadeIn('fast');

        setTimeout(function() {
            $notification.fadeOut('slow', function() {
                $notification.remove();
            });
        }, 2000);
    }

    /**
     * Monitor API key fields and provider selection for validation.
     */
    const openAiKeyField = $('input[name="occ_titles_openai_api_key"]');
    const googleKeyField = $('input[name="occ_titles_google_api_key"]');
    const providerField = $('select[name="occ_titles_ai_provider"]');
    function getBadge(provider) {
        return $('.occ_titles-api-badge[data-provider="' + provider + '"]');
    }

    function getBadgeMeta(provider) {
        return $('.occ_titles-api-meta[data-provider="' + provider + '"]');
    }

    function getStatusLabel(status) {
        if (occ_titles_admin_vars && occ_titles_admin_vars.strings) {
            if (status === 'valid') {
                return occ_titles_admin_vars.strings.badge_valid;
            }
            if (status === 'invalid') {
                return occ_titles_admin_vars.strings.badge_invalid;
            }
            return occ_titles_admin_vars.strings.badge_unknown;
        }

        if (status === 'valid') {
            return 'Valid';
        }
        if (status === 'invalid') {
            return 'Invalid';
        }
        return 'Not validated';
    }

    function getMetaLabel(status, checkedAt) {
        if (status === 'unknown' || !checkedAt) {
            return occ_titles_admin_vars && occ_titles_admin_vars.strings
                ? occ_titles_admin_vars.strings.badge_not_checked
                : 'Not checked yet.';
        }

        if (occ_titles_admin_vars && occ_titles_admin_vars.strings) {
            return occ_titles_admin_vars.strings.badge_last_checked.replace('%s', checkedAt);
        }

        return 'Last checked: ' + checkedAt;
    }

    function updateApiKeyBadge(provider, status, checkedAt) {
        const $badge = getBadge(provider);
        const $meta = getBadgeMeta(provider);

        if (!$badge.length || !$meta.length) {
            return;
        }

        $badge
            .removeClass('status-valid status-invalid status-unknown')
            .addClass('status-' + status)
            .attr('data-status', status)
            .text(getStatusLabel(status));

        $meta.text(getMetaLabel(status, checkedAt));
    }

    function validateApiKey($field, provider) {
        const apiKey = $field.val();
        const action = provider === 'openai' ? 'occ_titles_ajax_validate_openai_api_key' : 'occ_titles_ajax_validate_google_api_key';

        // Only validate if the field is visible and matches the current provider
        if (!$field.is(':visible')) return;

        addSpinnerWithMessage($field, 'Validating API key...');

        // First, save the API key
        $.ajax({
            url: occ_titles_admin_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'occ_titles_auto_save',
                nonce: occ_titles_admin_vars.occ_titles_ajax_nonce,
                field_name: $field.attr('name'),
                field_value: apiKey
            }
        })
        .done(function(saveResponse) {
            if (saveResponse.success) {
                // Then, validate the API key
                $.ajax({
                    url: occ_titles_admin_vars.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: action,
                        nonce: occ_titles_admin_vars.occ_titles_ajax_nonce,
                        api_key: apiKey
                    }
                })
                .done(function(validationResponse) {
                    if (validationResponse.success) {
                        showNotification('API key is valid and saved. Please wait...', 'success');
                        updateApiKeyBadge(provider, 'valid', occ_titles_admin_vars && occ_titles_admin_vars.now ? occ_titles_admin_vars.now : '');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification(validationResponse.data.message || 'Invalid API key.', 'error');
                        updateApiKeyBadge(provider, 'invalid', occ_titles_admin_vars && occ_titles_admin_vars.now ? occ_titles_admin_vars.now : '');
                    }
                })
                .fail(function(jqXHR) {
                    showNotification(getAjaxErrorMessage(jqXHR, 'Error validating API key.'), 'error');
                    updateApiKeyBadge(provider, 'invalid', occ_titles_admin_vars && occ_titles_admin_vars.now ? occ_titles_admin_vars.now : '');
                })
                .always(function() {
                    removeSpinnerWithMessage($field);
                });
            } else {
                showNotification(saveResponse.data.message || 'Failed to save API key.', 'error');
                removeSpinnerWithMessage($field);
            }
        })
        .fail(function() {
            showNotification('Error saving API key.', 'error');
            removeSpinnerWithMessage($field);
        });
    }

    /**
     * Extract a message from a failed AJAX response.
     *
     * @param {Object} jqXHR jQuery XHR object.
     * @param {string} fallback Fallback message.
     * @return {string} Error message.
     */
    function getAjaxErrorMessage(jqXHR, fallback) {
        if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message) {
            return jqXHR.responseJSON.data.message;
        }

        return fallback;
    }

    /**
     * Get the save payload for a given field.
     *
     * @param {jQuery} $field Field instance.
     * @return {{fieldName: string, fieldValue: (string|Array|Object)}} Payload.
     */
    function getFieldPayload($field) {
        var fieldName = $field.attr('name') || '';
        var payloadName = fieldName.replace('[]', '');
        var fieldValue;

        if (fieldName.indexOf('occ_titles_voice_profile[') === 0) {
            return {
                fieldName: 'occ_titles_voice_profile',
                fieldValue: collectVoiceProfileValues()
            };
        }

        if ($field.attr('type') === 'checkbox') {
            if (fieldName.indexOf('[]') !== -1) {
                fieldValue = [];
                $('input[name="' + fieldName + '"]:checked').each(function() {
                    fieldValue.push($(this).val());
                });
            } else {
                fieldValue = $field.is(':checked') ? $field.val() : '';
            }
        } else {
            fieldValue = $field.val();
        }

        return {
            fieldName: payloadName,
            fieldValue: fieldValue
        };
    }

    /**
     * Collect the full voice profile payload.
     *
     * @return {Object} Voice profile values.
     */
    function collectVoiceProfileValues() {
        return {
            tone: $('#occ_titles_voice_tone').val() || '',
            formality: $('#occ_titles_voice_formality').val() || '',
            sentence_length: $('#occ_titles_voice_sentence_length').val() || '',
            cta_style: $('#occ_titles_voice_cta').val() || '',
            must_use: $('#occ_titles_voice_must_use').val() || '',
            avoid: $('#occ_titles_voice_avoid').val() || '',
            examples: $('#occ_titles_voice_examples').val() || ''
        };
    }

    /**
     * Update the inline save-state label when present.
     *
     * @param {string} message Status text.
     * @param {string} state State class suffix.
     */
    function setSaveState(message, state) {
        var $value = $('.occ_titles-settings-save-state-value');

        if (!$value.length) {
            return;
        }

        $value
            .removeClass('is-ready is-saving is-saved is-error')
            .addClass('is-' + state)
            .text(message);
    }

    // Validate keys on explicit field completion instead of each keystroke.
    openAiKeyField.on('change blur', function() {
        const provider = providerField.val();
        if (provider === 'openai') {
            validateApiKey($(this), 'openai');
        }
    });

    googleKeyField.on('change blur', function() {
        const provider = providerField.val();
        if (provider === 'google') {
            validateApiKey($(this), 'google');
        }
    });

    // Monitor provider field change to refresh the page and validate visible key
    providerField.on('change', function() {
        const provider = $(this).val();
        autoSaveField($(this)); // Save the provider change

        // Wait for the page to potentially reload, then validate the visible key
        setTimeout(function() {
            if (provider === 'openai' && openAiKeyField.is(':visible') && openAiKeyField.val()) {
                validateApiKey(openAiKeyField, 'openai');
            } else if (provider === 'google' && googleKeyField.is(':visible') && googleKeyField.val()) {
                validateApiKey(googleKeyField, 'google');
            }
        }, 1000); // Delay to account for page reload
    });

    function initializeApiKeyStatus() {
        if (autoValidateRan) {
            return;
        }

        const provider = providerField.val();
        const fieldMap = {
            openai: openAiKeyField,
            google: googleKeyField
        };

        if (!provider || !fieldMap[provider] || !fieldMap[provider].length) {
            return;
        }

        const $badge = getBadge(provider);
        const $field = fieldMap[provider];
        const status = $badge.length ? $badge.attr('data-status') : '';
        const value = $field.val();

        if (value && status === 'unknown') {
            autoValidateRan = true;
            validateApiKey($field, provider);
        }
    }

    initializeApiKeyStatus();

    /**
     * Adds a spinner with a message below the input field.
     */
    function addSpinnerWithMessage($field, message) {
        $field.siblings('.occ-titles-spinner-container').remove();
        const spinnerContainer = $('<div class="occ-titles-spinner-container"></div>');
        const spinner = $('<div class="occ-titles-spinner"></div>');
        const spinnerMessage = $('<span class="occ-titles-spinner-message"></span>').text(message);
        spinnerContainer.append(spinner).append(spinnerMessage);
        $field.after(spinnerContainer);
        spinnerContainer.fadeIn('fast');
    }

    /**
     * Removes the spinner and message from below the input field.
     */
    function removeSpinnerWithMessage($field) {
        $field.siblings('.occ-titles-spinner-container').fadeOut('slow', function() {
            $(this).remove();
        });
    }

})(jQuery);
