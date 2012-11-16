<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }
$profile_list = DB_PREFIX . 'user_profile_list';
$profile_fields = DB_PREFIX . 'user_profile_fields';

// get all the groups that use this profile.

$profile_id  = $_GET['profile_id'];
$group_table = DB_PREFIX . 'pico_groups';

$groups = $db->force_multi_assoc('SELECT * FROM `'.$group_table.'` WHERE `profile_id`=?', $profile_id);
$profile_groups = array();

if (is_array($groups))
{
	foreach ($groups as $g)
	{
		$profile_groups[] = $g['name'];
	}
}

if (sizeof($profile_groups) > 0)
{
	$blurb = '<p>User Groups using this profile: <span class="bold">'.implode(', ', $profile_groups).'</span></p>';
}
else
{
	$blurb = '<p class="bold">There are currently no User Groups using this profile</p>';
}

// get all the fields
$field_list = $db->force_multi_assoc('SELECT * FROM `'.$profile_fields.'` WHERE `profile_id`=? AND `display`=? ORDER BY `position` ASC', $profile_id, 1);

$edit_mode = ($_GET['edit'] == 0) ? FALSE : TRUE;

if (is_array($field_list))
{
	$foutput .= '<table border="0" cellpadding="0" cellspacing="0" class="admin_list">';
	$foutput .= '<tr>
		<th>Name</th>
		<th>Type</th>
		<th>Required</th>
		<th>Pattern</th>
		<th>Actions</th>
	</tr>';
	$counter = 0;
	foreach ($field_list as $field)
	{
		$class    = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
		$required = ($field['required'] == 1) ? 'Yes' : 'No';
		
		$edit   = '<img hspace="1" src="'.$body->url('includes/icons/edit.png').'" title="Edit" class="icon click" onclick="Pico_UserProfileFields('.$profile_id.', '.$field['field_id'].')" />';
		$delete = '<img hspace="1" src="'.$body->url('includes/icons/delete.png').'" title="Delete" class="icon click" onclick="Pico_UserProfileDeleteField('.$profile_id.', '.$field['field_id'].')" />';
		$up     = '<img hspace="1" src="'.$body->url('includes/icons/arrow-up.png').'" title="Up" class="icon click" onclick="Pico_UserProfileMoveField('.$profile_id.', '.$field['field_id'].', \'up\')" />';
		$down   = '<img hspace="1" src="'.$body->url('includes/icons/arrow-down.png').'" title="Down" class="icon click" onclick="Pico_UserProfileMoveField('.$profile_id.', '.$field['field_id'].', \'down\')" />';
		
		$foutput .= '<tr class="'.$class.'">
			<td>'.$field['name'].'</td>
			<td>'.$field['type'].'</td>
			<td>'.$required.'</td>
			<td>'.$field['pattern'].'</td>
			<td>'.$edit.$delete.$up.$down.'</td>
		</tr>';
	}
	$foutput .= '</table>';
}

echo '<div class="ap_overflow">';

if (!$edit_mode)
{
	echo <<<HTML
	<p>Here you can add fields required for each profile. When a user signs up in a group that contains this profile, they will be required 
	to fill out the following information based on your settings here.</p>
	$blurb

	<h3>Fields</h3>
	$foutput

	<hr />
HTML;

	$title      = 'Add a Field';
	$action     = 'add_profile_field';
	$field_info = array();
	$back       = '<p class="click" onclick="Pico_UserProfiles()">[Back]</p>';
	$extra      = '';
}
else
{
	$title      = 'Edit Field';
	$action     = 'edit_profile_field';
	$field_info = $db->assoc('SELECT * FROM `'.$profile_fields.'` WHERE `field_id`=?', $_GET['edit']);
	$back       = '<p class="click" onclick="Pico_UserProfileFields('.$profile_id.')">[Back]</p>';
	$extra      = '<input type="hidden" name="field_id" value="'.$_GET['edit'].'" />';
	
	//echo '<pre>'.print_r($field_info, true).'</pre>';
}
?>
<h3><?=$title?></h3>

<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_AddProfileField(this); return false">
<input type="hidden" name="ap_action" value="<?=$action?>" />
<input type="hidden" name="profile_id" value="<?=$profile_id?>" />
<?=$extra?>
<table border="0" cellpadding="0" cellspacing="0" class="admin_list">
<tr>
	<td>Field Name</td>
	<td><input type="text" name="field_name" value="<?=$field_info['name']?>" /></td>
</tr>
<tr>
	<td>Field Type</td>
	<td><?php
	if ($edit_mode)
	{
		echo $field_info['type'];
	}
	else
	{
		echo Pico_GroupFieldType('field_type');
	}
	?></td>
</tr>
<tr>
	<td>Caption</td>
	<td><input type="text" name="field_caption" value="<?=$field_info['caption']?>" /></td>
</tr>
<tr>
	<td>Required</td>
	<td><input type="checkbox" name="field_required" value="1" <?=($field_info['required'] == '1')?'checked="checked"' : ''?> /></td>
</tr>
<tr>
	<td>Pattern</td>
	<td><?=Pico_GroupFieldPattern('field_pattern', $field_info['pattern'])?></td>
</tr>
<tr>
	<td>Billing Field</td>
	<td><?=Pico_GroupBillingField('field_billing', $field_info['billing'])?></td>
</tr>
<tr>
	<td>Options</td>
	<td><textarea class="ap_textarea" name="field_options"><?=$field_info['options']?></textarea></td>
</tr>
</table>
<input type="submit" value="Continue" />
</form>

<?=$back?>
</div>