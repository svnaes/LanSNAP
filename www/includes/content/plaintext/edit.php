<?php
$content = $db->result('SELECT `content` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
if ($content == FALSE) { $content = ''; }
$content = str_replace("<br />", "", $content);
?>
<div id="plaintext">
<form method="post" action="<?=$body->url('includes/content/plaintext/submit.php')?>" onsubmit="PT_Submit(this); return false;">
<input type="hidden" name="instance_id" value="<?=$instance_id?>" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<textarea name="content"><?=$content?></textarea><br />
<input type="submit" value="Update" />
</form>
</div>