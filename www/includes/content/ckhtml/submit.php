<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$content      = stripslashes($_POST['ck_html']);
$instance_id  = $_POST['instance_id'];
$component_id = $_POST['component_id'];
$result = $db->run('UPDATE `'.DB_CONTENT.'` SET `content`=? WHERE `instance_id`=?', $content, $instance_id);
?>