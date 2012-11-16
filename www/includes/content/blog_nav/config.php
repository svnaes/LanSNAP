<?php
$options = array();
$options['title'] = 'Blog Navigation'; // what you call your content
$options['description'] = 'Blog "Navbar" to help navigate through various blog sections<br />!!! The view setting on this should always be "Always Same" and a blog must exist already for this to work !!!'; // a brief description of what your content does, any restrictions, etc
$options['content_file'] = 'view.php'; // file that will be called when executing/displaying REQUIRED
$options['edit_file'] = 'edit.php'; // file that will be called when editing the content
$options['static_js'] = 'static.js'; // file that will always be included into javascript when the component is called for
$options['static_css'] = ''; // same as javascript, only for CSS
$options['admin_js'] = 'admin.js'; // same as static, but if logged in as moderator or higher
$options['admin_css'] = 'admin.css'; // same as static, but if logged in as moderator or higher
$options['delete_file'] = ''; // file that will be called in addition to removing the component from the content-links table
$options['install_file'] = ''; // file that will be called after initally installing the component (good time to gather required information for your component, or create an SQL table if needed)
$options['on_edit_load'] = ''; // javascript code that will happen after load is don
?>