<table border="0" cellpadding="2" cellspacing="1">
<?php
foreach ($fields as $field)
{
	$name        = $field['name'];
	$is_required = ($field['required'] == 'required') ? TRUE : FALSE;
	if ($is_required) { $num_required++; }
	
	switch($field['type'])
	{
		case 'text':
			$html_field = '<input type="text" name="fields['.$counter.']" value="'.$data[$counter].'" />'; break;
		case 'textarea':
			$html_field = '<textarea name="fields['.$counter.']">'.$data[$counter].'</textarea>'; break;
		case 'checkbox':
			$html_field = '<input type="hidden" name="fields['.$counter.']" value="off" /><input type="checkbox" value="on" name="fields['.$counter.']" '.(($data[$counter] == 'on')?'checked="checked"':'').' />'; break;
		case 'select':
			$html_field = '<select name="fields['.$counter.']">';
			
			$html_field .= '<option value="">'.$field['caption'].'</option>';
			$field['caption'] = '';
			
			$options = $field['options'];
			
			if (preg_match('/BLOG\:(\d+)/', implode("\n", $options), $m))
			{
				if (is_numeric($m[1]))
				{
					$blog_entries = DB_PREFIX . 'pico_blog_entries';
					$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=? ORDER BY `title` ASC', $m[1], 1);
					if (is_array($entries))
					{
						global $params;
						foreach ($entries as $e)
						{
							$val = $e['title'];
							if (is_numeric($params[1])) { 
								$selected = ($params[1] == $e['post_id']) ? 'selected="selected"' : ''; 
							}
							else {
								$selected = ($data[$counter] == $val) ? 'selected="selected"' : ''; 
							}
							$html_field .= '<option value="'.$val.'" '.$selected.'>'.$val.'</option>';
						}
					}
				}//echo '<pre>'.print_r($m, true).'</pre>';
			}
			else
			{
				if (sizeof($options) > 0)
				{
					foreach ($options as $option)
					{
						if (strstr($option, '|'))
						{
							list($option, $email) = explode('|', $option);
						}
						$html_field .= '<option value="'.$option.'" '.(($data[$counter] == $option)? 'selected="selected"': '').'>'.$option.'</option>';
					}
				}
			}
			$html_field .= '</select>';
			break;
		case 'check_list':
			$html_field = '<input type="hidden" name="fields['.$counter.'][]" value="foo" />';
			$x = 0;
			
			if (sizeof($field['options']) > 0)
			{
				foreach ($field['options'] as $option)
				{
					$html_field .= '<input type="checkbox" name="fields['.$counter.'][]" value="'.$option.'" '.( ( (is_array($data[$counter])) and(in_array($option, $data[$counter])) )   ?'checked="checked"':'').' />'.$option.'<br />';
					$x++;
				}
			}
			break;
		case 'file': 
			$html_field = '<input type="file" name="fields['.$counter.']" />';
			break;
		case 'dir_dropdown':
			$dir_source = $field['directory_source'];
			$dir_field  = $field['directory_field'];
			$html_field = '';
			
			if ( (is_numeric($dir_source)) and (is_numeric($dir_field)) )
			{
				$addl_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $dir_source);
				$_data = unserialize($addl_info);
				
				$dir_fields   = $_data['fields'];
				$linked_field = $dir_fields[$dir_field];
				$field_name   = $linked_field['field_name'];
				
				// get all values in the database with field_name
				$directory_table = DB_PREFIX . 'directory_' . $dir_source;
				$results = $db->force_multi_assoc('SELECT `entry_id`, `'.$field_name.'` FROM `'.$directory_table.'` ORDER BY `'.$field_name.'` ASC');
				if (is_array($results))
				{
					$html_field .= '<select name="fields['.$counter.']">';
					foreach ($results as $result)
					{
						$val = str_replace('"', '\\"', $result[$field_name]);
						$selected = '';
						
						if ($params[1] == 'link')
						{
							if ($params[2] == $result['entry_id'])
							{
								$selected = 'selected="selected"';
							}
						}
						else
						{
							//echo $data[$counter] . '|' . $val . '|'.$counter.'<br />';
							if ($data[$counter] == $val)
							{
								$selected = 'selected="selected"';
							}
						}
						
						$html_field .= '<option value="'.$val.'" '.$selected.'>'.$result[$field_name].'</option>';
					}
					$html_field .= '</select>';
				}
				
				//$html_field = '<pre>'.print_r($linked_field, true).'</pre>';
			}
			
			//$html_field = '<pre>'.print_r($field, true).'</pre>';
			break;
		default:
			$html_field = '';
	}
	
	$caption = '<div class="caption">'.$field['caption'].'</div>';
	
	$req = ($is_required) ? '*' : '';

	echo <<<HTML
<tr>
	<td class="left">
		$name $req 
	</td>
	<td class="right">
		$caption
		$html_field
	</td>
</tr>
HTML;
	$counter++;
}
?>

<tr>
	<td class="left">
		Verification *
	</td>
	<td class="right">
		<?=$captcha_img?>
		<div class="caption">Please type the phrase above into the box below</div>
		<input type="text" name="verify" />
	</td>
</tr>
</table>