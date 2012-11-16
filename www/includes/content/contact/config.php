<?php
$options = array();
$options['title'] = 'Contact Form Builder'; // what you call your content
$options['description'] = 'Build advanced contact forms with ease<br />!!! The view setting on this should always be "Always Same" !!!'; // a brief description of what your content does, any restrictions, etc
$options['content_file'] = 'view.php'; // file that will be called when executing/displaying REQUIRED
$options['edit_file'] = 'edit.php'; // file that will be called when editing the content
$options['static_js'] = ''; // file that will always be included into javascript when the component is called for
$options['static_css'] = 'style.css'; // same as javascript, only for CSS
$options['admin_js'] = 'admin.js'; // same as static, but if logged in as moderator or higher
$options['admin_css'] = 'admin.css'; // same as static, but if logged in as moderator or higher
$options['delete_file'] = ''; // file that will be called in addition to removing the component from the content-links table
$options['install_file'] = 'CF_CloseForm();'; // file that will be called after initally installing the component (good time to gather required information for your component, or create an SQL table if needed)
$options['on_edit_load'] = 'CF_LoadForm();'; // javascript code that will happen after load is done
$options['on_ap_close'] = 'CF_CloseForm();'; // javascript code that will happen when the admin panel is closed

$options['edit_options'] = array(
	array(
		'container'=>'cf_fields',
		'link_text'=>'Fields',
		'inc_file'=>'fields.php',
		'access'=>3
	),
	array(
		'container'=>'cf_history',
		'link_text'=>'History',
		'inc_file'=>'history.php',
		'access'=>3
	),
); // for custom options while editing


?>