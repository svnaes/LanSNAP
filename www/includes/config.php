<?php

$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'config.php') { echo 'You cannot access this file directly'; exit(); }

$config = array();
// database settings

$config['dbhost']= "localhost";
$config['dbname'] = "";
$config['dbuser'] = "";
$config['dbpass'] = "";
$config['dbprefix'] = "";
$config['admin_email'] = 'info@massivelan.com';
$config['admin_from']  = 'info@massivelan.com';

// domain settings

$config['domain'] = 'mlp.lan';
$config['domain_path'] = '/';
$config['install_path'] = '/local/lansnap/www/';
define('SITE_TITLE', 'MLP Lan Site');
?>