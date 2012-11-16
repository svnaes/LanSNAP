<?php
$content = $db->result('SELECT `content` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
if ($content == FALSE) { $content = ''; }
$content = htmlspecialchars($content);
?>
<form method="post" action="<?=$body->url('includes/content/phpcode/submit.php')?>" onsubmit="PC_Submit(this); return false">
<textarea class="codepress php linenumbers-on" id="pc_codewin" name="pc_codewin" style="width: 100%; height: 425px" /><?=$content?></textarea>
<input type="hidden" name="instance_id" value="<?=$instance_id?>" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="php_data" value="" />
<input type="submit" value="Update" />
</form>