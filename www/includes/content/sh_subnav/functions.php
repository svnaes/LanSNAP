<?php

function SubNav_GetMainParent($id)
{
	global $db;
	$sh_table = DB_PREFIX . 'pico_site_heirarchy';
	
	$parent = $db->result('SELECT `parent` FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
	if ($parent == 0)
	{
		return $id;
	}
	else
	{
		return SubNav_GetMainParent($parent);
	}
}

function SubNav_ActiveChild($data)
{
	global $db, $body;
	$sh_table  = DB_PREFIX . 'pico_site_heirarchy';
	foreach ($data as $item)
	{
		$id = $item['id'];
		if (!isset($item['sub_link'])) // sub_link is for component based linking... don't check that
		{
			$item_info = $db->assoc('SELECT * FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
			if ($item_info['page_id'] != 0)
			{
				$page_info = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $item_info['page_id']);
				if ($item_info['page_id'] == CURRENT_PAGE) { return TRUE; }
			}
			
			if (sizeof($item['children']) > 0)
			{
				return SubNav_ActiveChild($item['children']);
			}
		}
	}
	return FALSE;
}

function SubNav_Display($data, $class = '', $parent = 0)
{
	//echo '<pre>'.print_r($data, true).'</pre>';
	global $db, $body;
	$sh_table  = DB_PREFIX . 'pico_site_heirarchy';
	$num_items = sizeof($data);
	
	if ($num_items > 0)
	{
		echo '<ul class="'.$class.'">';
		$counter = 0;
		
		foreach ($data as $item)
		{
			if ($item['show_in_nav'] == 1)
			{
				$classes = array();
				
				$id = $item['id'];
				
				if ($counter == 0)
				{ 
					$classes[] = 'first';
				}
				elseif (($counter+1) == $num_items)
				{
					$classes[] = 'last';
				}
				else
				{
					$classes[] = 'inner';
				}
				
				if ($item['test'] == TRUE)
				{
					$class_text = implode(' ', $classes);
					$name = $item['display'];
					echo '<li class="'.$class_text.'">';
					echo '<span class="item"><a href="#">'.$name.'</a></span>';
				}
				else
				{
					if (isset($item['sub_nav']) == TRUE)
					{
						// get the parent alias
						$page_id = $db->result('SELECT `page_id` FROM `'.$sh_table.'` WHERE `entry_id`=?', $parent);
						//$alias   = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
						$name    = '<a href="'.$body->url($alias . '/' . $item['sub_link']).'">'.$item['name'].'</a>';
					}
					else
					{
						$item_info = $db->assoc('SELECT * FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
						if ($item_info['page_id'] != 0)
						{
							// page
							$page_info = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $item_info['page_id']);
							//echo $db->query;
							
							if ( (isset($item_info['linked'])) and ($item_info['linked'] == 0) )
							{
								$name = $page_info['name'];
							}
							else
							{
								$name = '<a href="'.$body->url($page_info['alias']).'">'.$page_info['name'].'</a>';
							}
							
							
							if ($page_info['page_id'] == CURRENT_PAGE) { $classes[] = 'active'; }
						}
						else
						{
							$name = $item_info['text'];
							if (strlen($item_info['external_url']) > 0)
							{
								$name = '<a href="'.$item_info['external_url'].'">'.$name.'</a>';
							}
						}
					}
					
					if (sizeof($item['children']) > 0)
					{
						foreach ($item['children'] as $child)
						{
							if ($child['show_in_nav'] == 1)
							{
								$classes[] = 'menu';
								break;
							}
						}
					}
					
					// see if we have an active child
					if ( (sizeof($item['children']) > 0) and (SubNav_ActiveChild($item['children'])) )
					{
						//echo '<pre>'.print_r($item['children'], true).'</pre>';
						$classes[] = 'active_child';
					}
					
					$class_text = implode(' ', $classes);
					echo '<li class="'.$class_text.'">';
					echo '<span class="item">'.$name.'</span>';
				}
				
				if (sizeof($item['children']) > 0)
				{
					SubNav_Display($item['children'], null, $item['id']);
				}
				echo '</li>';
				$counter++;
			}
		}
		echo '</ul>';
	}
}
?>