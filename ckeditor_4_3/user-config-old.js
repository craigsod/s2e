/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbar = [
	{ name: 'basicstyles', items: ['TextColor', 'FontSize', 'Bold', 'Italic',  '-', 'RemoveFormat' ] },
	{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
	'/',
		{ name: 'styles', items: [ 'Styles'] },
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
	{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
	{ name: 'insert', items: [ 'Image', 'Table', 'SpecialChar' ] },
	{ name: 'others', items: [ 'MediaEmbed','Forms' ] }

];
	
	config.extraPlugins = 'colorbutton,mediaembed,tabletools,justify,stylesheetparser,forms,font,filebrowser,image';

	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Make dialogs simpler.
	config.removeDialogTabs = 'image:advanced;link:advanced';
	
	config.uiColor = '#9AB8F3';
	config.removeButtons = 'Strike,Underline,Subscript,Superscript';
	config.height = '450';
	
		
	
	config.stylesSet = [];
	config.stylesSet = 'default:/edit/ckeditor_4_3/site-styles.js';

	// Sets editor to use the sites CSS file when displaying page in editor
	config.contentsCss = '../styleks1.css';
	
	
	// Controls what items display in the Format drop-down list
	config.format_tags = 'p;h1;h2;div;h3';

	config.filebrowserBrowseUrl = '/edit/ckeditor_4_3/kcfinder/browse.php?type=files';
   config.filebrowserImageBrowseUrl = '/edit/ckeditor_4_3/kcfinder/browse.php?type=images&dir=images';
   config.filebrowserUploadUrl = '/edit/ckeditor_4_3/kcfinder/upload.php?type=files';
   config.filebrowserImageUploadUrl = '/edit/ckeditor_4_3/kcfinder/upload.php?type=images&dir=images';
	config.filebrowserWindowWidth = '640';
	config.filebrowserWindowHeight = '480';
	
};