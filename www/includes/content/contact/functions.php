<?php

function draw_text_table ($table) {
    
    // Work out max lengths of each cell

    foreach ($table AS $row) {
        $cell_count = 0;
        foreach ($row AS $key=>$cell) {
            $cell_length = strlen($cell);

            $cell_count++;
            if (!isset($cell_lengths[$key]) || $cell_length > $cell_lengths[$key]) $cell_lengths[$key] = $cell_length;

        }    
    }

    // Build header bar

    $bar = '+';
    $header = '|';
    $i=0;
	
	$header_line = array_shift($table); // get the top line has the header dur
	foreach($header_line as $key=>$val)
	{
		$length = $cell_lengths[$key];
		
		$bar    .= str_pad('', $length+2, '-')."+";
		$header .= ' '.str_pad($val, $length, ' ', STR_PAD_RIGHT) . " |";
	}

	/*
    foreach ($cell_lengths AS $fieldname => $length) {
        $i++;
        $bar .= str_pad('', $length+2, '-')."+";

        $name = $fieldname;
        if (strlen($name) > $length) {
            // crop long headings

            //$name = substr($name, 0, $length-1);
        }
        $header .= ' '.str_pad($name, $length, ' ', STR_PAD_RIGHT) . " |";

    }*/

    $output = '';

    $output .= $bar."\r\n";
    $output .= $header."\r\n";

    $output .= $bar."\r\n";

    // Draw rows

    foreach ($table AS $row) {
        $output .= "|";

        foreach ($row AS $key=>$cell) {
            $output .= ' '.str_pad($cell, $cell_lengths[$key], ' ', STR_PAD_RIGHT) . " |";

        }
        $output .= "\r\n";
    }

    $output .= $bar."\r\n";

    return $output;
}

// processes a request from the contact form, returns TRUE or $error
function CF_Process($post, $fields, $component_id, $settings)
{
	global $db, $body;
	$log_data = array();
	$data     = $post['fields'];
	
	$counter     = 0;
	$message     = '';
	$attachments = array();
	
	foreach ($fields as $field)
	{
		// go thru fields
		$required = ($field['required'] == 'required') ? TRUE : FALSE;
		$value    = $data[$counter];
		
		if ($required)
		{
			if (is_array($value))
			{
				if (sizeof($value) == 0)
				{
					$error .= 'Invalid ' . $field['name'] . '<br />';
				}
			}
			else
			{
				if ( (strlen($value) == 0) and ($field['type'] != 'file') )
				{
					$error .= 'Invalid ' . $field['name'] . '<br />';
				}
			}
		}
		
		if ($field['type'] == 'text')
		{
			$regex = $field['pattern'];
			switch($regex)
			{
				case 'email':
					//$regexp = "/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";
					$regexp = '/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}$/';
					break;
				case 'alpha':
					$regexp = '/[A-z\s]/';
					break;
				case 'numeric':
					$regexp = '/[0-9]/';
					break;
				case 'alpha_numeric':
					$regexp = '/[A-z0-9\s]/';
					break;
				case 'phone':
					$regexp = '/[0-9\-\(\)\s]/';
					break;
				default:
					$regexp = '/.*/';
					break;
			}
			if (preg_match($regexp, $value))
			{
				$valid = TRUE;
				
				if ($regex == 'email')
				{
					$replyTo = $value;
				}
			}
			else
			{
				if  ( (strlen($value) == 0) and ($required == FALSE) )
				{
					$valid = TRUE;
				}
				else
				{
					$valid = FALSE;
				}
			}
			
			if ($valid != FALSE)
			{
				$k = $field['name'];
				$v = $value;
			}
		}
		elseif ($field['type'] == 'textarea')
		{
			$k = $field['name'];
			$v = $value;
		}
		elseif ($field['type'] == 'select')
		{
			$k = $field['name'];
			$v = $value;
			
			$options = $field['options'];
			foreach ($options as $option)
			{
				$len = strlen($v);
				if (substr($option, 0, $len+1) == $value . '|')
				{
					$custom_address = substr($option, $len+1);
				}
			}
		}
		elseif ($field['type'] == 'file')
		{
			$check = $_FILES['fields']['error'][$counter];
			$name  = $_FILES['fields']['name'][$counter];
			$tmp   = $_FILES['fields']['tmp_name'][$counter];
			
			if ($check == 0)
			{
				$attachments[$name] = $tmp;
			}
			elseif ($required)
			{
				$error .= 'Error attaching file ('.$field['name'].')<br />';
			}
			$k = $field['name'];
			$v = $name;
		}
		elseif ($field['type'] == 'checkbox')
		{
			$k = $field['name'];
			$v = ($value == 'on') ? 'Yes' : 'No';
		}
		elseif ($field['type'] == 'check_list')
		{
			$k = $field['name'];
			array_shift($value); // this gets rid of our "foo" entry so that we can garantee a field has been passed!
			$v = (sizeof($value) > 0) ? implode(', ', $value) : '';
		}
		elseif ($field['type'] == 'dir_dropdown')
		{
			$k = $field['name'];
			$v = $value;
		}
		
		if ($v != null)
		{
			$caption = (strlen($field['caption']) > 0) ? '('.$field['caption'].')' : '';
			$message .= $k . ' '.$caption.': ' . $v ."\n\n";
			$log_data[$k] = $v;
		}
		
		$counter++;
	}
	
	if (!isset($error))
	{
		// continue
		require_once('includes/class.phpmailer.php');
		
		$mail = new PHPMailer();
		$mail->From = ADMIN_EMAIL;
		$mail->FromName = ADMIN_FROM;
		
		if (isset($replyTo))
		{
			$mail->AddReplyTo($replyTo);
		}
		
		$recipient = (isset($custom_address)) ? $custom_address : $settings['recipient_address'];
		$mail->AddAddress($recipient);
		$mail->IsHTML(false);
		$mail->Subject = $settings['from_subject'];
		$mail->Body    = $message;
		
		$log_attach = array();
		
		if (sizeof($attachments) > 0)
		{
			foreach ($attachments as $name => $location)
			{
				if (!$mail->AddAttachment($location, $name))
				{
					$error .= 'Error adding attachment: ' . $name . '<br />';
				}
				$log_attach[$name] = base64_encode(file_get_contents($location));
			}
		}
		
		if (!isset($error))
		{
			// log the data
			$history_table  = DB_PREFIX . 'pico_contact_history';
			
			$db->run('INSERT INTO `'.$history_table.'` (`component_id`, `timestamp`, `log_data`, `attachments`) VALUES (?,?,?,?)', 
				$component_id, time(), serialize($log_data), serialize($log_attach)
			);
			
			$result = $mail->Send();
			if (!$result)
			{
				$error .= 'Error sending mail message<br />';
			}
			else
			{
				return TRUE;
			}
		}
	}
	return $error;
}

function CF_GetDirectoryFields($component_id, $dir_component_id)
{
	global $db;
	$contact_table = DB_PREFIX . 'pico_contact_form';
	
	$field_data = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$field_data = unserialize($field_data);
	
	$addl_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $dir_component_id);
	$data = unserialize($addl_info);
	
	$output = '';
	
	$fields = $data['fields'];
	if (sizeof($fields) > 0)
	{
		$output .= '<select name="field[directory_field]">';
		$output .= '<option value=""></option>';
		foreach ($fields as $field_id => $field)
		{
			$selected = ($field_data['directory_field'] == $field_id) ? 'selected="selected"' : '';
			$output .= '<option value="'.$field_id.'" '.$selected.'>'.$field['name'].'</option>';
		}
		$output .= '</select>';
	}
	
	return $output;
}
?>