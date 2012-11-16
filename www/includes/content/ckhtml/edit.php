<?php
$content = $db->result('SELECT `content` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
if ($content == FALSE) { $content = ''; }
//$content = str_replace("<br />", "", $content);
?>
<form method="post" action="<?=$body->url('includes/content/ckhtml/submit.php')?>" onsubmit="CKT_Submit(this); return false;">
<textarea id="ck_text" style="display: none; width: 1px; height: 1px; overflow: hidden;"><?=$content?></textarea>
<input type="hidden" name="instance_id" value="<?=$instance_id?>" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="ck_html" value="" />
<input type="submit" value="Update" />
</form>