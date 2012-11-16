<?php
$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
$fields = unserialize($fields);
if (!is_array($fields)) { $fields = array(); }

$field_text = '';
$counter = 0;

if (isset($edit_field))
{
	$field_id    = $edit_field;
	$page_action = 'edit_field_post';
}
else
{
	$field_id    = -1;
	$page_action = 'add_field';
	$field_data  = array();
}

if (sizeof($fields) > 0)
{
	foreach ($fields as $field)
	{
		if ($counter == $field_id)
		{
			$field_data = $field;
		}
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'"         class="icon click" onclick="CF_EditField('.$counter.', '.$component_id.')" title="Edit" />';
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'"       class="icon click" onclick="CF_DeleteField('.$counter.', '.$component_id.')" title="Delete" />';
		$up     = '<img src="'.$body->url('includes/icons/arrow-up.png').'"     class="icon click" onclick="CF_MoveField('.$counter.', '.$component_id.', \'up\')" title="Up" />';
		$down   = '<img src="'.$body->url('includes/icons/arrow-down.png').'"   class="icon click" onclick="CF_MoveField('.$counter.', '.$component_id.', \'down\')" title="Down" />';
		$field_text .= '<div>'.$edit.$delete.$up.$down . ' ' . $field['name'] . '</div>';
		$counter++;
	}
}


?>
<table border="0" cellpadding="0" cellspacing="1" style="height: 100%; width: 100%">
<tr>
	<td valign="top">
		<form id="cf_field_form" method="post" action="<?=$body->url('includes/content/contact/submit.php')?>" onsubmit="CF_AddField(this); return false">
		<input type="hidden" name="page_action"  value="<?=$page_action?>" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<input type="hidden" name="field_id"     value="<?=$field_id?>" />
		<table border="0" cellpadding="2" cellspacing="1">
		<tr>
			<td class="bold">Field Name</td>
			<td><input type="text" name="field[name]" class="ap_text" value="<?=$field_data['name']?>" /></td>
		</tr>
		<tr>
			<td class="bold">Field Caption / Description</td>
			<td><input type="text" name="field[caption]" class="ap_text" value="<?=$field_data['caption']?>" /></td>
		</tr>
		<tr>
			<td class="bold">Required?</td>
			<td><input type="checkbox" name="field[required]" value="required" <?=($field_data['required'] == 'required') ? 'checked="checked"' : ''?> /></td>
		</tr>
		<tr>
			<td class="bold">Pattern</td>
			<td>
				<select name="field[pattern]">
					<option <?=($field_data['pattern'] == '') ? 'selected="selected"' : ''?>              value="">None</option>
					<option <?=($field_data['pattern'] == 'email') ? 'selected="selected"' : ''?>         value="email">E-Mail</option>
					<option <?=($field_data['pattern'] == 'alpha') ? 'selected="selected"' : ''?>         value="alpha">Alpha</option>
					<option <?=($field_data['pattern'] == 'numeric') ? 'selected="selected"' : ''?>       value="numeric">Numeric</option>
					<option <?=($field_data['pattern'] == 'alpha_numeric') ? 'selected="selected"' : ''?> value="alpha_numeric">Alpha-Numeric</option>
					<option <?=($field_data['pattern'] == 'phone') ? 'selected="selected"' : ''?>         value="phone">Phone</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="bold">Type</td>
			<td>
				<select name="field[type]">
					<option <?=($field_data['type'] == 'text') ? 'selected="selected"' : ''?>       value="text">Text - Single Line</option>
					<option <?=($field_data['type'] == 'textarea') ? 'selected="selected"' : ''?>   value="textarea">Text - Multi-Line</option>
					<option <?=($field_data['type'] == 'select') ? 'selected="selected"' : ''?>     value="select">Choice List</option>
					<option <?=($field_data['type'] == 'check_list') ? 'selected="selected"' : ''?> value="check_list">Checkbox List</option>
					<option <?=($field_data['type'] == 'checkbox') ? 'selected="selected"' : ''?>   value="checkbox">Single Checkbox</option>
					<option <?=($field_data['type'] == 'file') ? 'selected="selected"' : ''?>       value="file">File Attachment</option>
					<option <?=($field_data['type'] == 'dir_dropdown') ? 'selected="selected"' : ''?> value="dir_dropdown">Directory Dropdown</option>
				</select>
			</td>
		</tr>
		<?php
		if ($field_data['type'] == 'dir_dropdown')
		{
			// get directories
			$directories = $db->force_multi_assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `folder`=?', 'directory');
			
			$dd_dropdown = '<select name="field[directory_source]" onchange="CF_LoadDirectyFields('.$component_id.', this.value)">';
			if (is_array($directories))
			{
				foreach ($directories as $directory)
				{
					$selected = ($field_data['directory_source'] == $directory['component_id']) ? 'selected="selected"' : '';
					$dd_dropdown .= '<option value="'.$directory['component_id'].'" '.$selected.'>'.$directory['description'].'</option>';
				}
			}
			$dd_dropdown .= '</select>';
			
			$extra = (is_numeric($field_data['directory_source'])) ? CF_GetDirectoryFields($component_id, $field_data['directory_source']) : 'Please choose a directory';
			
			echo '<tr><td class="bold">Directory Source</td><td>'.$dd_dropdown.'</td></tr>';
			echo '<tr><td class="bold">Directory Field</td><td><span id="cf-directory-field">'.$extra.'</span></td></tr>';
			
			// fields
			
		}
		?>
		<tr>
			<td class="bold">Options/Fields</td>
			<td>
				<textarea class="ap_textarea" name="field[options]"><?php if (is_array($field_data['options'])) { echo implode("\n", $field_data['options']); }?></textarea>
			</td>
		</tr>
		</table>
		<input type="submit" value="Update" />
		</form>
	</td>
	<td valign="top" width="270" height="100%">
		<div id="cf_fieldlist">
			<?=$field_text?>
		</div>
	</td>
</tr>
</table>