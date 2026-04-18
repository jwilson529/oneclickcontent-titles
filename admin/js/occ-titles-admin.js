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
         * Gets a localized UI string when available.
         *
         * @param {string} key      String key.
         * @param {string} fallback Fallback string.
         * @return {string} UI string.
         */
        function get_ui_string( key, fallback ) {
            if ( occ_titles_admin_vars && occ_titles_admin_vars.strings && occ_titles_admin_vars.strings[ key ] ) {
                return occ_titles_admin_vars.strings[ key ];
            }

            return fallback;
        }

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
         * Clamp a score to 0-100.
         *
         * @param {number} score Raw score.
         * @return {number} Clamped score.
         */
        function clamp_score( score ) {
            return Math.max( 0, Math.min( 100, score ) );
        }

        /**
         * Get a normalized goal key.
         *
         * @param {string} goal Goal label.
         * @return {string} Normalized goal key.
         */
        function get_goal_key( goal ) {
            return ( goal || '' ).toLowerCase().trim();
        }

        /**
         * Get goal-specific score weights.
         *
         * @param {string} goal Goal label.
         * @return {Object} Weight profile.
         */
        function get_goal_weight_profile( goal ) {
            var goal_key = get_goal_key( goal );
            var profile = {
                label: 'Balanced',
                weights: {
                    intent: 0.18,
                    keyword: 0.16,
                    length: 0.12,
                    pixel: 0.12,
                    specificity: 0.11,
                    opening: 0.10,
                    clarity: 0.09,
                    readability: 0.07,
                    sentiment: 0.05
                }
            };

            if ( goal_key.indexOf( 'rank for keyword' ) !== -1 ) {
                profile.label = 'SEO';
                profile.weights = {
                    intent: 0.15,
                    keyword: 0.24,
                    length: 0.14,
                    pixel: 0.13,
                    specificity: 0.10,
                    opening: 0.07,
                    clarity: 0.08,
                    readability: 0.05,
                    sentiment: 0.04
                };
            } else if ( goal_key.indexOf( 'increase ctr' ) !== -1 ) {
                profile.label = 'CTR';
                profile.weights = {
                    intent: 0.20,
                    keyword: 0.12,
                    length: 0.11,
                    pixel: 0.10,
                    specificity: 0.14,
                    opening: 0.14,
                    clarity: 0.09,
                    readability: 0.05,
                    sentiment: 0.05
                };
            } else if ( goal_key.indexOf( 'discover' ) !== -1 || goal_key.indexOf( 'social' ) !== -1 ) {
                profile.label = 'Discovery';
                profile.weights = {
                    intent: 0.20,
                    keyword: 0.10,
                    length: 0.10,
                    pixel: 0.14,
                    specificity: 0.13,
                    opening: 0.15,
                    clarity: 0.08,
                    readability: 0.04,
                    sentiment: 0.06
                };
            } else if ( goal_key.indexOf( 'thought leadership' ) !== -1 ) {
                profile.label = 'Authority';
                profile.weights = {
                    intent: 0.20,
                    keyword: 0.10,
                    length: 0.10,
                    pixel: 0.09,
                    specificity: 0.16,
                    opening: 0.08,
                    clarity: 0.14,
                    readability: 0.09,
                    sentiment: 0.04
                };
            } else if ( goal_key.indexOf( 'lead gen' ) !== -1 ) {
                profile.label = 'Lead Gen';
                profile.weights = {
                    intent: 0.22,
                    keyword: 0.12,
                    length: 0.10,
                    pixel: 0.10,
                    specificity: 0.14,
                    opening: 0.13,
                    clarity: 0.12,
                    readability: 0.04,
                    sentiment: 0.03
                };
            }

            return profile;
        }

        /**
         * Score intent match.
         *
         * @param {Object} metrics Metrics object.
         * @return {number} Intent score.
         */
        function score_intent_match( metrics ) {
            var goal_key = get_goal_key( metrics.goal );
            var title = ( metrics.title || '' ).toLowerCase();
            var score = 72;

            if ( ! goal_key ) {
                return score;
            }

            if ( goal_key.indexOf( 'rank for keyword' ) !== -1 ) {
                if ( metrics.has_keyword_targets && metrics.keyword_density > 0 ) {
                    score += 18;
                }
                if ( title.indexOf( 'seo' ) !== -1 || title.indexOf( 'guide' ) !== -1 || metrics.starts_strong ) {
                    score += 8;
                }
            } else if ( goal_key.indexOf( 'increase ctr' ) !== -1 ) {
                if ( metrics.has_power_word || metrics.starts_strong ) {
                    score += 15;
                }
                if ( metrics.has_number ) {
                    score += 8;
                }
            } else if ( goal_key.indexOf( 'discover' ) !== -1 || goal_key.indexOf( 'social' ) !== -1 ) {
                if ( metrics.has_power_word || metrics.has_number ) {
                    score += 12;
                }
                if ( metrics.pixel_width >= 540 && metrics.pixel_width <= 620 ) {
                    score += 10;
                }
            } else if ( goal_key.indexOf( 'thought leadership' ) !== -1 ) {
                if ( metrics.readability_score >= 2.5 && metrics.readability_score <= 6.5 ) {
                    score += 10;
                }
                if ( metrics.word_count >= 7 && metrics.word_count <= 12 ) {
                    score += 10;
                }
            } else if ( goal_key.indexOf( 'lead gen' ) !== -1 ) {
                if ( title.indexOf( 'how to' ) !== -1 || title.indexOf( 'why' ) !== -1 || title.indexOf( 'best' ) !== -1 ) {
                    score += 12;
                }
                if ( metrics.has_power_word || metrics.starts_strong ) {
                    score += 10;
                }
            }

            return clamp_score( score );
        }

        /**
         * Calculate expanded signal scores.
         *
         * @param {Object} metrics Metrics object.
         * @return {Object} Signal scores.
         */
        function calculate_signal_scores( metrics ) {
            var readability_score_normalized = clamp_score( 100 - Math.abs( metrics.readability_score - 4.5 ) * 16 );
            var sentiment_score = metrics.sentiment === 'Positive' ? 100 : ( metrics.sentiment === 'Neutral' ? 75 : 50 );
            var keyword_score = 75;

            if ( metrics.has_keyword_targets ) {
                if ( metrics.keyword_density > 0 && metrics.keyword_density <= 0.30 ) {
                    keyword_score = 100;
                } else if ( metrics.keyword_density > 0.30 && metrics.keyword_density <= 0.45 ) {
                    keyword_score = 70;
                } else {
                    keyword_score = 45;
                }
            }

            var pixel_score = 50;
            if ( metrics.pixel_width >= 560 && metrics.pixel_width <= 600 ) {
                pixel_score = 100;
            } else if ( metrics.pixel_width >= 540 && metrics.pixel_width <= 620 ) {
                pixel_score = 85;
            } else if ( metrics.pixel_width >= 520 && metrics.pixel_width <= 640 ) {
                pixel_score = 70;
            }

            var opening_score = 55;
            if ( metrics.has_power_word && metrics.starts_strong ) {
                opening_score = 100;
            } else if ( metrics.has_power_word || metrics.starts_strong ) {
                opening_score = 82;
            }

            var specificity_score = 50;
            if ( metrics.has_number ) {
                specificity_score += 20;
            }
            if ( metrics.has_separator ) {
                specificity_score += 15;
            }
            if ( metrics.word_count >= 6 && metrics.word_count <= 12 ) {
                specificity_score += 15;
            }

            var clarity_score = metrics.word_count >= 7 && metrics.word_count <= 12 ? 100 : ( metrics.word_count >= 5 && metrics.word_count <= 14 ? 80 : 62 );
            if ( /[!?]{2,}/.test( metrics.title ) ) {
                clarity_score -= 20;
            }

            return {
                length: metrics.seo_data.score,
                pixel: pixel_score,
                keyword: keyword_score,
                readability: readability_score_normalized,
                sentiment: sentiment_score,
                opening: opening_score,
                specificity: clamp_score( specificity_score ),
                clarity: clamp_score( clarity_score ),
                intent: score_intent_match( metrics )
            };
        }

        /**
         * Calculate weighted score from signal scores.
         *
         * @param {Object} signal_scores Signal scores.
         * @param {Object} weights Weight map.
         * @return {number} Weighted score.
         */
        function calculate_weighted_score( signal_scores, weights ) {
            return Object.keys( weights ).reduce( function( total, key ) {
                return total + ( signal_scores[ key ] || 0 ) * weights[ key ];
            }, 0 );
        }

        /**
         * Convert score to letter grade.
         *
         * @param {number} score Numeric score.
         * @return {string} Letter grade.
         */
        function get_letter_grade( score ) {
            if ( score >= 85 ) {
                return 'A';
            }
            if ( score >= 70 ) {
                return 'B';
            }
            return 'C';
        }

        /**
         * Escape HTML entities in text.
         *
         * @param {string} text Raw text.
         * @return {string} Escaped text.
         */
        function escape_html( text ) {
            return String( text ).replace( /[&<>"']/g, function( char ) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    '\'': '&#039;'
                }[ char ];
            } );
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
            var safe_title = escape_html( title );
            var safe_url = escape_html( url );
            return '<div class="occ_titles-serp">' +
                '<div class="occ_titles-serp-url">' + safe_url + '</div>' +
                '<div class="occ_titles-serp-title">' + safe_title + '</div>' +
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
            var safe_title = escape_html( title );
            var safe_source = escape_html( source );
            var safe_domain_initial = escape_html( domain_initial );
            return '<div class="occ_titles-discover-card">' +
                '<div class="occ_titles-discover-image">' +
                    '<span class="occ_titles-discover-image-label">Top stories</span>' +
                '</div>' +
                '<div class="occ_titles-discover-body">' +
                    '<div class="occ_titles-discover-meta">' +
                        '<span class="occ_titles-discover-favicon">' + safe_domain_initial + '</span>' +
                        '<span class="occ_titles-discover-source">' + safe_source + '</span>' +
                        '<span class="occ_titles-discover-dot">•</span>' +
                        '<span class="occ_titles-discover-time">3h</span>' +
                    '</div>' +
                    '<div class="occ_titles-discover-title">' + safe_title + '</div>' +
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
            var checks = 0;

            checks += 1;
            if ( metrics.char_count >= 50 && metrics.char_count <= 60 ) {
                passes += 1;
            }

            if ( metrics.has_keyword_targets ) {
                checks += 1;
                if ( metrics.keyword_density > 0 && metrics.keyword_density <= 0.30 ) {
                    passes += 1;
                }
            }

            checks += 1;
            if ( metrics.has_power_word || metrics.starts_strong ) {
                passes += 1;
            }

            checks += 1;
            if ( metrics.pixel_width >= 540 && metrics.pixel_width <= 620 ) {
                passes += 1;
            }

            return passes >= Math.max( 2, Math.ceil( checks * 0.67 ) ) ? 'Pass' : 'Needs work';
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
            if ( density > 0 && density <= 0.30 ) {
                return 'High';
            }
            if ( density > 0.30 ) {
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
                .text( collapsed ? get_ui_string( 'show_results', 'Show results' ) : get_ui_string( 'collapse_results', 'Collapse results' ) )
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

            var header_subtitle = lastGeneratedAt
                ? get_ui_string( 'results_last', 'Last generated:' ) + ' ' + lastGeneratedAt
                : get_ui_string( 'results_empty', 'Generate titles to see results.' );
            var provider_label = lastProvider ? get_ui_string( 'results_provider', 'Provider:' ) + ' ' + lastProvider.toUpperCase() : '';

            var $header = $( '<div class="occ_titles-results-header"></div>' );
            var $header_left = $( '<div class="occ_titles-results-summary"></div>' );
            $header_left.append( '<h3 class="occ_titles-results-title">' + escape_html( get_ui_string( 'results_title', 'Title Recommendations' ) ) + '</h3>' );
            $header_left.append( '<p class="occ_titles-results-meta">' + escape_html( header_subtitle ) + '</p>' );
            if ( provider_label ) {
                $header_left.append( '<p class="occ_titles-results-provider">' + escape_html( provider_label ) + '</p>' );
            }
            var $header_actions = $( '<div class="occ_titles-results-actions"></div>' );
            var $utility_actions = $( '<div class="occ_titles-results-utility"></div>' );
            $utility_actions.append( '<button type="button" class="button occ_titles-results-toggle" id="occ_titles_toggle_panel" aria-expanded="true">' + escape_html( get_ui_string( 'collapse_results', 'Collapse results' ) ) + '</button>' );
            $header_actions.append( $utility_actions );
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
                '<div class="occ_titles-controls-header">' +
                    '<div class="occ_titles-controls-summary">' +
                        '<p class="occ_titles-controls-kicker">' + escape_html( get_ui_string( 'controls_kicker', 'Optimize before you generate' ) ) + '</p>' +
                        '<h4 class="occ_titles-controls-title">' + escape_html( get_ui_string( 'controls_title', 'Generation Controls' ) ) + '</h4>' +
                        '<p class="occ_titles-controls-intro">' + escape_html( get_ui_string( 'controls_intro', 'Choose the outcome you want, then generate a fresh batch.' ) ) + '</p>' +
                    '</div>' +
                    '<button type="button" class="button button-secondary button-small occ_titles-controls-toggle" aria-expanded="true">' + escape_html( get_ui_string( 'collapse_controls', 'Collapse controls' ) ) + '</button>' +
                '</div>' +
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
                        '<button type="button" class="button button-primary" id="occ_titles_generate_button_top">' + escape_html( get_ui_string( 'generate_titles', 'Generate Titles' ) ) + '</button>' +
                        '<button type="button" class="button" id="occ_titles_revert_button_top">' + escape_html( get_ui_string( 'revert_title', 'Revert to Original Title' ) ) + '</button>' +
                    '</div>' +
                '</div>' +
                '<p class="occ_titles-controls-help">' + escape_html( get_ui_string( 'controls_help', 'Set goal, style, and optional keyword targets before generating.' ) ) + '</p>'
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
            var selected_keywords = get_selected_keywords();
            var selected_goal = $( '#occ_titles_intent' ).val() || metadata.intent || '';
            var score_profile = get_goal_weight_profile( selected_goal );
            var preview_intent = selected_goal;

            normalized.forEach( function( title_obj, index ) {
                var title_text = title_obj.text || '';
                var char_count = title_text.length;
                var sentiment = title_obj.sentiment || 'Neutral';
                var sentiment_emoji = get_emoji_for_sentiment( sentiment );
                var keywords = Array.isArray( title_obj.keywords ) ? title_obj.keywords : [];
                var has_keyword_targets = selected_keywords.length > 0 || keywords.length > 0;
                var keyword_density = calculate_keyword_density( title_text, keywords );
                var readability_score = calculate_readability_score( title_text );
                var seo_data = calculate_seo_grade( char_count );
                var pixel_width = measure_text_width( title_text );
                var pixel_percent = Math.min( 100, Math.round( ( pixel_width / 600 ) * 100 ) );
                var has_power_word = /ultimate|best|proven|simple|easy|fast|guide|tips|secrets/i.test( title_text );
                var has_separator = /[:,\-]/.test( title_text );
                var has_number = /\d/.test( title_text );
                var word_count = title_text.split( /\s+/ ).filter( function( word ) {
                    return word.trim().length > 0;
                } ).length;
                var starts_strong = /^(?:[0-9]|how|why|what|when|the|this|your)\b/i.test( title_text.trim() );
                var signal_scores = calculate_signal_scores( {
                    title: title_text,
                    goal: selected_goal,
                    seo_data: seo_data,
                    sentiment: sentiment,
                    keyword_density: keyword_density,
                    readability_score: readability_score,
                    has_keyword_targets: has_keyword_targets,
                    has_power_word: has_power_word,
                    has_separator: has_separator,
                    has_number: has_number,
                    starts_strong: starts_strong,
                    pixel_width: pixel_width,
                    word_count: word_count
                } );
                var overall_score = calculate_weighted_score( signal_scores, score_profile.weights );
                var gate = get_quality_gate( {
                    char_count: char_count,
                    keyword_density: keyword_density,
                    has_keyword_targets: has_keyword_targets,
                    has_power_word: has_power_word,
                    starts_strong: starts_strong,
                    pixel_width: pixel_width
                } );
                var grade = get_letter_grade( overall_score );
                var signal_summary = [
                    'Intent ' + Math.round( signal_scores.intent ),
                    'Keyword ' + Math.round( signal_scores.keyword ),
                    'Specificity ' + Math.round( signal_scores.specificity ),
                    'Opening ' + Math.round( signal_scores.opening ),
                    'Pixel ' + Math.round( signal_scores.pixel )
                ].join( ' • ' );

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
                    grade: grade,
                    style: title_obj.style || metadata.style || '',
                    gate: gate,
                    signal_summary: signal_summary,
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
                $score_cell.append( '<div class="occ_titles-grade occ_titles-grade-' + row_data.grade.toLowerCase() + '">Grade ' + escape_html( row_data.grade ) + '</div>' );

                var $insights_cell = $( '<td class="occ_titles-col-insights"></td>' );
                var $signal_breakdown = $( '<div class="occ_titles-signal-breakdown"></div>' );
                $signal_breakdown.text( row_data.signal_summary );
                var $chips = $( '<div class="occ_titles-chips"></div>' );
                $chips.append( '<span class="occ_titles-chip occ_titles-gate ' + ( row_data.gate === 'Pass' ? 'is-pass' : 'is-warning' ) + '">' + escape_html( row_data.gate ) + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Sentiment: ' + escape_html( row_data.sentiment ) + ' ' + escape_html( row_data.sentiment_emoji ) + '</span>' );
                if ( row_data.style ) {
                    $chips.append( '<span class="occ_titles-chip">Style: ' + escape_html( row_data.style ) + '</span>' );
                }
                $chips.append( '<span class="occ_titles-chip">Length: ' + escape_html( length_label ) + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Keyword fit: ' + escape_html( keyword_fit ) + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Readability: ' + escape_html( readability_formatted ) + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Density: ' + escape_html( keyword_density_pct ) + '</span>' );
                $chips.append( '<span class="occ_titles-chip">Profile: ' + escape_html( score_profile.label ) + '</span>' );
                $insights_cell.append( $signal_breakdown );
                $insights_cell.append( $chips );

                var $keywords_cell = $( '<td class="occ_titles-col-keywords"></td>' ).text( keywords_list );
                var $serp_cell = $( '<td class="occ_titles-col-serp"></td>' );
                $serp_cell.append( build_title_preview( row_data.title, preview_intent ) );
                $serp_cell.append( '<div class="occ_titles-serp-meter"><span style="width:' + row_data.pixel_percent + '%"></span></div>' );
                $serp_cell.append( '<div class="occ_titles-serp-meta">Target: 560 to 600 px • Current: ' + row_data.pixel_width + 'px</div>' );

                $row.append( $rank_cell, $title_cell, $score_cell, $insights_cell, $keywords_cell, $serp_cell );
                $table_body.append( $row );
            } );

            $titles_table.append( $table_body );
            var $top_picks = $( '<div class="occ_titles-top-picks"></div>' );
            var primary_row = rows[0] || null;
            var extra_rows = rows.slice( 1, 3 );

            if ( primary_row ) {
                var primary_density_pct = ( primary_row.keyword_density * 100 ).toFixed( 2 ) + '%';
                var primary_readability = primary_row.readability.toFixed( 2 );
                var primary_keyword_fit = get_keyword_fit_label( primary_row.keyword_density );
                var primary_length = get_length_label( primary_row.title.length );
                var primary_score = Math.round( primary_row.overall_score );
                var $primary_card = $( '<article class="occ_titles-pick-card is-primary"></article>' );
                var $primary_title = $( '<button type="button" class="occ_titles-pick-title"></button>' ).text( primary_row.title );
                var $primary_actions = $( '<div class="occ_titles-title-actions occ_titles-pick-actions"></div>' );

                $primary_card.attr( 'data-index', primary_row.index );
                $primary_card.addClass( 'is-best' );
                if ( primary_row.is_current ) {
                    $primary_card.addClass( 'is-current' );
                }

                $primary_card.append(
                    '<div class="occ_titles-pick-head">' +
                        '<div class="occ_titles-pick-rank-wrap">' +
                            '<span class="occ_titles-pick-rank">#1</span>' +
                            '<span class="occ_titles-best-badge">' + escape_html( get_ui_string( 'pick_best_for', 'Best for' ) ) + ' ' + escape_html( score_profile.label ) + '</span>' +
                            ( primary_row.is_current ? '<span class="occ_titles-current-badge">' + escape_html( get_ui_string( 'pick_current', 'Current title' ) ) + '</span>' : '' ) +
                        '</div>' +
                        '<div class="occ_titles-pick-score">' +
                            '<span class="occ_titles-score-value">' + primary_score + '</span>' +
                            '<span class="occ_titles-grade occ_titles-grade-' + primary_row.grade.toLowerCase() + '">Grade ' + escape_html( primary_row.grade ) + '</span>' +
                        '</div>' +
                    '</div>'
                );

                $primary_title.on( 'click', function() {
                    apply_title_from_row( primary_row );
                } );
                $primary_card.append( $primary_title );
                $primary_card.append(
                    '<div class="occ_titles-pick-metrics">' +
                        '<span class="occ_titles-chip">' + escape_html( get_ui_string( 'pick_length', 'Length' ) ) + ': ' + escape_html( primary_length ) + '</span>' +
                        '<span class="occ_titles-chip">' + escape_html( get_ui_string( 'pick_keywords', 'Keyword fit' ) ) + ': ' + escape_html( primary_keyword_fit ) + '</span>' +
                        '<span class="occ_titles-chip">' + escape_html( get_ui_string( 'pick_pixel', 'Pixel width' ) ) + ': ' + escape_html( primary_row.pixel_width + 'px' ) + '</span>' +
                        '<span class="occ_titles-chip">' + escape_html( get_ui_string( 'pick_readability', 'Readability' ) ) + ': ' + escape_html( primary_readability ) + '</span>' +
                    '</div>'
                );
                $primary_card.append(
                    '<div class="occ_titles-pick-why">' +
                        '<strong>' + escape_html( get_ui_string( 'pick_why', 'Why it works' ) ) + ':</strong> ' + escape_html( primary_row.signal_summary ) + ' • Density ' + escape_html( primary_density_pct ) +
                    '</div>'
                );

                if ( primary_row.is_current ) {
                    $primary_actions.append( '<span class="occ_titles-applied-label is-visible" aria-hidden="true">This is your current title</span>' );
                } else {
                    $primary_actions.append( '<button type="button" class="button button-primary occ_titles-apply" data-index="' + primary_row.index + '">' + escape_html( get_ui_string( 'pick_apply', 'Apply this title' ) ) + '</button>' );
                    $primary_actions.append( '<button type="button" class="button occ_titles-undo" data-index="' + primary_row.index + '">' + escape_html( get_ui_string( 'revert_title', 'Revert to Original Title' ) ) + '</button>' );
                    $primary_actions.append( '<span class="occ_titles-applied-label" aria-hidden="true">Applied</span>' );
                }

                $primary_card.append( $primary_actions );
                $top_picks.append( $primary_card );
            }

            if ( extra_rows.length ) {
                var $more_picks = $( '<details class="occ_titles-more-picks"></details>' );
                var $more_picks_body = $( '<div class="occ_titles-more-picks-body"></div>' );
                $more_picks.append(
                    '<summary class="occ_titles-more-picks-summary">' +
                        '<span class="occ_titles-more-picks-title">' + extra_rows.length + ' ' + escape_html( get_ui_string( 'results_more_options', 'More options' ) ) + '</span>' +
                        '<span class="occ_titles-more-picks-meta">' + escape_html( get_ui_string( 'results_summary', 'Start with the strongest options below. Open the full breakdown only if you want the deeper score math.' ) ) + '</span>' +
                    '</summary>'
                );

                extra_rows.forEach( function( row_data, offset ) {
                    var overall_score_formatted = Math.round( row_data.overall_score );
                    var $compact_card = $( '<article class="occ_titles-pick-card is-compact"></article>' );
                    var $compact_title = $( '<button type="button" class="occ_titles-pick-title"></button>' ).text( row_data.title );
                    var $compact_actions = $( '<div class="occ_titles-title-actions occ_titles-pick-actions"></div>' );

                    $compact_card.attr( 'data-index', row_data.index );
                    if ( row_data.is_current ) {
                        $compact_card.addClass( 'is-current' );
                    }

                    $compact_card.append(
                        '<div class="occ_titles-pick-head">' +
                            '<div class="occ_titles-pick-rank-wrap">' +
                                '<span class="occ_titles-pick-rank">#' + ( offset + 2 ) + '</span>' +
                                ( row_data.is_current ? '<span class="occ_titles-current-badge">' + escape_html( get_ui_string( 'pick_current', 'Current title' ) ) + '</span>' : '' ) +
                            '</div>' +
                            '<div class="occ_titles-pick-score">' +
                                '<span class="occ_titles-score-value">' + overall_score_formatted + '</span>' +
                                '<span class="occ_titles-grade occ_titles-grade-' + row_data.grade.toLowerCase() + '">Grade ' + escape_html( row_data.grade ) + '</span>' +
                            '</div>' +
                        '</div>'
                    );

                    $compact_title.on( 'click', function() {
                        apply_title_from_row( row_data );
                    } );
                    $compact_card.append( $compact_title );

                    if ( row_data.is_current ) {
                        $compact_actions.append( '<span class="occ_titles-applied-label is-visible" aria-hidden="true">This is your current title</span>' );
                    } else {
                        $compact_actions.append( '<button type="button" class="button button-secondary occ_titles-apply" data-index="' + row_data.index + '">' + escape_html( get_ui_string( 'pick_apply', 'Apply this title' ) ) + '</button>' );
                    }

                    $compact_card.append( $compact_actions );
                    $more_picks_body.append( $compact_card );
                } );

                $more_picks.append( $more_picks_body );
                $top_picks.append( $more_picks );
            }

            if ( primary_row ) {
                $container.append( $top_picks );
            }

            var keywords_summary = all_keywords.length ? all_keywords.join( ', ' ) : 'None';
            var $guidance = $( '<div class="occ_titles-guidance"></div>' );
            $guidance.append(
                '<div class="occ_titles-guidance-card">' +
                    '<strong>How to pick:</strong> Start with the top card, then compare only if the tone or goal feels off.' +
                '</div>' +
                '<div class="occ_titles-guidance-card">' +
                    '<strong>Pixel target:</strong> Google usually trims around 560 to 600 px. Stay close to the green zone.' +
                '</div>' +
                '<div class="occ_titles-guidance-card"><strong>Keywords used:</strong> ' + escape_html( keywords_summary ) + '</div>'
            );

            var $breakdown = $( '<details class="occ_titles-breakdown"></details>' );
            var $breakdown_summary = $( '<summary class="occ_titles-breakdown-summary"></summary>' );
            var $breakdown_body = $( '<div class="occ_titles-breakdown-body"></div>' );
            var $deep_actions = $( '<div class="occ_titles-results-bulk-actions occ_titles-breakdown-actions"></div>' );

            $breakdown_summary.append(
                '<span class="occ_titles-breakdown-title">' + escape_html( get_ui_string( 'open_breakdown', 'Open full breakdown' ) ) + ' (' + rows.length + ')</span>' +
                '<span class="occ_titles-breakdown-meta">' + escape_html( get_ui_string( 'breakdown_label', 'Detailed scoring, previews, exports, and keyword notes' ) ) + '</span>'
            );

            $deep_actions.append( '<button type="button" class="button button-secondary" id="occ_titles_score_current">' + escape_html( get_ui_string( 'score_current', 'Score Current Title' ) ) + '</button>' );
            $deep_actions.append( '<button type="button" class="button button-secondary" id="occ_titles_copy_all">' + escape_html( get_ui_string( 'copy_all', 'Copy All' ) ) + '</button>' );
            $deep_actions.append( '<button type="button" class="button button-secondary" id="occ_titles_export_csv">' + escape_html( get_ui_string( 'download_csv', 'Download CSV' ) ) + '</button>' );

            $breakdown_body.append( $deep_actions, $guidance, $titles_table );
            $breakdown.append( $breakdown_summary, $breakdown_body );
            $container.append( $breakdown );

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
            $( '.occ_titles-row, .occ_titles-pick-card' ).removeClass( 'is-applied' );
            $( '.occ_titles-row[data-index="' + row_data.index + '"], .occ_titles-pick-card[data-index="' + row_data.index + '"]' ).addClass( 'is-applied' );
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
                    title: title,
                    post_id: get_post_id()
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
            var $panel = ensure_error_panel();
            if ( ! $panel.length ) {
                return;
            }

            var safe_message = escape_html( message || '' );
            var settings_url = occ_titles_admin_vars.settings_url || '';
            var cta = settings_url ? '<a class="button button-secondary" href="' + settings_url + '">Open settings</a>' : '';

            $panel.html(
                '<div class="occ_titles-error-content">' +
                    '<strong>Generation issue:</strong> ' + safe_message +
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
                .fail( function( jqXHR ) {
                    display_custom_error( get_ajax_error_message( jqXHR, 'We encountered an issue connecting to the server. Please check your API key and try again.' ) );
                } );
        }

        /**
         * Extract a useful error message from an AJAX response.
         *
         * @param {Object} jqXHR jQuery XHR object.
         * @param {string} fallback Fallback message.
         * @return {string} Error message.
         */
        function get_ajax_error_message( jqXHR, fallback ) {
            if ( jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) {
                return jqXHR.responseJSON.data.message;
            }

            return fallback;
        }

        /**
         * Ensure a persistent error panel exists.
         *
         * @return {Object} jQuery collection for the error panel.
         */
        function ensure_error_panel() {
            var $container = $( '#occ_titles_table_container' );
            var $panel = $container.children( '.occ_titles-error-panel' ).first();

            if ( ! $panel.length && $container.length ) {
                $panel = $( '<div class="occ_titles-error-panel" style="display:none;"></div>' );
                $container.prepend( $panel );
            }

            return $panel;
        }

        /**
         * Hide the persistent error panel.
         */
        function clear_error_panel() {
            ensure_error_panel().hide().empty();
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
                .text( error_message || '' )
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
            clear_error_panel();

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
            if ( stored !== '0' ) {
                $( '.occ_titles-controls' ).addClass( 'is-collapsed' );
                $( '.occ_titles-controls-toggle' ).attr( 'aria-expanded', 'false' ).text( get_ui_string( 'show_controls', 'Show controls' ) );
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
                .text( collapsed ? get_ui_string( 'show_controls', 'Show controls' ) : get_ui_string( 'collapse_controls', 'Collapse controls' ) );
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
            clear_error_panel();

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
            $( '.occ_titles-row, .occ_titles-pick-card' ).removeClass( 'is-applied' );
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
                $( '.occ_titles-row, .occ_titles-pick-card' ).removeClass( 'is-applied' );
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

            $( '.occ_titles-row[data-index="' + index + '"], .occ_titles-pick-card[data-index="' + index + '"]' ).addClass( 'is-loading' );

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
                $( '.occ_titles-row[data-index="' + index + '"], .occ_titles-pick-card[data-index="' + index + '"]' ).removeClass( 'is-loading' );
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
