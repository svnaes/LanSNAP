<?php

function LS_GetSetting($key)
{
	global $db;
	$settings_table = DB_PREFIX . 'lansnap_settings';
	$result = $db->result('SELECT `setting_value` FROM `'.$settings_table.'` WHERE `setting_name`=?', $key);
	if (is_bool($result)) { $result = ''; }
	return $result;
}

function LS_GetIPAddress($ip_group)
{
	global $db;
	$num_addresses = LS_GetSetting('num_addresses');
	$ip_table      = DB_PREFIX . 'lansnap_addresses';
	
	$ranges = array();
	
	if ($ip_group == 1)
	{
		// staff
		$ranges[] = 0;
	}
	elseif ($ip_group == 2)
	{
		// other
		$ranges[] = 1;
		if ($num_addresses >= 1024)
		{
			$ranges[] = 2;
			$ranges[] = 3;
		}
		
		if ($num_address >= 2048)
		{
			$ranges[] = 4;
			$ranges[] = 5;
			$ranges[] = 6;
			$ranges[] = 7;
		}
	}
	
	foreach ($ranges as $range)
	{
		$start = ($range == 0) ? 35 : 1;
		
		for ($x = $start; $x++; $x < 255)
		{
			$ip_address = '10.0.' . $range . '.' . $x;
			// see if this IP is being used
			$check = $db->result('SELECT count(1) FROM `'.$ip_table.'` WHERE `ip_address`=?', $ip_address);
			if ($check == 0)
			{
				return $ip_address;
			}
		}
	}
	
	return FALSE;
}


function LS_BindAllUnusedAddresses($bind_or_unbind = 0)
{
	global $db;
	$num_addresses = LS_GetSetting('num_addresses');
	$ip_table      = DB_PREFIX . 'lansnap_addresses';
	
	$ranges = array();
	$ranges[] = 0;
	$ranges[] = 1;
	
	if ($num_addresses >= 1024)
	{
		$ranges[] = 2;
		$ranges[] = 3;
	}
	
	if ($num_address >= 2048)
	{
		$ranges[] = 4;
		$ranges[] = 5;
		$ranges[] = 6;
		$ranges[] = 7;
	}
	
	for ($x = 0; $x < sizeof($ranges); $x++)
	{
		$start = ($x == 0) ? 35 : 1; // we skip the first 35 ips for the initial range
		$end   = (($x+1) == sizeof($ranges)) ? 254 : 255; // all go to 255 except the last range
		
		for ($y = $start; $y <= $end; $y++)
		{
			$range = $ranges[$x];
			$ip_address = '10.0.' . $range . '.' . $y;
			
			// see if this address is being used
			
			if ($bind_or_unbind == 0)
			{
				$check = $db->result('SELECT count(1) FROM `'.$ip_table.'` WHERE `ip_address`=?', $ip_address);
				if ($check == 0)
				{
					// bind this address
					LS_BindAddress($ip_address);
				}
			}
			else
			{
				LS_UnbindAddress($ip_address);
			}
		}
		
		sleep(1); // delay so we dont overload the box
	}
}

function LS_GetTableLayout($component_id, $table_html = '', $seat_html = '', $show_external_filled = FALSE)
{
	//echo '<pre><xmp>'.print_r($seat_html, true).'</xmp></pre>';
	//echo 'in';
	global $db;
	include('includes/content/lansnap/layout/seat_config.php');

	$layout_table = DB_PREFIX . 'lansnap_layouts';
	$table_table  = DB_PREFIX . 'lansnap_layout_tables';
	$seat_table   = DB_PREFIX . 'lansnap_layout_seats';

	if ($show_external_filled)
	{
		// establish external connection
		$host   = LS_GetSetting('externaldb_host');
		$user   = LS_GetSetting('externaldb_user');
		$pass   = LS_GetSetting('externaldb_pass');
		$dbname = LS_GetSetting('externaldb_name');
		$etable = LS_GetSetting('externaldb_table');
		$column = LS_GetSetting('edb_linked_barcode');
		$user_field = LS_GetSetting('edb_linked_username');
		$external_db = new DataBase($host, $user, $pass, $dbname);
		$linked_seat = LS_GetSetting('edb_linked_seat');
	}

	// get the layout info
	$layout_info = $db->assoc('SELECT * FROM `'.$layout_table.'` WHERE `component_id`=?', $component_id);
	if (!is_array($layout_info))
	{
		echo '<p>Please configure the layout before configuring seats</p>';
		return;
	}

	$layout_id = $layout_info['layout_id'];

	$table_output = '';
	$max_x = 0;
	$max_y = 0;
	$shim_x = 10;
	$shim_y = 10;

	// get all the tables, use inline styles
	$table_list = $db->force_multi_assoc('SELECT * FROM `'.$table_table.'` WHERE `layout_id`=?', $layout_id);
	if (is_array($table_list))
	{
		foreach ($table_list as $table)
		{
			$type      = $table['table_type'];
			$className = (strstr($table['table_type'], 'tall')) ? 'tall' : 'wide';
			$left      = $table['pos_x'];
			$top       = $table['pos_y'];
			$width     = ($seats[$type]['width'] * $scale)-2; // -2 for border
			$height    = ($seats[$type]['height'] * $scale)-2;
			
			$check_width = ($left + $width);
			if ($check_width > $max_x)
			{
				$max_x = $check_width;
			}
			
			$check_height = ($top + $height);
			if ($check_height > $max_y)
			{
				$max_y = $check_height;
			}
			
			$table_output .= '<div class="table '.$className.'" style="left: '.($left+$shim_x).'px; top: '.($top+$shim_y).'px; width: '.$width.'px; height: '.$height.'px" id="table_'.$table['table_id'].'">';
			$t_html = $table_html;
			$t_html = str_replace('TABLE_ID', $table['table_id'], $t_html);
			$table_output .= $t_html;
			
			// add seats
			$seat_list = $db->force_multi_assoc('SELECT * FROM `'.$seat_table.'` WHERE `table_id`=? ORDER BY `table_seat_number` ASC', $table['table_id']);
			if (is_array($seat_list))
			{
				$counter = 0;
				foreach ($seat_list as $seat)
				{
					if ($className == 'tall')
					{
						$s_width  = $width;
						$s_height = (floor($height / sizeof($seat_list)));
						$left     = 0;
						$top      = ($counter * $s_height);
					}
					else
					{
						$s_width  = (floor($width / sizeof($seat_list)));
						$s_height = $height; 
						$left     = ($counter * $s_width);
						$top      = 0;
					}
					
					$avail_class = ($seat['user_id'] == 0) ? 'seat_vacant' : 'seat_taken';
					
					$table_output .= '<div class="seat '.$avail_class.'" id="seat_'.$seat['seat_id'].'" style="width: '.($s_width-2).'px; height: '.($s_height-2).'px; left: '.$left.'px; top: '.$top.'px">';
					$s_html = $seat_html;
					$s_html = str_replace('SEAT_ID', $seat['seat_id'], $s_html);
					$s_html = str_replace('SEAT_NUM', $seat['seat_num'], $s_html);
					$s_html = str_replace('TABLE_ROW', $table['row'], $s_html);

					if ($show_external_filled)
					{
						$seat_name = $table['row'] . '-' . $seat['seat_num'];
						// check the external DB to see if someone is in this seat
						$check = $external_db->result('SELECT count(1) FROM `'.$etable.'` WHERE `'.$linked_seat.'`=? AND `'.$user_field.'`!=?', $seat_name, '');
						if ($check == 1) 
						{
							$uname   = $external_db->result('SELECT `'.$user_field.'` FROM `'.$etable.'` WHERE `'.$linked_seat.'`=?', $seat_name);
							$s_html  = str_replace('USER_SEAT_CLASS', 'pre_seat', $s_html);
							$s_html  = str_replace('USER_SEAT', $uname, $s_html);
						}
						else
						{
							$s_html  = str_replace('USER_SEAT_CLASS', '', $s_html);
							$s_html  = str_replace('USER_SEAT', '', $s_html);
						}
					}
					$table_output .= $s_html;
					$table_output .= '</div>';
					$counter++;
				}
			}
			
			$table_output .= '</div>';
		}
	}

	$output = '<div class="canvas" style="width: '.($max_x+($shim_x*2)).'px; height: '.($max_y+($shim_y*2)).'px">';
	$output .= $table_output;
	$output .= '</div>';
	
	return $output;
}

function LS_GetSeatsDropdown($component_id, $seat_number = '')
{
	global $db;
	
	$layout_table = DB_PREFIX . 'lansnap_layouts';
	$table_table  = DB_PREFIX . 'lansnap_layout_tables';
	$seat_table   = DB_PREFIX . 'lansnap_layout_seats';
	
	$layout_info = $db->assoc('SELECT * FROM `'.$layout_table.'` WHERE `component_id`=?', $component_id);
	$layout_id   = $layout_info['layout_id'];
	$table_list  = $db->force_multi_assoc('SELECT * FROM `'.$table_table.'` WHERE `layout_id`=? ORDER BY RAND()', $layout_id);
	$seat_list   = array();

	if (sizeof($table_list) > 0)
	{
		foreach ($table_list as $table)
		{
			$table_id = $table['table_id'];
			$available_seats = $db->force_multi_assoc('SELECT * FROM `'.$seat_table.'` WHERE `user_id`=? AND `table_id`=?', 0, $table_id);
			if (is_array($available_seats))
			{
				foreach ($available_seats as $seat)
				{
					$seat_id = $seat['seat_id'];
					$seat_list[$seat_id] = $table['row'] . '-' . str_pad($seat['seat_num'], 2, '0', STR_PAD_LEFT);
				}
			}
		}
	}

	$seat_dropdown = '<select name="seat_number" id="seat_number"><option value="">Choose...</option>';
	if (sizeof($seat_list) > 0)
	{
		asort($seat_list);
		foreach ($seat_list as $seat_id=>$label)
		{
			$selected = ($seat_number == $seat_id) ? 'selected="selected"' : '';
			$seat_dropdown .= '<option value="'.$seat_id.'" '.$selected.'>'.$label.'</option>';
		}
	}

	$seat_dropdown .= '</select>';
	
	return $seat_dropdown;
}

function LS_GraphTotalBandwidth()
{
	$filename = 'includes/content/lansnap/graphs/bandwidth.png';
	if ( (!is_file($filename)) or (time() - filemtime($filename) > 60) )
	{
		global $db;
		require_once('includes/pchart/pChart.class');
		require_once('includes/pchart/pData.class');
		
		// gather the dataz
		$traffic_table = DB_PREFIX . 'lansnap_traffic';
		$ip_table      = DB_PREFIX . 'lansnap_addresses';
		
		$last_30_mins = time() - 1800;
		
		$data = $db->force_multi_assoc('SELECT SUM(`bytes_in`) as `bytes_in`, SUM(`bytes_out`) as `bytes_out`, `timestamp` FROM `'.$traffic_table.'` WHERE `timestamp`>=? GROUP BY `timestamp` ORDER BY `timestamp` ASC', $last_30_mins);
		$in   = array();
		$out  = array(); 
		
		echo $db->error;
		
		$max = 0;
		if (is_array($data))
		{
			foreach ($data as $entry)
			{
				$mb_in   = round(($entry['bytes_in'] / 1024), 2); // MB used that minute
				$mb_out  = round(($entry['bytes_out'] / 1024), 2);

				if ($entry['bytes_in'] > $max) { $max = $mb_in; }
				if ($entry['bytes_out'] > $max) { $max = $mb_out; }
				
				$in[] = $mb_in;
				$out[] = $mb_out;
			}
		}
		
		$DataSet = new pData;
		$DataSet->AddPoint($in,"Serie1");
		$DataSet->AddPoint($out,"Serie2");
		
		$DataSet->AddAllSeries();
		//$DataSet->SetAbsciseLabelSerie();
		
		$labels = array();
		for ($y = 30; $y > 0; $y--)
		{
			$labels[] = $y;
		}
		
		$DataSet->AddPoint($labels,"Serie3");
		$DataSet->SetAbsciseLabelSerie("Serie3");
		
		
		$DataSet->SetSerieName("MB Down","Serie1");
		$DataSet->SetSerieName("MB Up","Serie2");
		
		LS_DrawLineGraph($DataSet, $filename, 'Bandwidth Used');
	}
	return $filename . '?rand=' . rand(10000,99999);
}

function LS_GetUserBWByMinutes($ip_address, $minutes)
{
	global $db;
	$traffic_table = DB_PREFIX . 'lansnap_traffic';
	$ago = time() - ($minutes *60);
	$user_bw_entries = $db->force_multi_assoc('SELECT * FROM `'.$traffic_table.'` WHERE `ip_address`=? AND `timestamp` >= ? ORDER BY `timestamp` ASC', $ip_address, $ago);
	
	//echo $db->query . '<br />';
	
	$return = array();
	
	if (is_array($user_bw_entries))
	{
		foreach ($user_bw_entries as $entry)
		{
			$ts = $entry['timestamp'];
			$return[$ts] = array(
				'bytes_in'    => $entry['bytes_in'],
				'bytes_out'   => $entry['bytes_out'],
				'packets_in'  => $entry['packets_in'],
				'packets_out' => $entry['packets_out'],
			);
		}
	}
	
	return $return;
}

function LS_GetUserBWByTimestamp($ip_address)
{
	global $db;
	$traffic_table = DB_PREFIX . 'lansnap_traffic';
	$user_bw_entries = $db->force_multi_assoc('SELECT * FROM `'.$traffic_table.'` WHERE `ip_address`=? ORDER BY `timestamp` ASC', $ip_address);
	$return = array();
	
	if (is_array($user_bw_entries))
	{
		foreach ($user_bw_entries as $entry)
		{
			$ts = $entry['timestamp'];
			$return[$ts] = array(
				'bytes_in'    => $entry['bytes_in'],
				'bytes_out'   => $entry['bytes_out'],
				'packets_in'  => $entry['packets_in'],
				'packets_out' => $entry['packets_out'],
			);
		}
	}
	
	return $return;
}

function LS_GraphTop10($title = 'Top 10 downloaders', $type = 'bytes', $mode = 'in')
{
	$flag = $type . '_' . $mode;
	$filename = 'includes/content/lansnap/graphs/top10'.$flag.'.png';
	
	if ( (!is_file($filename)) or (time() - filemtime($filename) > 60) )
	{
	
		global $db;
		require_once('includes/pchart/pChart.class');
		require_once('includes/pchart/pData.class');
	
		// get top 10 people
		$traffic_table = DB_PREFIX . 'lansnap_traffic';
		$ip_table      = DB_PREFIX . 'lansnap_addresses';
		
		$data = $db->force_multi_assoc('SELECT SUM(`'.$flag.'`) as `'.$flag.'`, `ip_address` FROM `'.$traffic_table.'` GROUP BY `ip_address` ORDER BY `'.$flag.'` DESC LIMIT 10');
		
		// need to get all the possible timestamps
		
		$mins_to_check = 30;
		$x_mins_ago = time() - ($mins_to_check * 60);
		
		$timestamps = $db->force_multi_assoc('SELECT DISTINCT(`timestamp`) as `timestamp` FROM `'.$traffic_table.'` WHERE `timestamp` >=? ORDER BY `timestamp` ASC', $x_mins_ago);
		$ts_list    = array();
		foreach ($timestamps as $entry)
		{
			$ts_list[] = $entry['timestamp'];
		}
		
		$DataSet = new pData;
		
		$x = 1;
		
		foreach ($data as $entry)
		{
			// each IP has its own entry here. we will need to go get each data set and add it to the graph
			$ip = $entry['ip_address'];
			$user_bw = array();
			
			$user_data = LS_GetUserBWByMinutes($ip, $mins_to_check);
			foreach ($ts_list as $ts)
			{
				$bw =  $user_data[$ts][$flag];
				if (!is_numeric($bw)) { $bw = 0; }
				
				if ($type == 'bytes')
				{
					// convert to megs
					$bw = round($bw/1024, 2);
				}
				
				
				$user_bw[] = $bw;
			}
			
			$username = LS_GetUserNameByIP($ip);
			$DataSet->AddPoint($user_bw,"Serie" . $x);
			$DataSet->SetSerieName($ip . ': ' . $username,"Serie" . $x);   
			$x++;
		}
		
		$labels = array();
		for ($y = 30; $y > 0; $y--)
		{
			$labels[] = $y;
		}
		
		$DataSet->AddAllSeries();
		$DataSet->AddPoint($labels,"Serie" . $x);
		$DataSet->SetAbsciseLabelSerie("Serie" . $x);
		
		
		//$DataSet->SetAbsciseLabelSerie();
		
		LS_DrawLineGraph($DataSet, $filename, $title);
	}
	return $filename . '?rand=' . rand(10000,99999);
}

function LS_DrawLineGraph($DataSet, $filename, $title)
{
	$font = 'includes/pchart/Fonts/tahoma.ttf';
	
	$Test = new pChart(700,230);
	$Test->setFontProperties($font,8);   
	$Test->setGraphArea(160,30,680,200);   
	$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);   
	$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);   
	$Test->drawGraphArea(255,255,255,TRUE);
	$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);   
	$Test->drawGrid(4,TRUE,230,230,230,50);

	// Draw the 0 line   
	$Test->setFontProperties($font,6);   
	$Test->drawTreshold(0,143,55,72,TRUE,TRUE);   

	// Draw the line graph
	$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());   
	$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);   

	// Finish the graph   
	$Test->setFontProperties($font,8);   
	$Test->drawLegend(10,35,$DataSet->GetDataDescription(),255,255,255);   
	$Test->setFontProperties($font,10);   
	$Test->drawTitle(60,22,$title,50,50,50,585);   
	
	$Test->Render($filename);
}

function LS_DrawUserBandwidthGraph($ip_address)
{
	global $db;
	$traffic_table = DB_PREFIX . 'lansnap_traffic';
	
	$entries = LS_GetUserBWByTimestamp($ip_address);
	$in  = array();
	$out = array();
	
	// need to get all the possible timestamps
	$timestamps = $db->force_multi_assoc('SELECT DISTINCT(`timestamp`) as `timestamp` FROM `'.$traffic_table.'` ORDER BY `timestamp` ASC');
	$ts_list    = array();
	foreach ($timestamps as $entry)
	{
		$ts_list[] = $entry['timestamp']; // should get the last 30 timestamps
	}
	
	$num_passes = sizeof($ts_list) / 5;
	for ($x = 0; $x < $num_passes; $x++)
	{
		$_in = 0;
		$_out = 0;
		for ($y = 0; $y < 5; $y++)
		{
			$key = ($x*5) + $y;
			$timestamp = $ts_list[$key];
			$entry = $entries[$timestamp];
			// $entry contains a row of that timestamp and any data that was saved during that time.
			$bytes_in  = (is_numeric($entry['bytes_in'])) ? $entry['bytes_in'] : 0;
			$bytes_out = (is_numeric($entry['bytes_out'])) ? $entry['bytes_out'] : 0;
			
			$_in += $bytes_in;
			$_out += $bytes_out;
			
		}
		$time = (6 - $x) * 5;
		$in[] = round($_in/1024,2);
		$out[] = round($_out/1024, 2);
	}
	
	// $in and $out should now have 6 entries, total bytes stored in and out in 5 min increments, draw that graph!
	
	require_once('includes/pchart/pChart.class');
	require_once('includes/pchart/pData.class');
	
	$DataSet = new pData;
	
	$DataSet->AddPoint($in,"Serie1");
	$DataSet->AddPoint($out,"Serie2");
	$DataSet->SetSerieName("MB In","Serie1");
	$DataSet->SetSerieName("MB Out","Serie2");
	
	$DataSet->AddAllSeries();
	
	$DataSet->SetXAxisUnit("m");
	$DataSet->AddPoint(array('30', '25', '20', '15', '10', '5'),"Serie3");
	$DataSet->SetAbsciseLabelSerie('Serie3');
	
	
	$font = 'includes/pchart/Fonts/tahoma.ttf';
	
	$w = 280;
	$h = 150;
	
	$Test = new pChart($w,$h);
	$Test->setFontProperties($font,8);   
	$Test->setGraphArea(40,20,$w-20,$h-30);   
	$Test->drawFilledRoundedRectangle(7,7,$w-8,$h-8,5,240,240,240);   
	$Test->drawRoundedRectangle(5,5,$w-10,$h-10,5,204,204,204);   
	
	//$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);   
	//$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);   
	
	
	
	$Test->drawGraphArea(255,255,255,TRUE);
	$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);   
	$Test->drawGrid(4,TRUE,230,230,230,50);

	// Draw the 0 line   
	$Test->setFontProperties($font,6);   
	$Test->drawTreshold(0,143,55,72,TRUE,TRUE);   

	// Draw the line graph
	$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());   
	$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);   

	// Finish the graph   
	$Test->setFontProperties($font,8);   
	$Test->drawLegend(75,35,$DataSet->GetDataDescription(),255,255,255);   
	$Test->setFontProperties($font,10);   
	//$Test->drawTitle(60,22,'Bandwidth used in MB',50,50,50,585);   
	
	$filename = 'includes/content/lansnap/graphs/bw_'.str_replace('.', '_', $ip_address).'.png';
	$Test->Render($filename);
	return $filename . '?rand='.rand(10000,99999);
}

function LS_GetUserNameByIP($ip_address)
{
	global $db;
	$ip_table   = DB_PREFIX . 'lansnap_addresses';
	$user_table = DB_PREFIX . 'lansnap_users';
	
	$user_id = $db->result('SELECT `user_id` FROM `'.$ip_table.'` WHERE `ip_address`=?',
		$ip_address
	);
	
	if (is_numeric($user_id))
	{
		$username = $db->result('SELECT `username` FROM `'.$user_table.'` WHERE `user_id`=?', $user_id);
	}
	else
	{
		$username = '';
	}
	
	return $username;
}

function LS_GetUserNameByID($user_id)
{
	global $db;
	$user_table = DB_PREFIX . 'lansnap_users';
	$username = $db->result('SELECT `username` FROM `'.$user_table.'` WHERE `user_id`=?', $user_id);
	return $username;
}

function LS_GetUserIDByIP($ip_address)
{
	global $db;
	$ip_table   = DB_PREFIX . 'lansnap_addresses';
	$user_table = DB_PREFIX . 'lansnap_users';
	
	$user_id = $db->result('SELECT `user_id` FROM `'.$ip_table.'` WHERE `ip_address`=?',
		$ip_address
	);
	
	if (!is_numeric($user_id))
	{
		$user_id = 0;
	}

	return $user_id;
}

function LS_DeleteUser($user_id)
{
	global $db;
	
	$user_table     = DB_PREFIX . 'lansnap_users';
	$ip_table       = DB_PREFIX . 'lansnap_addresses';
	$domain_table   = DB_PREFIX . 'lansnap_domains';
	$seat_table     = DB_PREFIX . 'lansnap_layout_seats';
	$traffic_table  = DB_PREFIX . 'lansnap_traffic';
	$survey_results = DB_PREFIX . 'lansnap_survey_results';
	
	// delete from user table
	$db->run('DELETE FROM `'.$user_table.'` WHERE `user_id`=? LIMIT 1', $user_id);
	
	// delete from seating table
	$db->run('UPDATE `'.$seat_table.'` SET `user_id`=? WHERE `user_id`=?', 0, $user_id);
	
	// delete from ip table
	$ip_address = $db->result('SELECT `ip_address` FROM `'.$ip_table.'` WHERE `user_id`=? AND `source`=?', $user_id, 0);
	LS_BindAddress($ip_address); // rebind address
	$db->run('DELETE FROM `'.$ip_table.'` WHERE `user_id`=? AND `source`=? LIMIT 1', $user_id, 0);
	
	// delete from "domain" table
	$db->run('DELETE FROM `'.$domain_table.'` WHERE `rdata`=?', $ip_address);
	
	// delete from survey table
	$db->run('DELETE FROM `'.$survey_results.'` WHERE `user_id`=?', $user_id);
	
	// delete from traffic table
	$db->run('DELETE FROM `'.$traffic_table.'` WHERE `ip_address`=?', $ip_address);
	
	$db->run('UPDATE `'.$ip_table.'` SET `status`=?', 1); // so dhcp rewrites
	
	// UPDATE DNS
	// TODO: configure this
	$dns_db = new DataBase('localhost', 'root', 'jl@0p13!', 'pdns');
	$dns_db->result('DELETE FROM `records` WHERE `content`=?', $ip_address);
}

function LS_LinkedSettingDropdown($fields, $sel, $name)
{
	
	$return = '<select name="'.$name.'">
		<option value=""></option>';
		
	foreach ($fields as $field)
	{
		$name = $field['Field'];
		
		$selected = ($sel == $name) ? 'selected="selected"' : '';
		$return .= '<option value="'.$name.'" '.$selected.'>'.$name.'</option>';
	}
	
	$return .= '</select>';
	return $return;
}

function LS_LinkedTicketsDropdown($fields, $sel, $name)
{
	$return = '<select name="'.$name.'">
		<option value=""></option>';
		
	foreach ($fields as $field)
	{
		$name = $field['ticket_type'];
		
		$selected = ($sel == $name) ? 'selected="selected"' : '';
		$return .= '<option value="'.$name.'" '.$selected.'>'.$name.'</option>';
	}
	
	$return .= '</select>';
	return $return;
}

// functions for binding/unbinding addresses for the system
function LS_BindAddress($ip)
{
	$host = str_replace('.', '_', $ip);
	$netmask = '255.255.252.0';
	$command = <<<TXT
/local/lansnap/bin/LS-bind eth1:$host $ip $netmask up
TXT;
	//$command = "ifconfig eth1:$host $ip netmask  up";
	exec($command);
}

function LS_UnbindAddress($ip)
{
	$host = str_replace('.', '_', $ip);
	$netmask = '255.255.252.0';
	$command = <<<TXT
/local/lansnap/bin/LS-bind-down eth1:$host
TXT;
	//$command = "ifconfig eth1:$host $ip netmask  up";
	exec($command);
}

function LS_GetReadableSeatNumber($seat_id)
{
	global $db;
	$seat_table  = DB_PREFIX . 'lansnap_layout_seats';
	$table_table = DB_PREFIX . 'lansnap_layout_tables';
	
	$seat_num = $db->result('SELECT `seat_num` FROM `'.$seat_table.'` WHERE `seat_id`=?', $seat_id);
	$table_id = $db->result('SELECT `table_id` FROM `'.$seat_table.'` WHERE `seat_id`=?', $seat_id);
	
	$row = $db->result('SELECT `row` FROM `'.$table_table.'` WHERE `table_id`=?', $table_id);
	
	$return = $row . '-' . $seat_num;
	return $return;
}

function LS_GetBarcode($barcode)
{
	define('BC_PATH', 'includes/content/lansnap/barcode/class/');
	require_once('includes/content/lansnap/barcode/defs.inc');
	
	require_once(BC_PATH . 'BCGFont.php');
	require_once(BC_PATH . 'BCGColor.php');
	require_once(BC_PATH . 'BCGDrawing.php'); 

	// Including the barcode technology
	require_once(BC_PATH . 'BCGcode93.barcode.php'); 
	require_once(BC_PATH . 'BCGcode39.barcode.php'); 
	require_once(BC_PATH . 'BCGi25.barcode.php'); 
	require_once(BC_PATH . 'BCGcode11.barcode.php'); 
	
	$color_black = new BCGColor(0, 0, 0);
	$color_white = new BCGColor(255, 255, 255);
	
	$storage_dir = 'includes/content/lansnap/barcode/storage/';
	
	$output_file = $storage_dir . $barcode . '.jpg';
	//if (is_file($output_file)) { unlink($output_file); }
	
	if (!is_file($output_file))
	{
		$code = new BCGcode39();
		//$code->setHeight(20);
		$code->setScale(1); // Resolution
		$code->setThickness(50); // Thickness
		$code->setForegroundColor($color_black); // Color of bars
		$code->setBackgroundColor($color_white); // Color of spaces
		$font = new BCGFont(BC_PATH . 'font/Arial.ttf', 12);
		
		$font_size = ($show_text == TRUE) ? 12 : 0;
		
		$code->setFont($font_size); // Font (or 0)
		$code->parse($barcode); // Text
		
		$drawing = new BCGDrawing($output_file, $color_white);
		$drawing->setBarcode($code);
		$drawing->draw();
		
		$drawing->finish(BCGDrawing::IMG_FORMAT_JPEG);
	}
	return $output_file;
}

?>
