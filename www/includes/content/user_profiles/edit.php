<?php
$pu_table = DB_PREFIX . 'user_signups';


// get all the signup forms so we can pick one!
$ai = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$ai = unserialize($ai);

if (!is_array($ai)) { $ai = array(); }

$components = $db->force_multi_assoc('SELECT `component_id`, `description` FROM `'.DB_COMPONENT_TABLE.'` WHERE `folder`=?', 'paid_users');

$dd = '<select name="source">';
if ( (is_array($components)) and (sizeof($components) > 0) )
{
	foreach ($components as $row)
	{
		$selected = ($ai['source'] == $row['component_id']) ? 'selected="selected"' : '';
		$dd .= '<option value="'.$row['component_id'].'" '.$selected.'>'.$row['description'].'</option>';
	}
}
$dd .= '</select>';

?>
<h3>Choose Source</h3>
<p>Choose a user registration portal to associate with this component</p>
<form method="post" action="<?=$body->url('includes/content/user_profiles/submit.php')?>" onsubmit="UP_Submit(this); return false">
<?=$dd?><br />

<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action" value="update_details" />
<input type="submit" value="Save" name="submitbtn" />
</form>