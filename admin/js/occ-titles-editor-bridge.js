( function( window, document ) {
	'use strict';

	/**
	 * Dispatch a native event when the current browser supports it.
	 *
	 * @param {Element} element Event target.
	 * @param {string}  type    Event type.
	 */
	function dispatchEvent( element, type ) {
		if ( ! element || typeof element.dispatchEvent !== 'function' || typeof window.Event !== 'function' ) {
			return;
		}

		element.dispatchEvent( new window.Event( type, { bubbles: true } ) );
	}

	/**
	 * Return the Block Editor canvas document when WordPress uses an iframe.
	 *
	 * @return {Document} Editor document.
	 */
	function getEditorDocument() {
		var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );

		return iframe && iframe.contentDocument ? iframe.contentDocument : document;
	}

	/**
	 * Locate the visible Block Editor title field.
	 *
	 * @return {Element|null} Title element.
	 */
	function getBlockTitleElement() {
		var editorDocument = getEditorDocument();
		var selectors = [
			'textarea.editor-post-title__input',
			'input.editor-post-title__input',
			'.editor-post-title__input',
			'h1.wp-block-post-title'
		];

		for ( var i = 0; i < selectors.length; i++ ) {
			var element = editorDocument.querySelector( selectors[ i ] );
			if ( element ) {
				return element;
			}
		}

		return null;
	}

	/**
	 * Detect the active editor from WordPress' page-level body class.
	 *
	 * The data store can be registered on Classic Editor screens, so store
	 * availability alone must not be used as a Block Editor signal.
	 *
	 * @return {Object} Editor mode flags.
	 */
	function detectMode() {
		var isBlockEditor = !! ( document.body && document.body.classList && document.body.classList.contains( 'block-editor-page' ) );

		return {
			is_classic_editor: ! isBlockEditor,
			is_block_editor: isBlockEditor
		};
	}

	/**
	 * Set the Classic Editor title and notify its dirty-state listeners.
	 *
	 * @param {string} title New title.
	 * @return {boolean} Whether the title field was updated.
	 */
	function setClassicTitle( title ) {
		var input = document.querySelector( 'input#title' );
		var prompt = document.querySelector( '#title-prompt-text' );

		if ( ! input ) {
			return false;
		}

		if ( prompt ) {
			prompt.textContent = '';
		}
		input.value = title;
		dispatchEvent( input, 'input' );
		dispatchEvent( input, 'change' );
		if ( typeof input.focus === 'function' ) {
			input.focus();
		}
		if ( typeof input.blur === 'function' ) {
			input.blur();
		}

		return input.value === title;
	}

	/**
	 * Update a Block Editor title element as a compatibility fallback.
	 *
	 * @param {string} title New title.
	 * @return {boolean} Whether an element was updated.
	 */
	function setBlockTitleElement( title ) {
		var element = getBlockTitleElement();

		if ( ! element ) {
			return false;
		}

		if ( 'value' in element ) {
			element.value = title;
		} else {
			element.textContent = title;
		}
		dispatchEvent( element, 'input' );
		dispatchEvent( element, 'change' );

		return true;
	}

	/**
	 * Set the Block Editor title through the canonical editor data store.
	 *
	 * @param {string} title New title.
	 * @return {boolean} Whether the store or fallback field was updated.
	 */
	function setBlockTitle( title ) {
		var editorDispatch;
		var editorSelect;

		try {
			if ( window.wp && window.wp.data ) {
				editorDispatch = window.wp.data.dispatch( 'core/editor' );
				if ( editorDispatch && typeof editorDispatch.editPost === 'function' ) {
					editorDispatch.editPost( { title: title } );
					editorSelect = window.wp.data.select( 'core/editor' );
					if ( editorSelect && typeof editorSelect.getEditedPostAttribute === 'function' && editorSelect.getEditedPostAttribute( 'title' ) === title ) {
						return true;
					}
				}
			}
		} catch ( error ) {
			// Fall through to the visible editor field for compatibility.
		}

		return setBlockTitleElement( title );
	}

	/**
	 * Get the current editor title.
	 *
	 * @param {Object} mode Editor mode flags.
	 * @return {string} Current title.
	 */
	function getTitle( mode ) {
		var editorSelect;
		var element;

		if ( mode && mode.is_block_editor ) {
			try {
				if ( window.wp && window.wp.data ) {
					editorSelect = window.wp.data.select( 'core/editor' );
					if ( editorSelect && typeof editorSelect.getEditedPostAttribute === 'function' ) {
						return editorSelect.getEditedPostAttribute( 'title' ) || '';
					}
				}
			} catch ( error ) {
				// Fall through to the visible editor field.
			}
			element = getBlockTitleElement();
			return element ? ( element.value || element.textContent || '' ) : '';
		}

		element = document.querySelector( 'input#title' );
		return element ? element.value || '' : '';
	}

	window.occTitlesEditorBridge = {
		detectMode: detectMode,
		getTitle: getTitle,
		setTitle: function( title, mode ) {
			return mode && mode.is_block_editor ? setBlockTitle( title ) : setClassicTitle( title );
		}
	};
}( window, document ) );
