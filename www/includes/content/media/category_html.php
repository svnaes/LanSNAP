<?php
$category_id = $_REQUEST['category_id'];
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';

if ($_POST['page_action'] == 'update_category_html')
{
	$html = $_POST['html'];
	$db->run('UPDATE `'.$media_categories.'` SET `html`=? WHERE `category_id`=?', $html, $category_id);
	echo $db->query . "\n" . $db->error;
	exit();
}

$html = $db->result('SELECT `html` FROM `'.$media_categories.'` WHERE `category_id`=?', $category_id);

?>
<div class="click" onclick="MG_DestroyCK(); MG_ShowCategories()">[back]</div>
<form method="post" action="<?=$body->url('includes/content/media/category_html.php')?>" onsubmit="MG_UpdateCategoryHTML(this); return false">

<input type="hidden" name="category_id" value="<?=$category_id?>" />
<input type="hidden" name="html" value="" />
<input type="hidden" name="page_action" value="update_category_html" />
<textarea id="category_html"><?=$html?></textarea>
<br />
<input type="submit" value="Update" />
</form>