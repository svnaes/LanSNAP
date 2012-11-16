<?php
$links = $db->force_multi_assoc('SELECT * FROM `'.DB_LINKS.'` ORDER BY `name` ASC');

if ( ($links != FALSE) and (sizeof($links) > 0) )
{
	
	?>
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<th>Link Name</th>
	<th>URL</th>
	<th>Target</th>
	<th>Caption</th>
	<th>Actions</th>
</tr>
	<?php
	foreach ($links as $link)
	{
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'" onclick="LM_EditLink('.$link['link_id'].')" class="click icon" title="edit" />';
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'" onclick="LM_DeleteLink('.$link['link_id'].')" class="click icon" title="delete" />';
	?>
<tr>
	<td><?=$link['name']?></td>
	<td><?=$link['url']?></td>
	<td><?=$link['target']?></td>
	<td><?=$link['caption']?></td>
	<td><?=$edit.$delete?></td>
</tr>
	<?php
	}
	
	echo '</table>';
}

if (is_numeric($_GET['edit_link']))
{
	$link_id   = $_GET['edit_link'];
	$link_info = $db->assoc('SELECT * FROM `'.DB_LINKS.'` WHERE `link_id`=?', $link_id);
	$action    = 'edit_link';
	$title     = 'Edit Link';
}
else
{
	$link_info = array();
	$action    = 'add_link';
	$title     = 'Add New Link';
	$link_id   = 0;
}

?>
<form method="post" id="lm_linkform" action="<?=$body->url('includes/content/linkmenu/submit.php')?>" onsubmit="LM_AddLink(this); return false">
<input type="hidden" name="page_action" value="<?=$action?>" />
<div class="ap_title" id="lc_title"><?=$title?></div>
<input type="hidden" name="link_id" value="<?=$link_id?>" />
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td class="bold">Link URL</td>
	<td><input type="text" name="url" class="ap_text" value="<?=$link_info['url']?>" /></td>
</tr>
<tr>
	<td class="bold">Name</td>
	<td><input type="text" name="name" class="ap_text" value="<?=$link_info['name']?>" /></td>
</tr>
<tr>
	<td class="bold">Target (optional)</td>
	<td><input type="text" name="target" class="ap_text" value="<?=$link_info['target']?>" /></td>
</tr>
<tr>
	<td class="bold">Caption (optional)</td>
	<td><input type="text" name="caption" class="ap_text" value="<?=$link_info['caption']?>" /></td>
</tr>
</table>
<input type="submit" value="Continue" />
</form>