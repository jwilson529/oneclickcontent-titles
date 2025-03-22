(function($) {
    'use strict';

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
        console.log('initializeAutoSave');
        $('.occ_titles-settings-form')
            .find('input, select, textarea')
            .not('[name="occ_titles_openai_api_key"], [name="occ_titles_google_api_key"]')
            .on('input change', debounce(function() {
                showNotification('Saving settings....', 'success');
                autoSaveField($(this));
            }, 500));
    }

    let isProcessing = false; // Prevent multiple simultaneous AJAX requests

    // Auto-save the field value via AJAX
    function autoSaveField($field) {
        if (isProcessing) return;

        isProcessing = true;
        var fieldValue;
        var fieldName = $field.attr('name');

        // Handle checkbox fields
        if ($field.attr('type') === 'checkbox') {
            fieldValue = [];
            $('input[name="' + fieldName + '"]:checked').each(function() {
                fieldValue.push($(this).val());
            });
        } else {
            fieldValue = $field.val();
        }

        $.ajax({
            url: occ_titles_admin_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'occ_titles_auto_save',
                nonce: occ_titles_admin_vars.occ_titles_ajax_nonce,
                field_name: fieldName.replace('[]', ''),
                field_value: fieldValue
            }
        })
        .done(function(response) {
            if (response.success) {
                showNotification(response.data.message || 'Settings saved successfully.', 'success');
                if (response.data.refresh) {
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                }
            } else {
                showNotification(response.data.message || 'Failed to save settings.', 'error');
            }
        })
        .fail(function() {
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
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification(validationResponse.data.message || 'Invalid API key.', 'error');
                    }
                })
                .fail(function() {
                    showNotification('Error validating API key.', 'error');
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

    // Monitor OpenAI API key field
    openAiKeyField.on('input paste', debounce(function() {
        const provider = providerField.val();
        if (provider === 'openai') {
            validateApiKey($(this), 'openai');
        }
    }, 500));

    // Monitor Google Gemini API key field
    googleKeyField.on('input paste', debounce(function() {
        const provider = providerField.val();
        if (provider === 'google') {
            validateApiKey($(this), 'google');
        }
    }, 500));

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