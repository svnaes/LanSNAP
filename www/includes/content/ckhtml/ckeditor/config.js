/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.toolbar = 'Pico';

	config.toolbar_Pico =[['Source', 'Maximize'],['Cut','Copy','Paste','PasteText','PasteFromWord','-','SpellChecker','Scayt'],['Find','Replace','-','SelectAll','RemoveFormat'],['Image','Table','HorizontalRule','SpecialChar','PageBreak'],'/',['Bold','Italic','Underline','Strike'],['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Link','Unlink','Anchor'],'/',['Format','Font','FontSize'],['TextColor','BGColor'],['Undo','Redo']];
	
	config.filebrowserBrowseUrl = url('includes/uploader/browse.php?');
	config.filebrowserImageBrowseUrl  = url('includes/uploader/browse.php?mode=image');
	
	config.removePlugins = 'resize,elementspath';
	config.height = 300;
};