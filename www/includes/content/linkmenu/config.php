<?php
$options = array();
$options['title'] = 'Link Menu'; // what you call your content
$options['description'] = 'Single Level links and Menus'; // a brief description of what your content does, any restrictions, etc
$options['content_file'] = 'view.php'; // file that will be called when executing/displaying REQUIRED
$options['edit_file'] = 'edit.php'; // file that will be called when editing the content
$options['static_js'] = ''; // file that will always be included into javascript when the component is called for
$options['static_css'] = ''; // same as javascript, only for CSS
$options['admin_js'] = 'admin.js'; // same as static, but if logged in as moderator or higher
$options['admin_css'] = 'admin.css'; // same as static, but if logged in as moderator or higher
$options['delete_file'] = ''; // file that will be called in addition to removing the component from the content-links table
$options['install_file'] = ''; // file that will be called after initally installing the component (good time to gather required information for your component, or create an SQL table if needed)
$options['edit_options'] = array(
	array(
		'container'=>'external_links',
		'link_text'=>'External Links',
		'inc_file'=>'external_links.php',
		'access'=>3
	),
	array(
		'container'=>'font_options',
		'link_text'=>'Font Options',
		'inc_file'=>'font_options.php',
		'access'=>4
	),
); // for custom options while editing
?>