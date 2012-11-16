<?php

$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }

$twitter_table = DB_PREFIX . 'twitter_data';
$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$twitter_table` (
	`component_id` BIGINT(10) NOT NULL,
	`tweets` BLOB NULL, 
	`last_updated` BIGINT(11) NOT NULL,
	PRIMARY KEY (`component_id`)
);
SQL
);

?>

<form method="post" action="<?=$body->url('includes/content/twitter/submit.php')?>" onsubmit="TWTR_UpdateOptions(this); return false" />
	<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="page_action" value="update_options" />
	<table border="0" cellpadding="2" cellspacing="1">
	<tr>
		<td>Twitter Name/Search Phrase</td>
		<td><input type="text" name="options[t_name]" value="<?=$options['t_name']?>" /></td>
	</tr>
	<tr>
		<td>Number of Entries</td>
		<td><input type="text" name="options[t_num]" value="<?=$options['t_num']?>" /></td>
	</tr>
	<tr>
		<td>Title</td>
		<td><input type="text" name="options[t_title]" value="<?=htmlspecialchars($options['t_title'])?>" /></td>
	</tr>
	<tr>
		<td>Bottom Link Text</td>
		<td><input type="text" name="options[t_text]" value="<?=htmlspecialchars($options['t_text'])?>" /></td>
	</tr>
	<tr>
		<td>Search?</td>
		<td><input type="checkbox" name="options[t_search]" value="yes" <?=($options['t_search'] == 'yes') ? 'checked="checked"' : ''?> /></td>
	</tr>
	</table>
	<input type="submit" value="Update" name="submitbtn" />
</form>