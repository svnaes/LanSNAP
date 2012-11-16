<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$file_id = $_GET['id'];
$download_files = DB_PREFIX . 'download_files';

$html = $db->result('SELECT `html_description` FROM `'.$download_files.'` WHERE `file_id`=?', $file_id);


?>
<form method="post" action="<?=$body->url('includes/content/downloads/submit.php')?>" onsubmit="DL_HTML_Submit(this); return false">
<input type="hidden" name="page_action" value="update_html_desc" />
<input type="hidden" name="file_id" value="<?=$file_id?>" />
<input type="hidden" name="html_description" value="" />
<textarea id="dl_html_desc"><?=$html?></textarea><br />
<input type="submit" value="Update" />
</form>