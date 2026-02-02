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
        var currentTitles = [];
        var lastGeneratedAt = '';
        var lastProvider = '';
        var lastAppliedTitle = '';
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
         * Get current post ID from the editor context.
         *
         * @return {number} Post ID or 0.
         */
        function get_post_id() {
            if ( window.editorMode.is_block_editor && window.wp && wp.data && wp.data.select ) {
                var editor = wp.data.select( 'core/editor' );
                if ( editor && typeof editor.getCurrentPostId === 'function' ) {
                    return parseInt( editor.getCurrentPostId(), 10 ) || 0;
                }
            }
            return parseInt( $( '#post_ID' ).val(), 10 ) || 0;
        }

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
         * Normalize titles into a consistent object format.
         *
         * @param {Array} titles Raw titles list.
         * @return {Array} Normalized titles.
         */
        function normalize_titles( titles ) {
            if ( typeof titles === 'string' ) {
                return [ { text: titles } ];
            }

            if ( Array.isArray( titles ) && typeof titles[0] === 'string' ) {
                return titles.map( function( title_text ) {
                    return { text: title_text };
                } );
            }

            return Array.isArray( titles ) ? titles : [];
        }

        /**
         * Build keyword suggestions from content.
         *
         * @param {string} content Content text.
         * @return {Array} Suggestions with score.
         */
        function build_keyword_suggestions( content ) {
            if ( ! content ) {
                return [];
            }

            var cleaned = content
                .toLowerCase()
                .replace( /<[^>]+>/g, ' ' )
                .replace( /[^a-z0-9\s]/g, ' ' );

            var words = cleaned.split( /\s+/ ).filter( function( word ) {
                return word.length > 3;
            } );

            var stop_words = [ 'this', 'that', 'with', 'from', 'your', 'have', 'will', 'about', 'into', 'their', 'there', 'what', 'which', 'when', 'where', 'then', 'than', 'them', 'they', 'were', 'been', 'also', 'because', 'while', 'after', 'before', 'between', 'under', 'over', 'more', 'less', 'very', 'just', 'like' ];
            var counts = {};

            words.forEach( function( word ) {
                if ( stop_words.indexOf( word ) !== -1 ) {
                    return;
                }
                counts[ word ] = ( counts[ word ] || 0 ) + 1;
            } );

            var suggestions = Object.keys( counts ).map( function( word ) {
                return { term: word, score: counts[ word ] };
            } );

            suggestions.sort( function( a, b ) {
                return b.score - a.score;
            } );

            return suggestions.slice( 0, 10 );
        }

        /**
         * Build a SERP preview block.
         *
         * @param {string} title Title text.
         * @return {string} HTML string.
         */
        function build_serp_preview( title ) {
            var slug = occ_titles_admin_vars.post_slug || 'sample-slug';
            var url = occ_titles_admin_vars.post_permalink || ( window.location.origin + '/' + slug );
            return '<div class="occ_titles-serp">' +
                '<div class="occ_titles-serp-url">' + url + '</div>' +
                '<div class="occ_titles-serp-title">' + title + '</div>' +
                '<div class="occ_titles-serp-desc">Preview how this title may appear in search results.</div>' +
                '</div>';
        }

        /**
         * Build a Discover card preview block.
         *
         * @param {string} title Title text.
         * @return {string} HTML string.
         */
        function build_discover_preview( title ) {
            var source = window.location.hostname || 'Example News';
            var domain_initial = source ? source.charAt( 0 ).toUpperCase() : 'G';
            return '<div class="occ_titles-discover-card">' +
                '<div class="occ_titles-discover-image">' +
                    '<span class="occ_titles-discover-image-label">Top stories</span>' +
                '</div>' +
                '<div class="occ_titles-discover-body">' +
                    '<div class="occ_titles-discover-meta">' +
                        '<span class="occ_titles-discover-favicon">' + domain_initial + '</span>' +
                        '<span class="occ_titles-discover-source">' + source + '</span>' +
                        '<span class="occ_titles-discover-dot">•</span>' +
                        '<span class="occ_titles-discover-time">3h</span>' +
                    '</div>' +
                    '<div class="occ_titles-discover-title">' + title + '</div>' +
                    '<div class="occ_titles-discover-desc">Discover is image-led and scroll-based. Use curiosity and strong entities, but keep the title clear and true.</div>' +
                '</div>' +
                '</div>';
        }

        /**
         * Build a preview block based on intent.
         *
         * @param {string} title Title text.
         * @param {string} intent Selected intent.
         * @return {string} HTML string.
         */
        function build_title_preview( title, intent ) {
            var intent_value = ( intent || '' ).toLowerCase();
            if ( intent_value.indexOf( 'discover' ) !== -1 || intent_value.indexOf( 'top stories' ) !== -1 || intent_value.indexOf( 'top story' ) !== -1 ) {
                return build_discover_preview( title );
            }

            return build_serp_preview( title );
        }

        /**
         * Refresh preview cells based on selected intent.
         */
        function refresh_previews() {
            var intent_value = $( '#occ_titles_intent' ).val() || '';
            $( '.occ_titles-row' ).each( function() {
                var $row = $( this );
                var title_text = $row.find( '.occ_titles-title-link' ).text();
                var $preview_cell = $row.find( '.occ_titles-col-serp' );
                $preview_cell.empty().append( build_title_preview( title_text, intent_value ) );
                $preview_cell.append( '<div class="occ_titles-serp-meter"><span style="width:' + $row.data( 'pixelPercent' ) + '%"></span></div>' );
                $preview_cell.append( '<div class="occ_titles-serp-meta">Target: 560 to 600 px • Current: ' + $row.data( 'pixelWidth' ) + 'px</div>' );
            } );
        }

        /**
         * Measure text width in pixels.
         *
         * @param {string} text Text content.
         * @return {number} Width in px.
         */
        function measure_text_width( text ) {
            var $ruler = $( '#occ_titles_pixel_ruler' );
            if ( ! $ruler.length ) {
                $ruler = $( '<span id="occ_titles_pixel_ruler"></span>' ).css( {
                    position: 'absolute',
                    visibility: 'hidden',
                    whiteSpace: 'nowrap',
                    fontSize: '20px',
                    fontFamily: 'Arial, sans-serif',
                    fontWeight: 600
                } );
                $( 'body' ).append( $ruler );
            }
            $ruler.text( text );
            return Math.round( $ruler.width() );
        }

        /**
         * Determine quality gate label.
         *
         * @param {Object} metrics Metrics object.
         * @return {string} Gate label.
         */
        function get_quality_gate( metrics ) {
            var passes = 0;
            if ( metrics.char_count >= 50 && metrics.char_count <= 60 ) {
                passes += 1;
            }
            if ( metrics.keyword_density >= 0.01 && metrics.keyword_density <= 0.03 ) {
                passes += 1;
            }
            if ( metrics.has_power_word ) {
                passes += 1;
            }
            if ( metrics.starts_strong ) {
                passes += 1;
            }
            return passes >= 3 ? 'Pass' : 'Needs work';
        }

        /**
         * Get selected keyword targets.
         *
         * @return {Array} Selected keywords.
         */
        function get_selected_keywords() {
            var selected = [];
            $( '.occ_titles-keyword-chip.is-selected' ).each( function() {
                selected.push( $( this ).attr( 'data-term' ) );
            } );
            return selected;
        }

        /**
         * Determine keyword fit label based on density.
         *
         * @param {number} density Keyword density ratio.
         * @return {string} Fit label.
         */
        function get_keyword_fit_label( density ) {
            if ( density >= 0.01 && density <= 0.03 ) {
                return 'High';
            }
            if ( density > 0.03 ) {
                return 'Too High';
            }
            return 'Low';
        }

        /**
         * Get title length label.
         *
         * @param {number} char_count Character count.
         * @return {string} Length label.
         */
        function get_length_label( char_count ) {
            if ( char_count >= 50 && char_count <= 60 ) {
                return 'Ideal length';
            }
            if ( char_count < 50 ) {
                return 'Short';
            }
            return 'Long';
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
         * Get the current title from the editor.
         *
         * @return {string} Current title.
         */
        function get_current_title() {
            if ( window.editorMode.is_block_editor ) {
                return wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' ) || '';
            }

            if ( $( 'input#title' ).length ) {
                return $( 'input#title' ).val() || '';
            }

            return '';
        }

        /**
         * Toggle the results panel visibility.
         *
         * @param {boolean} collapsed Whether to collapse.
         */
        function set_results_collapsed( collapsed ) {
            var $results = $( '.occ_titles-results' );
            if ( ! $results.length ) {
                return;
            }

            $results.toggleClass( 'is-collapsed', collapsed );
            localStorage.setItem( 'occ_titles_results_collapsed', collapsed ? '1' : '0' );
            $( '#occ_titles_toggle_panel' )
                .text( collapsed ? 'Show panel' : 'Hide panel' )
                .attr( 'aria-expanded', collapsed ? 'false' : 'true' );
        }

        /**
         * Apply stored collapsed state.
         */
        function apply_results_collapsed_state() {
            var stored = localStorage.getItem( 'occ_titles_results_collapsed' );
            set_results_collapsed( '1' === stored );
        }

        /**
         * Displays the titles table and footer controls.
         *
         * @param {Array} titles List of title objects.
         */
        function display_titles( titles, meta ) {
            var metadata = meta || {};
            var normalized = normalize_titles( titles );
            var $container = $( '#occ_titles_table_container' );

            currentTitles = normalized;
            lastGeneratedAt = metadata.generated_at || lastGeneratedAt;
            lastProvider = metadata.provider || lastProvider;
            if ( metadata.intent ) {
                $( '#occ_titles_intent' ).val( metadata.intent );
            }
            if ( typeof metadata.ellipsis !== 'undefined' ) {
                $( '#occ_titles_ellipsis' ).prop( 'checked', !! metadata.ellipsis );
            }

            $container.empty().addClass( 'occ_titles-results' );

            var header_subtitle = lastGeneratedAt ? 'Last generated: ' + lastGeneratedAt : 'Generate titles to see results.';
            var provider_label = lastProvider ? 'Provider: ' + lastProvider.toUpperCase() : '';

            var $header = $( '<div class="occ_titles-results-header"></div>' );
            var $header_left = $( '<div class="occ_titles-results-summary"></div>' );
            $header_left.append( '<h3 class="occ_titles-results-title">Title Recommendations</h3>' );
            $header_left.append( '<p class="occ_titles-results-meta">' + header_subtitle + '</p>' );
            if ( provider_label ) {
                $header_left.append( '<p class="occ_titles-results-provider">' + provider_label + '</p>' );
            }

            var $header_actions = $( '<div class="occ_titles-results-actions"></div>' );
            $header_actions.append( '<button type="button" class="button button-secondary" id="occ_titles_score_current">Score Current Title</button>' );
            $header_actions.append( '<button type="button" class="button button-secondary" id="occ_titles_copy_all">Copy All</button>' );
            $header_actions.append( '<button type="button" class="button button-secondary" id="occ_titles_export_csv">Download CSV</button>' );
            $header_actions.append( '<button type="button" class="button button-secondary" id="occ_titles_toggle_panel" aria-expanded="true">Hide panel</button>' );
            $header.append( $header_left, $header_actions );
            $container.append( $header );

            var $error_panel = $( '<div class="occ_titles-error-panel" style="display:none;"></div>' );
            $container.append( $error_panel );

            var styles_options = [
                'how-to', 'listicle', 'question', 'command', 'intriguing statement',
                'news headline', 'comparison', 'benefit-oriented', 'storytelling', 'problem-solution'
            ].map( function( style ) {
                return '<option value="' + style + '">' + style.charAt( 0 ).toUpperCase() + style.slice( 1 ) + '</option>';
            } ).join( '' );

            var $controls = $( '<div class="occ_titles-controls"></div>' );
            $controls.append(
                '<div class="occ_titles-control-row">' +
                    '<div class="occ_titles-control occ_titles-control-group">' +
                        '<div class="occ_titles-control-item">' +
                            '<label for="occ_titles_intent"><strong>Goal</strong></label>' +
                            '<select id="occ_titles_intent" class="occ_titles_intent">' +
                                '<option value="">Select goal</option>' +
                                '<option value="Increase CTR">Increase CTR</option>' +
                                '<option value="Rank for keyword">Rank for keyword</option>' +
                                '<option value="Discover cards">Discover cards</option>' +
                                '<option value="Social share">Social share</option>' +
                                '<option value="Thought leadership">Thought leadership</option>' +
                                '<option value="Lead gen">Lead gen</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="occ_titles-control-item">' +
                            '<label for="occ_titles_style"><strong>Style</strong></label>' +
                            '<select id="occ_titles_style" name="occ_titles_style" class="occ_titles_style_dropdown">' +
                                '<option value="" disabled selected>Choose a Style...</option>' +
                                styles_options +
                            '</select>' +
                        '</div>' +
                        '<div class="occ_titles-control-item occ_titles-control-inline">' +
                            '<label for="occ_titles_ellipsis"><strong>Curiosity ellipsis</strong></label>' +
                            '<label class="occ_titles-toggle">' +
                                '<input type="checkbox" id="occ_titles_ellipsis" />' +
                                '<span>Allow “…” endings</span>' +
                            '</label>' +
                        '</div>' +
                    '</div>' +
                    '<div class="occ_titles-control occ_titles-control-actions">' +
                        '<button type="button" class="button button-primary" id="occ_titles_generate_button_top">Generate Titles</button>' +
                        '<button type="button" class="button" id="occ_titles_revert_button_top">Revert To Original Title</button>' +
                    '</div>' +
                '</div>' +
                '<p class="occ_titles-controls-help">Step 1: choose a goal and style. Step 2: click Generate Titles.</p>' +
                '<button type="button" class="button-link occ_titles-controls-toggle" aria-expanded="true">Hide controls</button>'
            );
            $controls.append( '<div class="occ_titles-control occ_titles-keywords-panel"><label><strong>Keyword targets</strong></label><div class="occ_titles-keyword-list"></div></div>' );
            $container.append( $controls );

            apply_controls_collapsed_state();
            apply_results_collapsed_state();

            var suggestions = build_keyword_suggestions( metadata.content || window.occTitlesContentCache || '' );
            var $keyword_list = $controls.find( '.occ_titles-keyword-list' );
            suggestions.forEach( function( suggestion ) {
                var $chip = $( '<button type="button" class="occ_titles-keyword-chip"></button>' );
                $chip.attr( 'data-term', suggestion.term );
                $chip.attr( 'data-score', suggestion.score );
                $chip.text( suggestion.term + ' (' + suggestion.score + ')' );
                $keyword_list.append( $chip );
            } );

            var $titles_table = $( '<table id="occ_titles_table" class="widefat fixed occ_titles-table" cellspacing="0"></table>' );
            $titles_table.append(
                '<thead><tr>' +
                '<th class="occ_titles-col-rank">Rank</th>' +
                '<th class="occ_titles-col-title">Title & Actions</th>' +
                '<th class="occ_titles-col-score">Score</th>' +
                '<th class="occ_titles-col-insights">Insights</th>' +
                '<th class="occ_titles-col-keywords">Keywords</th>' +
                '<th class="occ_titles-col-serp">Preview</th>' +
                '</tr></thead>'
            );
            var $table_body = $( '<tbody></tbody>' );

            var all_keywords = [];
            var rows = [];

            normalized.forEach( function( title_obj, index ) {
                var title_text = title_obj.text || '';
                var char_count = title_text.length;
                var sentiment = title_obj.sentiment || 'Neutral';
                var sentiment_emoji = get_emoji_for_sentiment( sentiment );
                var keywords = Array.isArray( title_obj.keywords ) ? title_obj.keywords : [];
                var keyword_density = calculate_keyword_density( title_text, keywords );
                var readability_score = calculate_readability_score( title_text );
                var seo_data = calculate_seo_grade( char_count );
                var overall_score = calculate_overall_score( seo_data.score, sentiment, keyword_density, readability_score );
                var has_power_word = /ultimate|best|proven|simple|easy|fast|guide|tips|secrets/i.test( title_text );
                var starts_strong = /^[0-9]|how|why|what|when|the|this|your/i.test( title_text.toLowerCase() );
                var gate = get_quality_gate( {
                    char_count: char_count,
                    keyword_density: keyword_density,
                    has_power_word: has_power_word,
                    starts_strong: starts_strong
                } );
                var pixel_width = measure_text_width( title_text );
                var pixel_percent = Math.min( 100, Math.round( ( pixel_width / 600 ) * 100 ) );

                var row_data = {
                    index: index,
                    title: title_text,
                    sentiment: sentiment,
                    sentiment_emoji: sentiment_emoji,
                    keywords: keywords,
                    keyword_density: keyword_density,
                    readability: readability_score,
                    seo: seo_data,
                    overall_score: overall_score,
                    style: title_obj.style || metadata.style || '',
                    gate: gate,
                    pixel_width: pixel_width,
                    pixel_percent: pixel_percent,
                    is_current: !! title_obj.is_current
                };

                all_keywords = [ ...new Set( [ ...all_keywords, ...keywords ] ) ];
                rows.push( row_data );
            } );

            rows.sort( function( a, b ) {
                return b.overall_score - a.overall_score;
            } );

            rows.forEach( function( row_data, rank_index ) {
                var keyword_density_pct = ( row_data.keyword_density * 100 ).toFixed( 2 ) + '%';
                var readability_formatted = row_data.readability.toFixed( 2 );
                var overall_score_formatted = Math.round( row_data.overall_score );
                var keywords_list = row_data.keywords.length ? row_data.keywords.join( ', ' ) : 'None';
                var keyword_fit = get_keyword_fit_label( row_data.keyword_density );
                var length_label = get_length_label( row_data.title.length );
                var is_best = 0 === rank_index;

                var $row = $( '<tr class="occ_titles-row"></tr>' );
                $row.attr( 'data-index', row_data.index );
                $row.data( 'pixelPercent', row_data.pixel_percent );
                $row.data( 'pixelWidth', row_data.pixel_width );
                $row.toggleClass( 'is-best', is_best );

                var $rank_cell = $( '<td class="occ_titles-col-rank"></td>' );
                $rank_cell.append( '<span class="occ_titles-rank">#' + ( rank_index + 1 ) + '</span>' );
                if ( is_best ) {
                    $rank_cell.append( '<span class="occ_titles-best-badge">Best</span>' );
                }
                if ( row_data.is_current ) {
                    $rank_cell.append( '<span class="occ_titles-current-badge">Current</span>' );
                }

                var $title_cell = $( '<td class="occ_titles-col-title"></td>' );
                var $title_text = $( '<button type="button" class="occ_titles-title-link"></button>' ).text( row_data.title );
                $title_text.on( 'click', function() {
                    apply_title_from_row( row_data );
                } );

                var $actions = $( '<div class="occ_titles-title-actions"></div>' );
                if ( row_data.is_current ) {
                    $actions.append( '<span class="occ_titles-applied-label is-visible" aria-hidden="true">This is your current title</span>' );
                } else {
                    $actions.append( '<button type="button" class="button button-primary occ_titles-apply" data-index="' + row_data.index + '">Apply</button>' );
                    $actions.append( '<button type="button" class="button occ_titles-undo" data-index="' + row_data.index + '">Undo</button>' );
                    $actions.append( '<span class="occ_titles-applied-label" aria-hidden="true">Applied</span>' );
                }

                var $iterate = $( '<div class="occ_titles-iterate"></div>' );
                $iterate.append( '<button type="button" class="button-link occ_titles-iterate-btn" data-variation="shorter" data-index="' + row_data.index + '">Shorter</button>' );
                $iterate.append( '<button type="button" class="button-link occ_titles-iterate-btn" data-variation="punchier" data-index="' + row_data.index + '">Punchier</button>' );
                $iterate.append( '<button type="button" class="button-link occ_titles-iterate-btn" data-variation="benefit" data-index="' + row_data.index + '">More benefit</button>' );
                $iterate.append( '<button type="button" class="button-link occ_titles-iterate-btn" data-variation="keyword" data-index="' + row_data.index + '">Add keyword</button>' );

                $title_cell.append( $title_text, $actions, $iterate );

                var $score_cell = $( '<td class="occ_titles-col-score"></td>' );
                var $meter = $( '<div class="occ_titles-score-meter"><span></span></div>' );
                $meter.find( 'span' ).css( 'width', Math.min( 100, overall_score_formatted ) + '%' );
                $score_cell.append( $meter );
                $score_cell.append( '<div class="occ_titles-score-value">' + overall_score_formatted + '</div>' );

                var $insights_cell = $( '<td class="occ_titles-col-insights"></td>' );
                var $chips = $( '<div class="occ_titles-chips"></div>' );
                $chips.append( '<span class="occ_titles-chip occ_titles-gate ' + ( row_data.gate === 'Pass' ? 'is-pass' : 'is-warning' ) + '">' + row_data.gate + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Sentiment: ' + row_data.sentiment + ' ' + row_data.sentiment_emoji + '</span>' );
                if ( row_data.style ) {
                    $chips.append( '<span class="occ_titles-chip">Style: ' + row_data.style + '</span>' );
                }
                $chips.append( '<span class="occ_titles-chip">Length: ' + length_label + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Keyword fit: ' + keyword_fit + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Readability: ' + readability_formatted + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Density: ' + keyword_density_pct + '</span>' );
                $insights_cell.append( $chips );

                var $keywords_cell = $( '<td class="occ_titles-col-keywords"></td>' ).text( keywords_list );
                var preview_intent = $( '#occ_titles_intent' ).val() || metadata.intent || '';
                var $serp_cell = $( '<td class="occ_titles-col-serp"></td>' );
                $serp_cell.append( build_title_preview( row_data.title, preview_intent ) );
                $serp_cell.append( '<div class="occ_titles-serp-meter"><span style="width:' + row_data.pixel_percent + '%"></span></div>' );
                $serp_cell.append( '<div class="occ_titles-serp-meta">Target: 560 to 600 px • Current: ' + row_data.pixel_width + 'px</div>' );

                $row.append( $rank_cell, $title_cell, $score_cell, $insights_cell, $keywords_cell, $serp_cell );
                $table_body.append( $row );
            } );

            $titles_table.append( $table_body );
            $container.append( $titles_table );

            var keywords_summary = all_keywords.length ? all_keywords.join( ', ' ) : 'None';
            var $guidance = $( '<div class="occ_titles-guidance"></div>' );
            $guidance.append(
                '<div class="occ_titles-guidance-card">' +
                    '<strong>How to pick:</strong> choose a title with a strong score, clean readability, and the right keywords. Click Apply when ready.' +
                '</div>' +
                '<div class="occ_titles-guidance-card">' +
                    '<strong>Pixel target:</strong> 560 to 600 px. Google trims headlines by pixel width, not character count.' +
                '</div>' +
                '<div class="occ_titles-guidance-card"><strong>Keywords used:</strong> ' + keywords_summary + '</div>'
            );
            $container.find( '.occ_titles-guidance' ).remove();
            $container.find( '.occ_titles-controls' ).after( $guidance );

            if ( ! metadata.from_cache ) {
                persist_results( {
                    titles: normalized,
                    generated_at: lastGeneratedAt,
                    provider: lastProvider,
                    style: metadata.style || '',
                    intent: $( '#occ_titles_intent' ).val() || '',
                    ellipsis: $( '#occ_titles_ellipsis' ).is( ':checked' ) ? 1 : 0,
                    keywords: get_selected_keywords()
                } );
            }
        }

        /**
         * Apply a title from a row and update UI state.
         *
         * @param {Object} row_data Row data.
         */
        function apply_title_from_row( row_data ) {
            var title_text = row_data && ( row_data.text || row_data.title );
            if ( ! title_text ) {
                return;
            }
            lastAppliedTitle = title_text;
            set_title_in_editor( title_text );
            $( '.occ_titles-row' ).removeClass( 'is-applied' );
            var $row = $( '.occ_titles-row[data-index="' + row_data.index + '"]' );
            $row.addClass( 'is-applied' );
        }

        /**
         * Persist results to post meta.
         *
         * @param {Object} payload Results payload.
         */
        function persist_results( payload ) {
            var post_id = get_post_id();
            if ( ! post_id ) {
                return;
            }

            $.ajax( {
                url: occ_titles_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'occ_titles_save_results',
                    nonce: occ_titles_admin_vars.occ_titles_ajax_nonce,
                    post_id: post_id,
                    results: JSON.stringify( payload )
                }
            } );
        }

        /**
         * Save a voice sample for future generations.
         *
         * @param {string} title Title text.
         */
        function save_voice_sample( title ) {
            if ( ! title ) {
                return;
            }

            $.ajax( {
                url: occ_titles_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'occ_titles_save_voice_sample',
                    nonce: occ_titles_admin_vars.occ_titles_ajax_nonce,
                    title: title
                }
            } );
        }

        /**
         * Load saved results from post meta.
         */
        function load_saved_results( attempt ) {
            var tries = attempt || 0;
            var post_id = get_post_id();

            if ( ! post_id || ! $( '#occ_titles_table_container' ).length ) {
                if ( tries < 10 ) {
                    setTimeout( function() {
                        load_saved_results( tries + 1 );
                    }, 500 );
                }
                return;
            }

            $.ajax( {
                url: occ_titles_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'occ_titles_get_results',
                    nonce: occ_titles_admin_vars.occ_titles_ajax_nonce,
                    post_id: post_id
                }
            } )
                .done( function( response ) {
                    if ( response.success && response.data && response.data.results && response.data.results.titles ) {
                        if ( ! originalTitle ) {
                            originalTitle = window.editorMode.is_block_editor ?
                                wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' ) :
                                $( 'input#title' ).val();
                        }
                        hasGenerated = true;
                        display_titles( response.data.results.titles, {
                            generated_at: response.data.results.generated_at,
                            provider: response.data.results.provider,
                            style: response.data.results.style,
                            from_cache: true
                        } );
                    }
                } );
        }

        /**
         * Show a recoverable error panel.
         *
         * @param {string} message Error message.
         */
        function show_error_panel( message ) {
            var $panel = $( '.occ_titles-error-panel' );
            if ( ! $panel.length ) {
                return;
            }

            var settings_url = occ_titles_admin_vars.settings_url || '';
            var cta = settings_url ? '<a class="button button-secondary" href="' + settings_url + '">Open settings</a>' : '';

            $panel.html(
                '<div class="occ_titles-error-content">' +
                    '<strong>Generation issue:</strong> ' + message +
                '</div>' +
                '<div class="occ_titles-error-actions">' + cta + '</div>'
            );
            $panel.fadeIn();
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
                    load_saved_results();
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

        load_saved_results();

        /**
         * Sends an AJAX request to generate titles.
         *
         * @param {string} content Post content.
         * @param {string} style Title style.
         * @param {string} nonce Security nonce.
         * @return {Object} jQuery AJAX promise.
         */
        function send_ajax_request( payload, callback ) {
            return $.ajax( {
                url: occ_titles_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: $.extend( {
                    action: 'occ_titles_generate_titles',
                    nonce: occ_titles_admin_vars.occ_titles_ajax_nonce
                }, payload )
            } )
                .done( function( response ) {
                    if ( typeof callback === 'function' ) {
                        callback( response );
                        return;
                    }

                    if ( response.success ) {
                        var titles = normalize_titles( response.data.titles );
                        display_titles( titles, {
                            generated_at: response.data.generated_at,
                            provider: response.data.provider,
                            style: payload.style || ''
                        } );
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
                } )
                .fail( function() {
                    display_custom_error( 'We encountered an issue connecting to the server. Please check your API key and try again.' );
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
            show_error_panel( error_message );
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
        $( document ).on( 'click', '#occ_titles_generate_button, #occ_titles_button, #occ_titles_svg_button, #occ_titles_generate_button_top', function( e ) {
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
            window.occTitlesContentCache = content;
            var style = $( '#occ_titles_style' ).val() || '';
            var intent = $( '#occ_titles_intent' ).val() || '';
            var ellipsis = $( '#occ_titles_ellipsis' ).is( ':checked' ) ? 1 : 0;

            send_ajax_request( {
                content: content,
                style: style,
                intent: intent,
                ellipsis: ellipsis,
                keywords: get_selected_keywords()
            } ).always( function() {
                isProcessing = false;
            } );
        } );

        /**
         * Toggle keyword selection for targeting.
         */
        $( document ).on( 'click', '.occ_titles-keyword-chip', function( e ) {
            e.preventDefault();
            $( this ).toggleClass( 'is-selected' );
        } );

        /**
         * Apply saved controls collapsed state.
         */
        function apply_controls_collapsed_state() {
            if ( ! $( '.occ_titles-controls' ).length ) {
                return;
            }
            var stored = localStorage.getItem( 'occ_titles_controls_collapsed' );
            if ( stored === '1' ) {
                $( '.occ_titles-controls' ).addClass( 'is-collapsed' );
                $( '.occ_titles-controls-toggle' ).attr( 'aria-expanded', 'false' ).text( 'Show controls' );
            }
        }

        /**
         * Toggle controls visibility and persist state.
         */
        $( document ).on( 'click', '.occ_titles-controls-toggle', function( e ) {
            e.preventDefault();
            var $controls = $( '.occ_titles-controls' );
            $controls.toggleClass( 'is-collapsed' );
            var collapsed = $controls.hasClass( 'is-collapsed' );
            localStorage.setItem( 'occ_titles_controls_collapsed', collapsed ? '1' : '0' );
            $( this )
                .attr( 'aria-expanded', collapsed ? 'false' : 'true' )
                .text( collapsed ? 'Show controls' : 'Hide controls' );
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
            window.occTitlesContentCache = content;
            var style = $( '#occ_titles_style' ).val() || 'listicle'; // Fix: Default to 'listicle'
            var intent = $( '#occ_titles_intent' ).val() || '';
            var ellipsis = $( '#occ_titles_ellipsis' ).is( ':checked' ) ? 1 : 0;

            send_ajax_request( {
                content: content,
                style: style,
                intent: intent,
                ellipsis: ellipsis,
                keywords: get_selected_keywords()
            } ).always( function() {
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
         * Event listener for intent dropdown change.
         */
        $( document ).on( 'change', '#occ_titles_intent', function() {
            refresh_previews();
        } );

        /**
         * Event listener for revert button.
         */
        $( document ).on( 'click', '#occ_titles_revert_button, #occ_titles_revert_button_top', function( e ) {
            e.preventDefault();
            set_title_in_editor( originalTitle );
        } );

        /**
         * Apply a recommended title.
         */
        $( document ).on( 'click', '.occ_titles-apply', function( e ) {
            e.preventDefault();
            var index = parseInt( $( this ).attr( 'data-index' ), 10 );
            var row_data = currentTitles[ index ];
            if ( ! originalTitle ) {
                originalTitle = window.editorMode.is_block_editor ?
                    wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' ) :
                    $( 'input#title' ).val();
            }
            if ( row_data ) {
                row_data.index = index;
            }
            apply_title_from_row( row_data );
            if ( row_data ) {
                save_voice_sample( row_data.text || row_data.title );
            }
        } );

        /**
         * Score the current title and insert into the table.
         */
        $( document ).on( 'click', '#occ_titles_score_current', function( e ) {
            e.preventDefault();
            var $button = $( this );
            var current_title = get_current_title();
            if ( ! current_title ) {
                display_custom_error( 'No title found to score.' );
                return;
            }

            var matched = false;
            var updated = currentTitles.map( function( item ) {
                if ( item && item.text && item.text.toLowerCase() === current_title.toLowerCase() ) {
                    matched = true;
                    return Object.assign( {}, item, { is_current: true } );
                }
                return item;
            } );

            if ( ! matched ) {
                updated.unshift( {
                    text: current_title,
                    style: 'Current',
                    sentiment: 'Neutral',
                    keywords: get_selected_keywords(),
                    is_current: true
                } );
            }

            display_titles( updated, {
                generated_at: lastGeneratedAt || occ_titles_admin_vars.now,
                provider: lastProvider,
                style: $( '#occ_titles_style' ).val() || '',
                intent: $( '#occ_titles_intent' ).val() || '',
                ellipsis: $( '#occ_titles_ellipsis' ).is( ':checked' ) ? 1 : 0,
                from_cache: true
            } );

            $button.addClass( 'is-success' ).text( 'Scored' );
            setTimeout( function() {
                $button.removeClass( 'is-success' ).text( 'Score Current Title' );
            }, 1500 );
        } );

        /**
         * Toggle results panel visibility.
         */
        $( document ).on( 'click', '#occ_titles_toggle_panel', function( e ) {
            e.preventDefault();
            var is_collapsed = $( '.occ_titles-results' ).hasClass( 'is-collapsed' );
            set_results_collapsed( ! is_collapsed );
        } );

        /**
         * Undo to the original title.
         */
        $( document ).on( 'click', '.occ_titles-undo', function( e ) {
            e.preventDefault();
            if ( originalTitle ) {
                set_title_in_editor( originalTitle );
                $( '.occ_titles-row' ).removeClass( 'is-applied' );
            }
        } );

        /**
         * Iterate on a specific title.
         */
        $( document ).on( 'click', '.occ_titles-iterate-btn', function( e ) {
            e.preventDefault();
            if ( isProcessing ) {
                return;
            }
            isProcessing = true;

            var index = parseInt( $( this ).attr( 'data-index' ), 10 );
            var variation = $( this ).attr( 'data-variation' );
            var row_data = currentTitles[ index ];
            if ( ! row_data ) {
                isProcessing = false;
                return;
            }

            var keyword = '';
            if ( 'keyword' === variation ) {
                keyword = window.prompt( 'Enter a keyword to include:' ) || '';
                if ( ! keyword ) {
                    isProcessing = false;
                    return;
                }
            }

            var content = window.editorMode.is_block_editor ?
                wp.data.select( 'core/editor' ).getEditedPostContent() :
                $( 'textarea#content' ).val();
            window.occTitlesContentCache = content;
            var style = $( '#occ_titles_style' ).val() || row_data.style || '';
            var intent = $( '#occ_titles_intent' ).val() || '';
            var ellipsis = $( '#occ_titles_ellipsis' ).is( ':checked' ) ? 1 : 0;

            $( '.occ_titles-row[data-index="' + index + '"]' ).addClass( 'is-loading' );

            send_ajax_request( {
                content: content,
                style: style,
                seed_title: row_data.text || row_data.title,
                variation: variation,
                keyword: keyword,
                count: 1,
                intent: intent,
                ellipsis: ellipsis,
                keywords: get_selected_keywords()
            }, function( response ) {
                $( '.occ_titles-row[data-index="' + index + '"]' ).removeClass( 'is-loading' );
                if ( response.success ) {
                    var titles = normalize_titles( response.data.titles );
                    if ( titles.length ) {
                        currentTitles[ index ] = titles[ 0 ];
                        lastGeneratedAt = response.data.generated_at || occ_titles_admin_vars.now;
                        lastProvider = response.data.provider || lastProvider;
                        display_titles( currentTitles, {
                            generated_at: lastGeneratedAt,
                            provider: lastProvider,
                            style: style
                        } );
                    }
                } else {
                    display_custom_error( response.data.message || 'An unknown error occurred.' );
                }
            } ).always( function() {
                isProcessing = false;
            } );
        } );

        /**
         * Copy all titles to clipboard.
         */
        $( document ).on( 'click', '#occ_titles_copy_all', function( e ) {
            e.preventDefault();
            var titles = [];
            $( '.occ_titles-row' ).each( function( index ) {
                var data_index = parseInt( $( this ).attr( 'data-index' ), 10 );
                var title_obj = currentTitles[ data_index ];
                var title_text = title_obj ? ( title_obj.text || title_obj ) : '';
                titles.push( ( index + 1 ) + '. ' + title_text );
            } );

            if ( navigator.clipboard && navigator.clipboard.writeText ) {
                navigator.clipboard.writeText( titles.join( '\n' ) );
            } else {
                var $temp = $( '<textarea></textarea>' );
                $( 'body' ).append( $temp );
                $temp.val( titles.join( '\n' ) ).select();
                document.execCommand( 'copy' );
                $temp.remove();
            }
        } );

        /**
         * Export titles to CSV.
         */
        $( document ).on( 'click', '#occ_titles_export_csv', function( e ) {
            e.preventDefault();
            var $rows = $( '.occ_titles-row' );
            if ( ! $rows.length ) {
                return;
            }

            var csv = 'Rank,Title,Style,Sentiment,Keywords\n';
            $rows.each( function( index ) {
                var data_index = parseInt( $( this ).attr( 'data-index' ), 10 );
                var row = currentTitles[ data_index ] || {};
                var title_text = ( row.text || '' ).replace( /"/g, '""' );
                var style = ( row.style || '' ).replace( /"/g, '""' );
                var sentiment = ( row.sentiment || '' ).replace( /"/g, '""' );
                var keywords = Array.isArray( row.keywords ) ? row.keywords.join( '; ' ) : '';
                csv += '"' + ( index + 1 ) + '","' + title_text + '","' + style + '","' + sentiment + '","' + keywords + '"\n';
            } );

            var blob = new Blob( [ csv ], { type: 'text/csv;charset=utf-8;' } );
            var link = document.createElement( 'a' );
            link.href = URL.createObjectURL( blob );
            link.download = 'occ-titles-' + ( new Date().toISOString().slice( 0, 10 ) ) + '.csv';
            document.body.appendChild( link );
            link.click();
            document.body.removeChild( link );
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
