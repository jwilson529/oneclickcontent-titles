const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const test = require( 'node:test' );
const vm = require( 'node:vm' );

const bridgeSource = fs.readFileSync(
	path.join( __dirname, '../../admin/js/occ-titles-editor-bridge.js' ),
	'utf8'
);

function loadBridge( options = {} ) {
	const events = [];
	const titleInput = options.titleInput || null;
	const prompt = options.prompt || null;
	const editorTitle = options.editorTitle || null;
	const iframeDocument = options.iframeDocument || null;
	const document = {
		body: {
			classList: {
				contains: ( className ) => className === 'block-editor-page' && !! options.blockEditor
			}
		},
		querySelector: ( selector ) => {
			if ( selector === 'input#title' ) {
				return titleInput;
			}
			if ( selector === '#title-prompt-text' ) {
				return prompt;
			}
			if ( selector === 'iframe[name="editor-canvas"]' ) {
				return iframeDocument ? { contentDocument: iframeDocument } : null;
			}
			return editorTitle;
		}
	};
	const window = {
		Event: function Event( type ) {
			this.type = type;
		},
		wp: options.wp
	};
	const context = { document, window };
	vm.runInNewContext( bridgeSource, context );

	return { bridge: window.occTitlesEditorBridge, events };
}

function createInput( initialValue = '' ) {
	return {
		value: initialValue,
		events: [],
		dispatchEvent( event ) {
			this.events.push( event.type );
		},
		focus() {},
		blur() {}
	};
}

test( 'Classic Editor stays classic even when the core/editor store exists', () => {
	const wp = { data: { select: () => ( {} ) } };
	const { bridge } = loadBridge( { blockEditor: false, wp } );

	const mode = bridge.detectMode();
	assert.equal( mode.is_classic_editor, true );
	assert.equal( mode.is_block_editor, false );
} );

test( 'Block Editor Visual and Code modes use the page-level editor signal', () => {
	const { bridge } = loadBridge( { blockEditor: true } );

	const mode = bridge.detectMode();
	assert.equal( mode.is_classic_editor, false );
	assert.equal( mode.is_block_editor, true );
} );

test( 'Classic Editor title updates and emits dirty-state events', () => {
	const input = createInput( 'Original title' );
	const prompt = { textContent: 'Enter title here' };
	const { bridge } = loadBridge( { titleInput: input, prompt } );
	const updated = bridge.setTitle( 'A stronger title', bridge.detectMode() );

	assert.equal( updated, true );
	assert.equal( input.value, 'A stronger title' );
	assert.deepEqual( input.events, [ 'input', 'change' ] );
	assert.equal( prompt.textContent, '' );
} );

test( 'Block Editor title updates through core/editor exactly once', () => {
	let storedTitle = 'Original title';
	let editCount = 0;
	const wp = {
		data: {
			dispatch: () => ( {
				editPost: ( change ) => {
					editCount += 1;
					storedTitle = change.title;
				}
			} ),
			select: () => ( {
				getEditedPostAttribute: () => storedTitle
			} )
		}
	};
	const { bridge } = loadBridge( { blockEditor: true, wp } );
	const mode = bridge.detectMode();

	assert.equal( bridge.setTitle( 'A stronger title', mode ), true );
	assert.equal( storedTitle, 'A stronger title' );
	assert.equal( editCount, 1 );
} );

test( 'Block Editor falls back to the iframe title field when the store cannot update', () => {
	const editorTitle = createInput( 'Original title' );
	const iframeDocument = {
		querySelector: () => editorTitle
	};
	const wp = {
		data: {
			dispatch: () => ( { editPost() {} } ),
			select: () => ( { getEditedPostAttribute: () => 'Original title' } )
		}
	};
	const { bridge } = loadBridge( { blockEditor: true, iframeDocument, wp } );

	assert.equal( bridge.setTitle( 'A stronger title', bridge.detectMode() ), true );
	assert.equal( editorTitle.value, 'A stronger title' );
	assert.deepEqual( editorTitle.events, [ 'input', 'change' ] );
} );
