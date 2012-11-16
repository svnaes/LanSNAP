<?php

if ($_GET['reload'] == 1)
{
	chdir('../../../');
	$component_id = $_GET['component_id'];
	require_once('core.php');
}

$contact_table = DB_PREFIX . 'pico_contact_form';
$history_table = DB_PREFIX . 'pico_contact_history';

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 0) ) { exit(); }

?>
<div class="ap_overflow">
<h3>Contact History</h3>
<?php

$history_table  = DB_PREFIX . 'pico_contact_history';

$output = '';
$entries = $db->force_multi_assoc('SELECT * FROM `'.$history_table.'` WHERE `component_id`=? ORDER BY `timestamp` DESC', $component_id);

$counter = 0;

if ( (is_array($entries)) and (sizeof($entries) > 0) )
{
	$output .= '<table border="0" cellpadding="2" cellspacing="1" class="contact_history">';
	$output .= '<tr><th>Date</th><th>Information</th><th>Attachments</th></tr>';
	foreach ($entries as $entry)
	{
		$data     = unserialize($entry['log_data']);
		$entry_id = $entry['entry_id'];
		$info = '';
		foreach ($data as $key=>$val)
		{
			$info .= '<span class="bold">'.$key . ':</span> ' . $val . '<br />';
		}
		
		$id = 'toggle_'.$component_id.'_'.$counter;
		$class = ($counter % 2 == 0) ? 'a': 'b';
		$counter++;
		
		$attachments = unserialize($entry['attachments']);
		if (!is_array($attachments)) { $attachments = array(); }
		
		$a_text = '';
		
		$files = array_keys($attachments);
		if (sizeof($files) > 0)
		{
			foreach ($files as $filename)
			{
				$a_text .= '<div><a href="'.$body->url('includes/content/contact/download.php?entry_id='.$entry_id.'&filename='.urlencode($filename)).'">'.$filename.'</a></div>';
			}
		}
		
		
		$output .= '<tr class="'.$class.'"><td width="75">'.date('h:i m/d/y', $entry['timestamp']).'</td><td><div class="click" onclick="CF_Toggle(\''.$id.'\')">Show/Hide</div><div id="'.$id.'" style="display: none">'.$info.'</div></td><td width="100">'.$a_text.'</td></tr>';
		
	}
	$output .= '</table>';
}
echo $output;
?>
<p class="click" onclick="CF_DeleteHistory(<?=$component_id?>)">Delete History</p>
</div>