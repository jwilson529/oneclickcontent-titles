(function($) {
    'use strict';

    // Prevent multiple executions
    if (window.occTitlesInitialized) {
        return;
    }
    window.occTitlesInitialized = true;

    $(document).ready(function() {
        // Editor mode detection
        function checkEditorMode() {
            var isClassicEditor = document.querySelector('.wp-editor-area') !== null;
            var isBlockEditor = !isClassicEditor;
            return { isClassicEditor, isBlockEditor };
        }
        window.checkEditorMode = checkEditorMode;
        window.editorMode = checkEditorMode();

        // SEO grade calculation
        function calculateSEOGrade(charCount) {
            var grade = '', score = 0, label = '';
            if (charCount >= 50 && charCount <= 60) {
                grade = '🟢'; score = 100; label = 'Excellent (50-60 characters)';
            } else if (charCount < 50) {
                grade = '🟡'; score = 75; label = 'Average (below 50 characters)';
            } else {
                grade = '🔴'; score = 50; label = 'Poor (above 60 characters)';
            }
            return { dot: grade, score: score, label: label };
        }

        // Sentiment emoji
        function getEmojiForSentiment(sentiment) {
            switch (sentiment) {
                case 'Positive': return '😊';
                case 'Negative': return '😟';
                case 'Neutral': return '😐';
                default: return '❓';
            }
        }

        // Keyword density
        function calculateKeywordDensity(text, keywords) {
            if (!keywords || !keywords.length) return 0;
            var keywordCount = keywords.reduce(function(count, keyword) {
                return count + (text.match(new RegExp(keyword, 'gi')) || []).length;
            }, 0);
            var wordCount = text.split(' ').length;
            return keywordCount / wordCount;
        }

        // Readability score
        function calculateReadabilityScore(text) {
            var wordCount = text.split(' ').length;
            var sentenceCount = text.split(/[.!?]+/).filter(function(sentence) {
                return sentence.trim().length > 0;
            }).length || 1;
            var syllableCount = text.split(/[aeiouy]+/).length - 1;
            return ((wordCount / sentenceCount) + (syllableCount / wordCount)) * 0.4;
        }

        // Overall score
        function calculateOverallScore(seoScore, sentiment, keywordDensity, readabilityScore) {
            var sentimentScore = sentiment === 'Positive' ? 100 : (sentiment === 'Neutral' ? 75 : 50);
            var keywordDensityScore = keywordDensity >= 0.01 && keywordDensity <= 0.03 ? 100 : 50;
            var readabilityScoreNormalized = 100 - Math.abs(readabilityScore - 10) * 10;
            return (seoScore + sentimentScore + keywordDensityScore + readabilityScoreNormalized) / 4;
        }

        // Set title in editor
        function setTitleInEditor(title) {
            if (window.editorMode.isBlockEditor) {
                wp.data.dispatch('core/editor').editPost({ title: title });
            } else if ($('input#title').length) {
                var titleInput = $('input#title');
                $('#title-prompt-text').empty();
                titleInput.val(title).focus().blur();
            }
        }

        // Display titles in table with keywords
        function displayTitles(titles) {
            $('#occ_titles_table_container').empty();
            var titlesTable = $(`
                <table id="occ_titles_table" class="widefat fixed" cellspacing="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Character Count</th>
                            <th>SEO Grade</th>
                            <th>Sentiment</th>
                            <th>Keyword Density</th>
                            <th>Keywords Used</th>
                            <th>Readability</th>
                            <th>Overall Score</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `);
            var tableBody = titlesTable.find('tbody');

            // Aggregate all keywords and find the best score
            var allKeywords = [];
            var bestScore = -Infinity;
            var rows = [];

            titles.forEach(function(titleObj) {
                var titleText = titleObj.text || titleObj;
                var charCount = titleText.length;
                var seoData = calculateSEOGrade(charCount);
                var sentimentEmoji = getEmojiForSentiment(titleObj.sentiment || 'Neutral');
                var keywords = Array.isArray(titleObj.keywords) ? titleObj.keywords : [];
                var keywordDensity = calculateKeywordDensity(titleText, keywords);
                var readabilityScore = calculateReadabilityScore(titleText);
                var overallScore = calculateOverallScore(seoData.score, titleObj.sentiment || 'Neutral', keywordDensity, readabilityScore);

                var keywordDensityPct = (keywordDensity * 100).toFixed(2) + '%';
                var readabilityFormatted = readabilityScore.toFixed(2);
                var overallScoreFormatted = overallScore.toFixed(2);
                var keywordsList = keywords.length ? keywords.join(', ') : 'None';

                allKeywords = [...new Set([...allKeywords, ...keywords])]; // Remove duplicates
                if (overallScore > bestScore) bestScore = overallScore;

                var titleRow = $('<tr></tr>');
                titleRow.append(
                    $('<td></td>').append(
                        $('<a href="#">').text(titleText).click(function(e) {
                            e.preventDefault();
                            setTitleInEditor(titleText);
                        })
                    ),
                    $('<td></td>').text(charCount),
                    $('<td></td>').text(seoData.dot + ' ' + seoData.label),
                    $('<td></td>').html(`<span style="font-size: 24px;">${sentimentEmoji}</span>`), // Bigger emoji
                    $('<td></td>').text(keywordDensityPct),
                    $('<td></td>').text(keywordsList),
                    $('<td></td>').text(readabilityFormatted),
                    $('<td></td>').text(overallScoreFormatted)
                );
                rows.push({ row: titleRow, score: overallScore });
            });

            // Append rows and highlight the best score
            rows.forEach(function(rowData) {
                if (rowData.score === bestScore) {
                    rowData.row.css('background-color', '#f0f0f0'); // Light gray highlight
                }
                tableBody.append(rowData.row);
            });

            // Add table to container
            $('#occ_titles_table_container').append(titlesTable);

            // Add keywords summary and revert button under the table
            var keywordsSummary = allKeywords.length ? allKeywords.join(', ') : 'None';
            $('#occ_titles_table_container').append(`
                <div id="occ_titles_footer" style="margin-top: 10px;">
                    <p><strong>Keywords Used:</strong> ${keywordsSummary}</p>
                    <button id="occ_titles_revert_button" class="button" style="margin-top: 5px;">
                        <span class="dashicons dashicons-undo" style="margin-right: 5px; vertical-align: middle;"></span> Revert To Original Title
                    </button>
                </div>
            `);
        }

        // Title Tips & Spinner Setup with Randomization
        const titleTips = [
            "Keep your title concise but descriptive.",
            "Use numbers to create structure, e.g., '5 Ways to...'.",
            "Incorporate power words like 'amazing', 'effective', or 'ultimate'.",
            "Use questions to spark curiosity.",
            "Focus on benefits and what the reader will learn.",
            "Include keywords for better SEO and searchability.",
            "Create a sense of urgency or importance.",
            "Use action-oriented language to encourage engagement.",
            "Highlight a problem and promise a solution.",
            "Make use of 'How-To' titles for instructional content.",
            "Keep your audience in mind—what do they want to know?",
            "Try using comparisons or contrasts, like 'This vs. That'.",
            "Use storytelling elements to connect emotionally.",
            "Avoid clickbait—be honest and accurate in your titles.",
            "Try adding a surprising element to pique interest.",
            "Match your title style to the content type (news, opinion, etc.).",
            "Leverage trends and current events when appropriate.",
            "Experiment with different lengths and word choices.",
            "Focus on clarity—what is the main takeaway for the reader?",
            "Ask yourself, 'Would I click on this title?'"
        ];

        let tipInterval;

        function getRandomTip() {
            return titleTips[Math.floor(Math.random() * titleTips.length)];
        }

        $('body').append(`
            <div id="occ_titles_spinner_wrapper" class="occ-spinner-wrapper">
                <div id="occ_titles_spinner" class="occ-spinner"></div>
                <div id="occ_titles_spinner_text" class="occ-spinner-text">Generating Titles...</div>
            </div>
        `);

        function startDisplayingTips() {
            $('#occ_titles_spinner_text').html(getRandomTip());
            tipInterval = setInterval(function() {
                $('#occ_titles_spinner_text').fadeOut(function() {
                    $(this).html(getRandomTip()).fadeIn();
                });
            }, 4000);
        }

        function stopDisplayingTips() {
            clearInterval(tipInterval);
        }

        // Utility Functions
        function getSvgImage() {
            return `<img src="${occ_titles_admin_vars.svg_url}" alt="Generate Titles" />`;
        }

        // Classic Editor Setup
        function addClassicEditorElements() {
            var $titleInput = $('#title');
            if ($titleInput.length) {
                $titleInput.css('position', 'relative');
                $titleInput.after('<button id="occ_titles_generate_button" class="button" type="button" title="Generate Titles">' + getSvgImage() + '</button>');
                $('#occ_titles_generate_button').css({
                    position: 'absolute',
                    right: '0px',
                    top: '3px',
                    background: 'transparent',
                    border: 'none',
                    cursor: 'pointer'
                });
                $('#titlediv').after('<div id="occ_titles_table_container" style="margin-top: 20px;"></div>');
            }
        }

        // Block Editor Setup
        function addBlockEditorElements() {
            var observer = new MutationObserver(function(mutations) {
                var $blockTitle = $('h1.wp-block-post-title');
                if ($blockTitle.length && $('#occ_titles_svg_button').length === 0) {
                    var svgButton = '<button id="occ_titles_svg_button" title="Generate Titles">' + getSvgImage() + '</button>';
                    $blockTitle.parent().css('position', 'relative');
                    $(svgButton).insertAfter($blockTitle);
                    $blockTitle.closest('.wp-block-post-title').after('<div id="occ_titles_table_container" style="margin-top: 20px;"></div>');
                    $('#occ_titles_table_container').after(`
                        <div id="occ_titles_controls_container" style="margin-top: 20px;">
                            <div id="occ_titles--controls-wrapper" style="margin-bottom: 20px; display: none;">
                                <label for="occ_titles_style" style="margin-right: 10px;" class="occ_titles_style_label">Select Style:</label>
                                <select id="occ_titles_style" name="occ_titles_style" class="occ_titles_style_dropdown">
                                    <option value="" disabled selected>Choose a Style...</option>
                                    ${['how-to', 'listicle', 'question', 'command', 'intriguing statement', 'news headline', 'comparison', 'benefit-oriented', 'storytelling', 'problem-solution']
                                        .map(style => `<option value="${style}">${style.charAt(0).toUpperCase() + style.slice(1)}</option>`).join('')}
                                </select>
                                <button id="occ_titles_button" class="button button-primary">Generate Titles</button>
                            </div>
                        </div>
                    `);
                    observer.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }

        if (window.editorMode.isClassicEditor) {
            addClassicEditorElements();
        } else if (window.editorMode.isBlockEditor) {
            addBlockEditorElements();
        }

        // AJAX Request to Generate Titles
        function sendAjaxRequest(content, style, nonce) {
            $.ajax({
                url: occ_titles_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'occ_titles_generate_titles',
                    content: content,
                    style: style,
                    nonce: occ_titles_admin_vars.occ_titles_ajax_nonce
                },
                success: function(response) {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        var titles = response.data.titles;
                        if (typeof titles === 'string') {
                            titles = [{ text: titles }];
                        } else if (Array.isArray(titles) && typeof titles[0] === 'string') {
                            titles = titles.map(function(t) { return { text: t }; });
                        }
                        displayTitles(titles);
                        $('#occ_titles_spinner_wrapper').fadeOut();
                        stopDisplayingTips();
                        if (titles.length > 0) {
                            $('#occ_titles--controls-wrapper').show();
                            $('#occ_titles_spinner_wrapper, #occ_titles_svg_button').fadeOut();
                        }
                    } else {
                        displayCustomError(response.data.message || "An unknown error occurred.");
                    }
                },
                error: function() {
                    displayCustomError('We encountered an issue connecting to the server. Please check your API key and try again.');
                }
            });
        }

        function displayCustomError(errorMessage) {
            stopDisplayingTips();
            $('#occ_titles_spinner_text')
                .hide()
                .removeClass('occ-spinner-text')
                .addClass('occ-error-text')
                .html(errorMessage)
                .fadeIn();
            setTimeout(() => {
                $('#occ_titles_spinner_wrapper').fadeOut();
            }, 5000);
        }

        // Event Listeners & DOM Manipulation
        var hasGenerated = false;
        var originalTitle = '';
        var isProcessing = false;

        $('#occ_titles_button').html('Generate Titles');

        $('#occ_titles_style').on('change', function() {
            updateButtonText();
        });

        function updateButtonText() {
            var selectedStyle = $('#occ_titles_style').val();
            var styleText = $('#occ_titles_style option:selected').text();
            var buttonText = hasGenerated && selectedStyle ?
                'Generate 5 More ' + styleText + ' Titles' :
                'Generate Titles';
            $('#occ_titles_button').html(buttonText);
        }

        $(document).on('click', '#occ_titles_generate_button, #occ_titles_button, #occ_titles_svg_button', function(e) {
            e.preventDefault();
            if (isProcessing) return;
            isProcessing = true;
            console.log('Generate button clicked');
            startDisplayingTips();
            $('#occ_titles_spinner_wrapper').fadeIn();
            hasGenerated = true;
            updateButtonText();
            if (hasGenerated) {
                $('.occ_titles_style_label, .occ_titles_style_dropdown').show();
            }
            originalTitle = window.editorMode.isBlockEditor ?
                wp.data.select('core/editor').getEditedPostAttribute('title') :
                $('input#title').val();
            var content = window.editorMode.isBlockEditor ?
                wp.data.select('core/editor').getEditedPostContent() :
                $('textarea#content').val();
            var style = $('#occ_titles_style').val() || '';
            var nonce = occ_titles_admin_vars.occ_titles_ajax_nonce;
            sendAjaxRequest(content, style, nonce);
            isProcessing = false;
        });

        $(document).on('click', '#occ_titles_revert_button', function(e) {
            e.preventDefault();
            setTitleInEditor(originalTitle);
        });

        // Expose functions globally
        window.calculateSEOGrade = calculateSEOGrade;
        window.getEmojiForSentiment = getEmojiForSentiment;
        window.calculateKeywordDensity = calculateKeywordDensity;
        window.calculateReadabilityScore = calculateReadabilityScore;
        window.calculateOverallScore = calculateOverallScore;
        window.setTitleInEditor = setTitleInEditor;
        window.displayTitles = displayTitles;
        window.sendAjaxRequest = sendAjaxRequest;
    });
})(jQuery);