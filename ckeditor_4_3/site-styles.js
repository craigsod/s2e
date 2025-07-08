/**
 * Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

// This file contains style definitions that can be used by CKEditor plugins.
//
// The most common use for it is the "stylescombo" plugin, which shows a combo
// in the editor toolbar, containing all styles. Other plugins instead, like
// the div plugin, use a subset of the styles on their feature.
//
// If you don't have plugins that depend on this file, you can simply ignore it.
// Otherwise it is strongly recommended to customize this file to match your
// website requirements and design properly.

CKEDITOR.stylesSet.add( 'default', [
	/* Block Styles */

	// These styles are already available in the "Format" combo ("format" plugin),
	// so they are not needed here by default. You may enable them to avoid
	// placing the "Format" combo in the toolbar, maintaining the same features.
	
	{ name: 'Text',		element: 'p', styles: {'text-align':'left', 'font-family': 'Verdana, Arial, Helvetica, sans-serif','font-size': '12px','font-style': 'normal',	
'line-height': '21px','font-weight': 'normal','color': '#301F0B' }},
	{ name: 'bHEADERXXLG', element: 'p', styles: {'text-align':'left','font-family': 'Verdana, Arial, Helvetica, sans-serif', 'font-size': '22px','font-style': 'normal', 'line-height': '32px', 'font-weight': 'bold',	'color': '#E50278' }},
	/*
	{ name: 'Heading 1',		element: 'h1' },
	{ name: 'Heading 2',		element: 'h2' },
	{ name: 'Heading 3',		element: 'h3' },
	{ name: 'Heading 4',		element: 'h4' },
	{ name: 'Heading 5',		element: 'h5' },
	{ name: 'Heading 6',		element: 'h6' },
	{ name: 'Preformatted Text',element: 'pre' },
	{ name: 'Address',			element: 'address' },
	*/


	/* Inline Styles */

	// These are core styles available as toolbar buttons. You may opt enabling
	// some of them in the Styles combo, removing them from the toolbar.
	// (This requires the "stylescombo" plugin)
	/*
	{ name: 'Strong',			element: 'strong', overrides: 'b' },
	{ name: 'Emphasis',			element: 'em'	, overrides: 'i' },
	{ name: 'Underline',		element: 'u' },
	{ name: 'Strikethrough',	element: 'strike' },
	{ name: 'Subscript',		element: 'sub' },
	{ name: 'Superscript',		element: 'sup' },
	*/

	{ name: 'HEADERXXLG', element: 'span', styles: {'text-align':'left','font-family': 'Verdana, Arial, Helvetica, sans-serif', 'font-size': '22px','font-style': 'normal', 'line-height': '32px', 'font-weight': 'bold',	'color': '#E50278' }},
	{ name: 'HEADERXLG', element: 'span', styles: {'text-align':'left','font-family': 'Verdana, Arial, Helvetica, sans-serif', 'font-size': '18px','font-style': 'normal', 'line-height': '30px', 'font-weight': 'bold',	'color': '#E50278' }},
	{ name: 'HEADER', element: 'span', styles: {'text-align':'left','font-family': 'Verdana, Arial, Helvetica, sans-serif', 'font-size': '13px','font-style': 'normal', 'line-height': '20px', 'font-weight': 'bold',	'color': '#E50278' }},
	{ name: 'HEADER Orange', element: 'span', styles: {'text-align':'left','font-family': 'Verdana, Arial, Helvetica, sans-serif', 'font-size': '13px','font-style': 'normal', 'line-height': '20px', 'font-weight': 'bold',	'color': '#FF5200' }},
	{ name: 'small bodytext', element: 'span', styles: {'text-align':'left', 'font-family': 'Verdana, Arial, Helvetica, sans-serif','font-size': '9px','font-style': 'normal',	
'line-height': '19px','font-weight': 'normal','color': '#301F0B' }},
{ name: 'white bodytext', element: 'span', styles: {'text-align':'left', 'font-family': 'Verdana, Arial, Helvetica, sans-serif','font-size': '12px','font-style': 'normal',	
'line-height': '17px','font-weight': 'normal','color': '#FFFFFF' }},


	/* Object Styles 

	{
		name: 'Styled image (left)',
		element: 'img',
		attributes: { 'class': 'left' }
	},

	{
		name: 'Styled image (right)',
		element: 'img',
		attributes: { 'class': 'right' }
	},

	{
		name: 'Compact table',
		element: 'table',
		attributes: {
			cellpadding: '5',
			cellspacing: '0',
			border: '1',
			bordercolor: '#ccc'
		},
		styles: {
			'border-collapse': 'collapse'
		}
	},

	{ name: 'Borderless Table',		element: 'table',	styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' } },
	{ name: 'Square Bulleted List',	element: 'ul',		styles: { 'list-style-type': 'square' } }
	*/
]);

