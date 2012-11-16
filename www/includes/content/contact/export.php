<?php
chdir('../../../');
require_once('core.php');

if (USER_ACCESS < 3) { exit(); }
require_once('includes/content/contact/export-xls.class.php');

$component_id = $_GET['form_id'];

if (!is_numeric($component_id)) { exit(); }

$contact_table = DB_PREFIX . 'pico_contact_form';
$history_table  = DB_PREFIX . 'pico_contact_history';

$fields   = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
$fields   = unserialize($fields);

$headers = array();
$headers[] = 'Time';

foreach ($fields as $f)
{
	$headers[] = $f['name'];
}

$filename = 'contact-history.xls';

#create an instance of the class
$xls = new ExportXLS($filename);
$xls->addHeader($headers);

array_shift($headers);

// get data
$contact_data = $db->force_multi_assoc('SELECT * FROM `'.$history_table.'` WHERE `component_id`=? ORDER BY `timestamp` DESC', $component_id);

if ( (is_array($contact_data)) and (sizeof($contact_data)> 0) )
{
	foreach ($contact_data as $c)
	{
		$ts = $c['timestamp'];
		
		$values = unserialize($c['log_data']);
		$data = array();
		
		foreach ($headers as $field)
		{
			// this makes sure each field is present
			$data[] = $values[$field];
		}
		
		//echo '<pre>'.print_r($data, TRUE).'</pre>';
		array_unshift($data, date('h:i m/d/y', $ts));
		
		$xls->addRow($data);
	}
}

$xls->sendFile();
?>