<?php
if ($_GET['refresh'] == 1)
{
	chdir('../../../');
	require_once('core.php');
	$component_id = $_GET['component_id'];
}

if ( (USER_ACCESS < 3) or (!defined('USER_ACCESS')) ) { exit(); }

$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }

require_once('includes/content/external_newsletter/fields.php');

$lists     = $options['lists'];
$listnames = $options['listnames'];
if (!is_array($lists)) { $lists = array(); }
?>
<div class="ap_overflow">
<form method="post" action="<?=$body->url('includes/content/external_newsletter/submit.php')?>" onsubmit="EN_UpdateOptions(this); return false" style="height: auto" />
	<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="page_action" value="update_options" />
	<table border="0" cellpadding="2" cellspacing="1">
	<tr>
		<td>Newsletter Portal</td>
		<td>
			<select name="options[newsletter_portal]">
				<option <?=($options['newsletter_portal'] == 'mc') ? 'selected="selected"' : ''?> value="mc">Mail Chimp</option>
				<option <?=($options['newsletter_portal'] == 'cc') ? 'selected="selected"' : ''?> value="cc">Constant Contact</option>
				<option <?=($options['newsletter_portal'] == 'ic') ? 'selected="selected"' : ''?> value="ic">iContact</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Layout</td>
		<td>
			<select name="options[layout]">
				<option <?=($options['layout'] == 'full') ? 'selected="selected"' : ''?> value="full">Full</option>
				<option <?=($options['layout'] == 'short') ? 'selected="selected"' : ''?> value="short">Short</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Signup Box Text</td>
		<td>
			<input type="text" name="options[signup_box_text]" value="<?=$options['signup_box_text']?>" />
		</td>
	</tr>
	<tr>
		<td>Submit Button Text</td>
		<td>
			<input type="text" name="options[submit_button_text]" value="<?=$options['submit_button_text']?>" />
		</td>
	</tr>
	<tr>
		<td>Custom Submit Button</td>
		<td>
			<?php
			$upload_path = $body->url('includes/content/external_newsletter/button_upload.php');
			$uploader = new Uploader($upload_path, 'EN_SubmitButton', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'ffffff');
			
			if (strlen($options['submit_button']) > 0)
			{
				$file = 'includes/content/external_newsletter/buttons/'.$options['submit_button'];
				if (is_file($file))
				{
					echo '<img src="'.$body->url($file).'" />';
				}
			}
			
			echo '<p><input type="checkbox" name="remove_button" value="1" /> Remove Uploaded Image</p>';
			echo $uploader->Output();
			echo '<input type="hidden" name="options[submit_button]" id="newsletter_submit_button" value="'.$options['submit_button'].'" />';
			?>
		</td>
	</tr>
<?php
if (!isset($options['newsletter_portal']))
{
	echo '</table><input type="submit" value="Update" name="submitbtn" /></form></div>';
	return;
}
else
{
	// show extra fields
	$selected_fields = $fields[$options['newsletter_portal']];
	foreach ($selected_fields as $key => $info)
	{
		?>
	<tr>
		<td><?=$info['name']?></td>
		<td colspan="2"><div><?=$info['desc']?></div><input type="text" name="options[<?=$key?>]" value="<?=$options[$key]?>" /></td>
	</tr>
		<?php
	}
}

for ($x = 0; $x < sizeof($lists)+1; $x++)
{
	?>
	<tr>
		<td>List Number/Name <?=$x+1?></td>
		<td><input type="text" name="options[lists][]" value="<?=$lists[$x]?>" /></td>
		<td><input type="text" name="options[listnames][]" value="<?=$listnames[$x]?>" /></td>
	</tr>
	<?php
}
?>
	
	</table>
	<input type="submit" value="Update" name="submitbtn" />
</form>
</div>
