<?php

if ($_GET['reload'] == 1)
{
	chdir('../../../');
	require_once('core.php');
	$component_id = $_GET['component_id'];
}

if ((!defined('USER_ACCESS')) or (USER_ACCESS < 3)) { exit(); }

require_once('includes/content/tournaments/database.php');

$tourny_table = DB_PREFIX . 'lantourny_list';

if ( (is_numeric($_GET['edit_id'])) and ($_GET['edit_id'] != 0) )
{
	$id          = $_GET['edit_id'];
	$page_action = 'edit_tourny';
	$extra       = '<input type="hidden" name="id" value="'.$id.'" />';
	$values      = $db->assoc('SELECT * FROM `'.$tourny_table.'` WHERE `id`=?', $id);
	$title       = 'Edit Tournament';
}
else
{
	$page_action = 'add_tourny';
	$extra       = '';
	$values      = array();
	$title       = 'Add Tournament';
}

$output = '';

if ($page_action == 'add_tourny')
{
	
	$all_tournys = $db->force_multi_assoc('SELECT * FROM `'.$tourny_table.'` WHERE `component_id`=? ORDER BY `name` ASC', $component_id);
	if (is_array($all_tournys))
	{
		$output .= '<h3>Tournament List</h3>';
		$output .= '<table class="admin_list" cellpadding="0" cellspacing="0" border="0">';
		$output .= '<tr><th>Name</th><th>Actions</th></tr>';
		
		$counter = 0;
		foreach ($all_tournys as $tourny)
		{
			$edit   = ($tourny['status'] == 0) ? '<img src="'.$body->url('includes/icons/edit.png').'" class="icon click" onclick="LT_EditTourny('.$component_id.', '.$tourny['id'].')" />' : '';
			$delete = ($tourny['status'] == 0) ? '<img src="'.$body->url('includes/icons/delete.png').'" class="icon click" onclick="LT_DeleteTourny('.$component_id.', '.$tourny['id'].')" />' : '';
		
			$class = ($counter % 2 == 0 ) ? 'a' : 'b'; $counter++;
			$output .= '<tr>';
			$output .= '<td>'.$tourny['name'].'</td>';
			$output .= '<td>'.$edit.$delete.'</td>';
			$output .= '</tr>';
		}
		
		$output .= '</table>';
	}
}
?>
<div class="ap_overflow">
<?=$output?>

<h3><?=$title?></h3>

<form method="post" style="height: auto" action="<?=$body->url('includes/content/tournaments/submit.php')?>" onsubmit="LT_Submit(this); return false">
<input type="hidden" name="page_action" value="<?=$page_action?>" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<?=$extra?>
<table class="admin_list" cellpadding="0" cellspacing="0" border="0">
<tr class="b">
	<td>Name</td>
	<td><input type="text" name="name" value="<?=$values['name']?>" /></td>
</tr>
<tr class="a">
	<td>Team or Single</td>
	<td>
		<input type="radio" name="team_or_single" value="0" <?=($values['team_or_single']==0) ? 'checked="checked"' : ''?> /> Team<br />
		<input type="radio" name="team_or_single" value="1" <?=($values['team_or_single']==1) ? 'checked="checked"' : ''?> /> Single
	</td>
</tr>
<tr class="b">
	<td>Elimination</td>
	<td>
		<input type="radio" name="elimination" value="0" <?=($values['elimination']==0) ? 'checked="checked"' : ''?> /> Single<br />
		<input type="radio" name="elimination" value="1" <?=($values['elimination']==1) ? 'checked="checked"' : ''?> /> Double
	</td>
</tr>
<tr class="a">
	<td>Max Teams</td>
	<td><input type="text" name="max_teams" value="<?=$values['max_teams']?>" /></td>
</tr>
<tr class="b">
	<td>Team Size</td>
	<td><input type="text" name="team_size" value="<?=$values['team_size']?>" /></td>
</tr>
<tr class="a">
	<td>Time</td>
	<td><input type="text" name="time" value="<?=$values['time']?>" /></td>
</tr>
<tr class="b">
	<td>Description</td>
	<td><textarea class="ap_textarea" name="description"><?=$values['description']?></textarea></td>
</tr>
<tr class="a">
	<td>Rules</td>
	<td><textarea class="ap_textarea" name="rules"><?=$values['rules']?></textarea></td>
</tr>
</table>

<input type="submit" value="Save" />
</form>
</div>