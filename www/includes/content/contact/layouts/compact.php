<div class="contact_compact">
<?php
foreach ($fields as $field)
{
	$name        = $field['name'];
	$is_required = ($field['required'] == 'required') ? TRUE : FALSE;
	if ($is_required) { $num_required++; }
	
	switch($field['type'])
	{
		case 'text':
			$val     = (isset($data[$counter])) ? $data[$counter] : $name;
			$onfocus = (isset($data[$counter])) ? $data[$counter] : 'onfocus="this.value=\'\'"';
			$html_field = '<input type="text" class="text" name="fields['.$counter.']" value="'.$val.'" '.$onfocus.' />'; break;
		case 'textarea':
			$val     = (isset($data[$counter])) ? $data[$counter] : $name;
			$onfocus = (isset($data[$counter])) ? $data[$counter] : 'onfocus="this.value=\'\'"';
			$html_field = '<textarea name="fields['.$counter.']" '.$onfocus.'>'.$val.'</textarea>'; break;
		case 'checkbox':
			$html_field = $name . ' <input type="hidden" name="fields['.$counter.']" value="off" /><input type="checkbox" value="on" name="fields['.$counter.']" '.(($data[$counter] == 'on')?'checked="checked"':'').' />'; break;
		case 'select':
			$html_field = '<div class="select"><select name="fields['.$counter.']">';
			
			$html_field .= '<option value="">'.$name.'</option>';
			$field['caption'] = '';
			
			$options = $field['options'];
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
			$html_field .= '</select></div>';
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
			$html_field = $name . '<br /> <input type="file" name="fields['.$counter.']" />';
			break;
		default:
			$html_field = '';
	}
	
	echo <<<HTML
<div class="entry">
	$html_field
</div>
HTML;
	
/*
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
HTML;*/
	$counter++;
}
?>
	<div class="entry captcha">
		<?=$captcha_img?>
	</div>
	<div class="entry">
		<div class="caption">Please type the phrase into the box below</div>
		<input type="text" class="text" name="verify" />
	</div>
	<div class="clear"></div>
</div>