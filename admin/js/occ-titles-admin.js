(function( $ ) {
    'use strict';

    /**
     * Prevent multiple executions of the script.
     */
    if ( window.occTitlesInitialized ) {
        return;
    }
    window.occTitlesInitialized = true;

    $( document ).ready( function() {
        /**
         * State variables.
         */
        var hasGenerated  = false;
        var originalTitle = '';
        var isProcessing  = false;
        var tip_interval; // Fixed: Declared here for global scope within ready()

        /**
         * Detects whether the Classic or Block Editor is in use.
         *
         * @return {Object} Editor mode flags.
         */
        function check_editor_mode() {
            var is_classic_editor = document.querySelector( '.wp-editor-area' ) !== null;
            var is_block_editor   = ! is_classic_editor;
            return { is_classic_editor: is_classic_editor, is_block_editor: is_block_editor };
        }
        window.checkEditorMode = check_editor_mode;
        window.editorMode      = check_editor_mode();

        /**
         * Calculates the SEO grade based on character count.
         *
         * @param {number} char_count Character count of the title.
         * @return {Object} SEO grade data.
         */
        function calculate_seo_grade( char_count ) {
            var grade = '',
                score = 0,
                label = '';

            if ( char_count >= 50 && char_count <= 60 ) {
                grade = '🟢';
                score = 100;
                label = 'Excellent (50-60 characters)';
            } else if ( char_count < 50 ) {
                grade = '🟡';
                score = 75;
                label = 'Average (below 50 characters)';
            } else {
                grade = '🔴';
                score = 50;
                label = 'Poor (above 60 characters)';
            }

            return { dot: grade, score: score, label: label };
        }

        /**
         * Returns an emoji based on sentiment.
         *
         * @param {string} sentiment Sentiment type (Positive, Negative, Neutral).
         * @return {string} Emoji representing sentiment.
         */
        function get_emoji_for_sentiment( sentiment ) {
            switch ( sentiment ) {
                case 'Positive':
                    return '😊';
                case 'Negative':
                    return '😟';
                case 'Neutral':
                    return '😐';
                default:
                    return '❓';
            }
        }

        /**
         * Calculates keyword density in the title.
         *
         * @param {string} text Title text.
         * @param {Array}  keywords List of keywords.
         * @return {number} Keyword density ratio.
         */
        function calculate_keyword_density( text, keywords ) {
            if ( ! keywords || ! keywords.length ) {
                return 0;
            }

            var keyword_count = keywords.reduce( function( count, keyword ) {
                return count + ( text.match( new RegExp( keyword, 'gi' ) ) || [] ).length;
            }, 0 );
            var word_count = text.split( ' ' ).length;
            return keyword_count / word_count;
        }

        /**
         * Calculates readability score of the title.
         *
         * @param {string} text Title text.
         * @return {number} Readability score.
         */
        function calculate_readability_score( text ) {
            var word_count     = text.split( ' ' ).length;
            var sentence_count = text.split( /[.!?]+/ ).filter( function( sentence ) {
                return sentence.trim().length > 0;
            } ).length || 1;
            var syllable_count = text.split( /[aeiouy]+/ ).length - 1;
            return ( ( word_count / sentence_count ) + ( syllable_count / word_count ) ) * 0.4;
        }

        /**
         * Calculates an overall score for the title.
         *
         * @param {number} seo_score SEO score.
         * @param {string} sentiment Sentiment type.
         * @param {number} keyword_density Keyword density ratio.
         * @param {number} readability_score Readability score.
         * @return {number} Overall score.
         */
        function calculate_overall_score( seo_score, sentiment, keyword_density, readability_score ) {
            var sentiment_score           = sentiment === 'Positive' ? 100 : ( sentiment === 'Neutral' ? 75 : 50 );
            var keyword_density_score     = keyword_density >= 0.01 && keyword_density <= 0.03 ? 100 : 50;
            var readability_score_normalized = 100 - Math.abs( readability_score - 10 ) * 10;
            return ( seo_score + sentiment_score + keyword_density_score + readability_score_normalized ) / 4;
        }

        /**
         * Sets the title in the editor (Classic or Block).
         *
         * @param {string} title Title text to set.
         */
        function set_title_in_editor( title ) {
            if ( window.editorMode.is_block_editor ) {
                wp.data.dispatch( 'core/editor' ).editPost( { title: title } );
            } else if ( $( 'input#title' ).length ) {
                var $title_input = $( 'input#title' );
                $( '#title-prompt-text' ).empty();
                $title_input.val( title ).focus().blur();
            }
        }

        /**
         * Displays the titles table and footer controls.
         *
         * @param {Array} titles List of title objects.
         */
        function display_titles( titles ) {
            $( '#occ_titles_table_container' ).empty();
            var $titles_table = $( [
                '<table id="occ_titles_table" class="widefat fixed" cellspacing="0" style="width: 100%;">',
                    '<thead>',
                        '<tr>',
                            '<th>Title</th>',
                            '<th>Character Count</th>',
                            '<th>SEO Grade</th>',
                            '<th>Sentiment</th>',
                            '<th>Keyword Density</th>',
                            '<th>Keywords Used</th>',
                            '<th>Readability</th>',
                            '<th>Overall Score</th>',
                        '</tr>',
                    '</thead>',
                    '<tbody></tbody>',
                '</table>'
            ].join( '' ) );
            var $table_body = $titles_table.find( 'tbody' );

            var all_keywords = [];
            var best_score   = -Infinity;
            var rows         = [];

            titles.forEach( function( title_obj ) {
                var title_text         = title_obj.text || title_obj;
                var char_count         = title_text.length;
                var seo_data           = calculate_seo_grade( char_count );
                var sentiment_emoji    = get_emoji_for_sentiment( title_obj.sentiment || 'Neutral' );
                var keywords           = Array.isArray( title_obj.keywords ) ? title_obj.keywords : [];
                var keyword_density    = calculate_keyword_density( title_text, keywords );
                var readability_score  = calculate_readability_score( title_text );
                var overall_score      = calculate_overall_score( seo_data.score, title_obj.sentiment || 'Neutral', keyword_density, readability_score );

                var keyword_density_pct = ( keyword_density * 100 ).toFixed( 2 ) + '%';
                var readability_formatted = readability_score.toFixed( 2 );
                var overall_score_formatted = overall_score.toFixed( 2 );
                var keywords_list       = keywords.length ? keywords.join( ', ' ) : 'None';

                all_keywords = [ ...new Set( [ ...all_keywords, ...keywords ] ) ];
                if ( overall_score > best_score ) {
                    best_score = overall_score;
                }

                var $title_row = $( '<tr></tr>' );
                $title_row.append(
                    $( '<td></td>' ).append(
                        $( '<a href="#"></a>' ).text( title_text ).click( function( e ) {
                            e.preventDefault();
                            set_title_in_editor( title_text );
                        } )
                    ),
                    $( '<td></td>' ).text( char_count ),
                    $( '<td></td>' ).text( seo_data.dot + ' ' + seo_data.label ),
                    $( '<td></td>' ).html( '<span style="font-size: 24px;">' + sentiment_emoji + '</span>' ),
                    $( '<td></td>' ).text( keyword_density_pct ),
                    $( '<td></td>' ).text( keywords_list ),
                    $( '<td></td>' ).text( readability_formatted ),
                    $( '<td></td>' ).text( overall_score_formatted )
                );
                rows.push( { row: $title_row, score: overall_score } );
            } );

            rows.forEach( function( row_data ) {
                if ( row_data.score === best_score ) {
                    row_data.row.css( 'background-color', '#f0f0f0' );
                }
                $table_body.append( row_data.row );
            } );

            $( '#occ_titles_table_container' ).append( $titles_table );

            var keywords_summary = all_keywords.length ? all_keywords.join( ', ' ) : 'None';
            var styles_options   = [
                'how-to', 'listicle', 'question', 'command', 'intriguing statement',
                'news headline', 'comparison', 'benefit-oriented', 'storytelling', 'problem-solution'
            ].map( function( style ) {
                return '<option value="' + style + '">' + style.charAt( 0 ).toUpperCase() + style.slice( 1 ) + '</option>';
            } ).join( '' );

            $( '#occ_titles_table_container' ).append( [
                '<div id="occ_titles_footer" style="margin-top: 10px;">',
                    '<p><strong>Keywords Used:</strong> ' + keywords_summary + '</p>',
                    '<button id="occ_titles_revert_button" class="button" style="margin-top: 5px;">',
                        '<span class="dashicons dashicons-undo" style="margin-right: 5px; vertical-align: middle;"></span> Revert To Original Title',
                    '</button>',
                    '<div id="occ_titles_more_controls" style="margin-top: 10px; display: ' + ( hasGenerated ? 'block' : 'none' ) + ';">',
                        '<label for="occ_titles_style" style="margin-right: 10px;">Select Style:</label>',
                        '<select id="occ_titles_style" name="occ_titles_style" class="occ_titles_style_dropdown">',
                            '<option value="" disabled selected>Choose a Style...</option>',
                            styles_options,
                        '</select>',
                        '<button id="occ_titles_generate_more_button" class="button" style="margin-left: 10px;">Generate 5 More Titles</button>',
                    '</div>',
                '</div>'
            ].join( '' ) );
        }

        /**
         * Title tips array for spinner display.
         *
         * @type {Array}
         */
        var title_tips = [
            'Keep your title concise but descriptive.',
            'Use numbers to create structure, e.g., \'5 Ways to...\'.',
            'Incorporate power words like \'amazing\', \'effective\', or \'ultimate\'.',
            'Use questions to spark curiosity.',
            'Focus on benefits and what the reader will learn.',
            'Include keywords for better SEO and searchability.',
            'Create a sense of urgency or importance.',
            'Use action-oriented language to encourage engagement.',
            'Highlight a problem and promise a solution.',
            'Make use of \'How-To\' titles for instructional content.',
            'Keep your audience in mind—what do they want to know?',
            'Try using comparisons or contrasts, like \'This vs. That\'.',
            'Use storytelling elements to connect emotionally.',
            'Avoid clickbait—be honest and accurate in your titles.',
            'Try adding a surprising element to pique interest.',
            'Match your title style to the content type (news, opinion, etc.).',
            'Leverage trends and current events when appropriate.',
            'Experiment with different lengths and word choices.',
            'Focus on clarity—what is the main takeaway for the reader?',
            'Ask yourself, \'Would I click on this title?\''
        ];

        /**
         * Gets a random tip from the title_tips array.
         *
         * @return {string} Random tip.
         */
        function get_random_tip() {
            return title_tips[ Math.floor( Math.random() * title_tips.length ) ];
        }

        /**
         * Initializes the spinner and tips display.
         */
        $( 'body' ).append( [
            '<div id="occ_titles_spinner_wrapper" class="occ-spinner-wrapper">',
                '<div id="occ_titles_spinner" class="occ-spinner"></div>',
                '<div id="occ_titles_spinner_text" class="occ-spinner-text">Generating Titles...</div>',
            '</div>'
        ].join( '' ) );

        /**
         * Starts displaying random tips in the spinner.
         */
        function start_displaying_tips() {
            $( '#occ_titles_spinner_text' ).html( get_random_tip() );
            tip_interval = setInterval( function() {
                $( '#occ_titles_spinner_text' ).fadeOut( function() {
                    $( this ).html( get_random_tip() ).fadeIn();
                } );
            }, 4000 );
        }

        /**
         * Stops the tips interval.
         */
        function stop_displaying_tips() {
            clearInterval( tip_interval );
        }

        /**
         * Returns the SVG image markup for the generate button.
         *
         * @return {string} SVG image HTML.
         */
        function get_svg_image() {
            return '<img src="' + occ_titles_admin_vars.svg_url + '" alt="Generate Titles" />';
        }

        /**
         * Adds elements for the Classic Editor.
         */
        function add_classic_editor_elements() {
            var $title_input = $( '#title' );
            if ( $title_input.length ) {
                $title_input.css( 'position', 'relative' );
                $title_input.after( '<button id="occ_titles_generate_button" class="button" type="button" title="Generate Titles">' + get_svg_image() + '</button>' );
                $( '#occ_titles_generate_button' ).css( {
                    position: 'absolute',
                    right: '0px',
                    top: '3px',
                    background: 'transparent',
                    border: 'none',
                    cursor: 'pointer'
                } );
                $( '#titlediv' ).after( '<div id="occ_titles_table_container" style="margin-top: 20px;"></div>' );
            }
        }

        /**
         * Adds elements for the Block Editor.
         */
        function add_block_editor_elements() {
            var observer = new MutationObserver( function( mutations ) {
                var $block_title = $( 'h1.wp-block-post-title' );
                if ( $block_title.length && $( '#occ_titles_svg_button' ).length === 0 ) {
                    var svg_button = '<button id="occ_titles_svg_button" title="Generate Titles">' + get_svg_image() + '</button>';
                    $block_title.parent().css( 'position', 'relative' );
                    $( svg_button ).insertAfter( $block_title );
                    $block_title.closest( '.wp-block-post-title' ).after( '<div id="occ_titles_table_container" style="margin-top: 20px;"></div>' );
                    observer.disconnect();
                }
            } );
            observer.observe( document.body, { childList: true, subtree: true } );
        }

        /**
         * Initialize editor-specific elements.
         */
        if ( window.editorMode.is_classic_editor ) {
            add_classic_editor_elements();
        } else if ( window.editorMode.is_block_editor ) {
            add_block_editor_elements();
        }

        /**
         * Sends an AJAX request to generate titles.
         *
         * @param {string} content Post content.
         * @param {string} style Title style.
         * @param {string} nonce Security nonce.
         * @return {Object} jQuery AJAX promise.
         */
        function send_ajax_request( content, style, nonce ) {
            return $.ajax( {
                url: occ_titles_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'occ_titles_generate_titles',
                    content: content,
                    style: style,
                    nonce: nonce
                },
                success: function( response ) {
                    if ( response.success ) {
                        var titles = response.data.titles;
                        if ( typeof titles === 'string' ) {
                            titles = [ { text: titles } ];
                        } else if ( Array.isArray( titles ) && typeof titles[0] === 'string' ) {
                            titles = titles.map( function( t ) { return { text: t }; } );
                        }
                        display_titles( titles );
                        $( '#occ_titles_spinner_wrapper' ).fadeOut();
                        stop_displaying_tips();
                        if ( titles.length > 0 ) {
                            $( '#occ_titles_more_controls' ).show();
                            update_generate_more_button_text();
                            if ( window.editorMode.is_block_editor ) {
                                $( '#occ_titles_svg_button' ).hide();
                            }
                        }
                    } else {
                        display_custom_error( response.data.message || 'An unknown error occurred.' );
                    }
                },
                error: function() {
                    display_custom_error( 'We encountered an issue connecting to the server. Please check your API key and try again.' );
                }
            } );
        }

        /**
         * Displays an error message in the spinner.
         *
         * @param {string} error_message Error message to display.
         */
        function display_custom_error( error_message ) {
            stop_displaying_tips();
            $( '#occ_titles_spinner_text' )
                .hide()
                .removeClass( 'occ-spinner-text' )
                .addClass( 'occ-error-text' )
                .html( error_message )
                .fadeIn();
            setTimeout( function() {
                $( '#occ_titles_spinner_wrapper' ).fadeOut();
            }, 5000 );
        }

        /**
         * Updates the "Generate 5 More" button text based on selected style.
         */
        function update_generate_more_button_text() {
            var selected_style = $( '#occ_titles_style' ).val();
            var style_text     = $( '#occ_titles_style option:selected' ).text();
            var button_text    = selected_style ? 'Generate 5 More ' + style_text + ' Titles' : 'Generate 5 More Titles';
            $( '#occ_titles_generate_more_button' ).html( button_text );
        }

        /**
         * Event listener for initial generate buttons.
         */
        $( document ).on( 'click', '#occ_titles_generate_button, #occ_titles_button, #occ_titles_svg_button', function( e ) {
            e.preventDefault();
            if ( isProcessing ) {
                return;
            }
            isProcessing = true;

            start_displaying_tips();
            $( '#occ_titles_spinner_wrapper' ).fadeIn();

            if ( ! hasGenerated ) {
                originalTitle = window.editorMode.is_block_editor ?
                    wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' ) :
                    $( 'input#title' ).val();
            }
            hasGenerated = true;

            var content = window.editorMode.is_block_editor ?
                wp.data.select( 'core/editor' ).getEditedPostContent() :
                $( 'textarea#content' ).val();
            var style = $( '#occ_titles_style' ).val() || '';
            var nonce = occ_titles_admin_vars.occ_titles_ajax_nonce;

            send_ajax_request( content, style, nonce ).always( function() {
                isProcessing = false;
            } );
        } );

        /**
         * Event listener for "Generate 5 More" button.
         */
        $( document ).on( 'click', '#occ_titles_generate_more_button', function( e ) {
            e.preventDefault();
            if ( isProcessing ) {
                return;
            }
            isProcessing = true;

            start_displaying_tips(); // Fix: Show tips on click
            $( '#occ_titles_spinner_wrapper' ).fadeIn();

            var content = window.editorMode.is_block_editor ?
                wp.data.select( 'core/editor' ).getEditedPostContent() :
                $( 'textarea#content' ).val();
            var style = $( '#occ_titles_style' ).val() || 'listicle'; // Fix: Default to 'listicle'
            var nonce = occ_titles_admin_vars.occ_titles_ajax_nonce;

            send_ajax_request( content, style, nonce ).always( function() {
                isProcessing = false;
            } );
        } );

        /**
         * Event listener for style dropdown change.
         */
        $( document ).on( 'change', '#occ_titles_style', function() {
            update_generate_more_button_text();
        } );

        /**
         * Event listener for revert button.
         */
        $( document ).on( 'click', '#occ_titles_revert_button', function( e ) {
            e.preventDefault();
            set_title_in_editor( originalTitle );
        } );

        /**
         * Expose functions globally for debugging or external use.
         */
        window.calculateSEOGrade        = calculate_seo_grade;
        window.getEmojiForSentiment     = get_emoji_for_sentiment;
        window.calculateKeywordDensity  = calculate_keyword_density;
        window.calculateReadabilityScore = calculate_readability_score;
        window.calculateOverallScore    = calculate_overall_score;
        window.setTitleInEditor         = set_title_in_editor;
        window.displayTitles            = display_titles;
        window.sendAjaxRequest          = send_ajax_request;
    } );
})( jQuery );