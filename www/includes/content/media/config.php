<?php
$options = array();
$options['title'] = 'Media Gallery'; // what you call your content
$options['description'] = 'Display images/video/audio in a variety of formats and viewtypes'; // a brief description of what your content does, any restrictions, etc
$options['content_file'] = 'view.php'; // file that will be called when executing/displaying REQUIRED
$options['edit_file'] = 'edit.php'; // file that will be called when editing the content
$options['static_js'] = 'functions.js'; // file that will always be included into javascript when the component is called for
$options['static_css'] = 'gallery.css'; // same as javascript, only for CSS
$options['admin_js'] = 'admin.js'; // same as static, but if logged in as moderator or higher
$options['admin_css'] = 'admin.css'; // same as static, but if logged in as moderator or higher
$options['delete_file'] = ''; // file that will be called in addition to removing the component from the content-links table
$options['install_file'] = 'install.php'; // file that will be called after initally installing the component (good time to gather required information for your component, or create an SQL table if needed)
$options['on_edit_load'] = ''; // javascript code that will happen after load is don
$options['on_ap_close'] = 'MG_Close()'; // javascript code that will happen after load is don
$options['edit_options'] = array(
	array(
		'container'=>'gallery_options',
		'link_text'=>'Gallery Options',
		'inc_file'=>'gallery_options.php',
		'access'=>4
	),
	/*
	array(
		'container'=>'gallery_categories',
		'link_text'=>'Gallery Categories',
		'inc_file'=>'gallery_categories.php',
		'access'=>4
	),*/
); // for custom options while editing
?>