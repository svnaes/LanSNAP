<?php
if (!isset($component_id))
{
	$component_id = $_GET['component_id'];
	$instance_id  = $_GET['instance_id'];
	
	chdir('../../../');
	require_once('core.php');
	if (USER_ACCESS < 3) { exit(); }
}

$faq_table = DB_PREFIX . 'faq_data';

if (isset($_GET['edit']))
{
	$faq_id   = $_GET['edit'];
	$action   = 'edit_faq';
	$p_action = 'Edit';
	$extra    = '<input type="hidden" name="faq_id" value="'.$faq_id.'" />';
	$info     = $db->assoc('SELECT * FROM `'.$faq_table.'` WHERE `faq_id`=?', $faq_id);
}
else
{
	$action   = 'add_faq';
	$p_action = 'Add';
	$extra    = '';
	$info     = array();
}



$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$faq_table` (
`faq_id` bigint(11) AUTO_INCREMENT,
`instance_id` varchar(32) not null,
`question` varchar(255) not null,
`answer` text,
`position` int(9) not null,
PRIMARY KEY (`faq_id`)
);
SQL
);
?>
<div class="ap_overflow">
<h3><?=$p_action?> a topic</h3>
<form id="faq_form" method="post" action="<?=$body->url('includes/content/faq/submit.php')?>" onsubmit="FAQ_Submit(this); return false">
<input type="hidden" id="instance_id"  name="instance_id" value="<?=$instance_id?>" />
<input type="hidden" id="component_id" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action" value="<?=$action?>" />
<?=$extra?>
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td>Question</td>
	<td><input type="text" name="question" value="<?=$info['question']?>" /></td>
</tr>
<tr>
	<td>Answer</td>
	<td><textarea name="answer"><?=$info['answer']?></textarea></td>
</tr>
</table>
<input type="submit" value="<?=$p_action?>" name="submitbtn" />
</form>

<h3>Current Topics</h3>
<table border="1" cellpadding="2" cellspacing="0">
<tr>
	<th>Question</th>
	<th>Actions</th>
</tr>
<?php
$faqs = $db->force_multi_assoc('SELECT * FROM `'.$faq_table.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
if ( (is_array($faqs)) and (sizeof($faqs) >0) )
{
	foreach ($faqs as $faq)
	{
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'" onclick="FAQ_Edit('.$faq['faq_id'].')" class="click icon" title="edit" />';
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'" onclick="FAQ_Delete('.$faq['faq_id'].')" class="click icon" title="delete" />';
		$up     = '<img src="'.$body->url('includes/icons/arrow-up.png').'" onclick="FAQ_Move('.$faq['faq_id'].', \'up\')" class="click icon" title="up" />';
		$down   = '<img src="'.$body->url('includes/icons/arrow-down.png').'" onclick="FAQ_Move('.$faq['faq_id'].', \'down\')" class="click icon" title="down" />';

	?>
<tr>
	<td><?=$faq['question']?></td>
	<td><?=$edit.$delete.$up.$down?></td>
</tr>
	<?php
	}
}
?>
</table>
</div>