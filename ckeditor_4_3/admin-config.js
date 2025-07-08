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

	{ name: 'document', items: [ 'Sourcedialog' ] },

	{ name: 'basicstyles', items: ['TextColor', 'FontSize', 'Bold', 'Italic',  '-', 'RemoveFormat' ] },

	{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },

	{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },

	'/',

		{ name: 'styles', items: [ 'Format'] },

		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },

	{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },

	{ name: 'insert', items: [ 'Image', 'Table', 'SpecialChar' ] },

	{ name: 'others', items: [ 'MediaEmbed','Forms' ] },

	{ name: 'bootstrap', items: [ 'jsplusShowBlocks','jsplusBootstrapToolsRowEdit','jsplusBootstrapToolsRowAdd','jsplusBootstrapToolsRowAddBefore','jsplusBootstrapToolsRowAddAfter','jsplusBootstrapToolsRowDelete','jsplusBootstrapToolsRowMoveUp','jsplusBootstrapToolsRowMoveDown','jsplusBootstrapToolsColEdit','jsplusBootstrapToolsColAdd','jsplusBootstrapToolsColAddBefore','jsplusBootstrapToolsColAddAfter','jsplusBootstrapToolsColDelete','jsplusBootstrapToolsColMoveLeft','jsplusBootstrapToolsColMoveRight'] }
];



	

	config.extraPlugins = 'colorbutton,mediaembed,tabletools,justify,stylesheetparser,forms,font,filebrowser,image,sourcedialog,indent,removeformat,indentblock,jsplusInclude,jsplusBootstrapTools';

config.jsplusInclude = {
    framework: "b3" // or "b4", "f5", "f6", "f6x"
  }



	// Remove some buttons, provided by the standard plugins, which we don't

	// need to have in the Standard(s) toolbar.

	config.removeButtons = 'Strike,Underline,Subscript,Superscript';

	

	// Use the classes 'AlignLeft', 'AlignCenter', 'AlignRight', 'AlignJustify'

//config.justifyClasses = [ 'AlignLeft', 'AlignCenter', 'AlignRight', 'AlignJustify' ];



	// Make dialogs simpler.

	config.removeDialogTabs = 'image:Upload;image:advanced;link:upload;link:advanced';

	

	config.uiColor = '#9AB8F3';

	config.height = '450';

	config.width = '1200';

	

		

	// Sets editor to use the sites CSS file when displaying page in editor

	config.contentsCss = '../../cke_styles.css';

	

	config.stylesSet = [];

	//config.stylesSet = 'default:site-styles.js';

	//config.stylesSet = 'default:/edit/ckeditor_4_3/site-styles.js';

	

	// Only add rules for <div> elements.

	//config.stylesheetParser_validSelectors = /\^(div)\.\w+/;

	

	// Controls what items display in the Format drop-down list

	config.format_tags = 'p;h1;h2;div;h3';

	config.filebrowserBrowseUrl = '/edit/ckeditor_4_3/ckfinder/ckfinder.html';
     config.filebrowserImageBrowseUrl = '/edit/ckeditor_4_3/ckfinder/ckfinder.html?type=Images';
     config.filebrowserUploadUrl = '/edit/ckeditor_4_3/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
     config.filebrowserImageUploadUrl = '/edit/ckeditor_4_3/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';

};
