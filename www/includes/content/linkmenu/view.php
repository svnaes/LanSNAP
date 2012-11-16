<?php

$options = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings = unserialize($options);
if (!is_array($settings)) { $settings = array(); }

$content = $db->result('SELECT `content` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
if ($content == FALSE) { $content = ''; }


$data = explode(',', $content);
if (!is_array($data)) { $data = array(); }

$links = array();
if (sizeof($data) > 0)
{
	foreach($data as $key=>$val)
	{
		if (strlen($val) > 0)
		{
			$links[$key] = $val;
		}
	}
}

//echo '<pre>'.print_r($links, TRUE).'</pre>';

$counter   = 0;
$last      = sizeof($links) - 1;
$link_text = '<ul>';

if (sizeof($links) > 0)
{
	foreach ($links as $key=>$val)
	{
		// key is just for placement
		$link    = explode('_', $val);
		$link_id = array_pop($link);
		
		$classes = array();
		if ($counter == 0)
		{
			$classes[] = 'first';
		}
		elseif ($counter == $last)
		{
			$classes[] = 'last';
		}
		else
		{
			$classes[] = 'inner';
		}
		
		if (in_array('t', $link))
		{
			$classes[] = 'tabbed';
		}
		
		
		if (in_array('l', $link))
		{
			// get link name from link table
			$link_data = $db->assoc('SELECT * FROM `'.DB_LINKS.'` WHERE `link_id`=?', $link_id);
			
			if ($settings['enabled'] == 'yes') // are images enabled
			{
				$url = '<a href="'.$link_data['url'].'" '.( (strlen($link_data['target']) > 0) ? 'target="'.$link_data['target'].'"' : '').'><img src="'.$body->url('includes/content/linkmenu/'.PAGE_ID.'/'.$component_id.'/'.$counter.'.png').'" border="0" alt="'.$link_data['name'].'" /></a>';
			}
			else
			{
				$url = '<a href="'.$link_data['url'].'" '.( (strlen($link_data['target']) > 0) ? 'target="'.$link_data['target'].'"' : '').'>'.$link_data['name'].'</a>';
			}
			
			if (strlen($link_data['caption']) > 0)
			{
				$url .= '<div class="caption">'.$link_data['caption'].'</div>';
			}
		}
		else
		{
			$link_data = $db->assoc('SELECT `alias`, `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $link_id);
			
			$active = false;
			// link name from pages
			if ($link_id == CURRENT_PAGE)
			{
				$classes[] = 'active';
				$active = true;
			}
			$link_data = $db->assoc('SELECT `alias`, `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $link_id);
			
			if ($settings['enabled'] == 'yes')
			{
				$extra = ($active == TRUE) ? '?active=true' : '';
				$url = '<a href="'.$body->url($link_data['alias']).'"><img src="'.$body->url('includes/content/linkmenu/'.$component_id.'/'.$counter.'.png'.$extra).'" border="0" alt="'.$link_data['name'].'" /></a>';
			}
			else
			{
				$url = '<a href="'.$body->url($link_data['alias']).'">'.$link_data['name'].'</a>';
			}
		}
		
		
		
		$link_text .= '<li class="'.implode(' ', $classes).'">'.$url.'</li>';
		
		if ($counter != $last)
		{
			if (strlen($settings['separator']) > 0)
			{
				$link_text .= '<li class="separator">'.$settings['separator'].'</li>';
			}
		}
		
		$counter++;
	}
}
$link_text .= '</ul>';
echo $link_text;
?>