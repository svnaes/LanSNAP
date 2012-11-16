<?php

$blog_options    = DB_PREFIX . 'pico_blog_options';
$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$blog_categories = DB_PREFIX . 'pico_blog_categories';
$blog_comments   = DB_PREFIX . 'pico_blog_comments';

function blog_check_image_dir($entry_id)
{
	global $db;
	$blog_options = DB_PREFIX . 'pico_blog_options';
	$blog_entries = DB_PREFIX . 'pico_blog_entries';
	
	/* check to make sure we only have files in this directory that actually belong here
		1 - source image
		2 - preview image
		3 - story preview image
		4 - story full image
	*/
	
	$entry_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$image_settings_raw = $db->result('SELECT `image_settings` FROM `'.$blog_options.'` WHERE `component_id`=?', $entry_info['component_id']);
	$image_settings = unserialize($image_settings_raw);
	if (!is_array($image_settings)) { $image_settings = array(); }
	
	$spi_width  = (is_numeric($image_settings['preview_width'])) ? $image_settings['preview_width'] : 200;
	$spi_height = (is_numeric($image_settings['preview_height'])) ? $image_settings['preview_height'] : 150;
	$sfi_width  = (is_numeric($image_settings['full_width'])) ? $image_settings['full_width'] : 200;
	$sfi_height = (is_numeric($image_settings['full_height'])) ? $image_settings['full_height'] : 150;
	
	$spi_image  = md5($saved_image . '_' . $spi_width . '_' . $spi_height) . '.png';
	$sfi_image  = md5($saved_image . '_' . $sfi_width . '_' . $sfi_height) . '.png';
	$spi_image2 = md5($saved_image . '_' . $spi_width . '_' . $spi_height) . '.jpg';
	$sfi_image2 = md5($saved_image . '_' . $sfi_width . '_' . $sfi_height) . '.jpg';
	
	$preview_file = $entry_info['story_image'];
	$saved_image  = md5('preview_'.$entry_info['story_image']) . '.png';
	
	$ok_images = array(
		$preview_file, 
		$saved_image,
		$spi_image,
		$sfi_image,
		$spi_image2,
		$sfi_image2,
	);
	
	$dir = 'includes/content/blog/storage/'. $entry_id;
	$all_files = array();
	
	if (is_dir($dir))
	{
		if ($dh = opendir($dir))
		{
			while (($file = readdir($dh)) !== false)
			{
				if ( ($file != '.') and ($file != '..') )
				{
					if (!in_array($file, $ok_images))
					{
						$full_bad_file = $dir . '/' . $file;
						if (is_file($full_bad_file))
						{
							//echo "BAD: $full_bad_file<br />";
							unlink($full_bad_file);
						}
					}
				}
			}
			closedir($dh);
		}
	}
}

function get_category_drop($name, $component_id, $selected = 0)
{
	$blog_categories = DB_PREFIX . 'pico_blog_categories';
	global $db;
	$return = '<select name="'.$name.'" id="blog_cat_'.$component_id.'">';
	
	$categories = $db->force_multi_assoc('SELECT * FROM `'.$blog_categories.'` WHERE `component_id`=? ORDER BY `title` ASC', $component_id);
	$end_categories = array();
	$end_categories[0] = 'None';
	
	if ( (is_array($categories)) and (sizeof($categories) > 0) )
	{
		foreach($categories as $cat)
		{
			$end_categories[$cat['category_id']] = $cat['title'];
		}
	}
	
	foreach ($end_categories as $id=>$val)
	{
		$seltext = ($selected == $id) ? 'selected="selected"' : '';
		$return .= '<option value="'.$id.'" '.$seltext.'>'.$val.'</option>';
	}
	$return .= '</select>';
	return $return;
}

function get_category($category_id)
{
	if ($category_id == 0) { return 'Uncategorized'; }
	global $db;
	$blog_categories = DB_PREFIX . 'pico_blog_categories';
	$category = $db->result('SELECT `title` FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
	return $category;
}

function get_blog_comments($post_id, $layout, $parent = 0, $all = false)
{
	global $db, $body;
	$blog_comments = DB_PREFIX . 'pico_blog_comments';
	
	if ($all)
	{
		$user_comments = $db->force_multi_assoc('SELECT * FROM `'.$blog_comments.'` WHERE `post_id`=? AND `parent`=? ORDER BY `date` DESC', $post_id, $parent);
	}
	else
	{
		$user_comments = $db->force_multi_assoc('SELECT * FROM `'.$blog_comments.'` WHERE `post_id`=? AND `active`=? AND `parent`=? ORDER BY `date` DESC', $post_id, 1, $parent);
	}
	$output = '';
	
	if ( (is_array($user_comments)) and (sizeof($user_comments) > 0) ) 
	{
		foreach ($user_comments as $comment)
		{
			$url = $comment['url'];
			if (sizeof($url) > 0)
			{
				if (substr($url, 0, 7) != 'http://') { $url = 'http://'.$url; }
				$name = '<a href="'.$url.'" target="_blank">'.$comment['name'].'</a>';
			}
			else
			{
				$name = $comment['name'];
			}
			
			$date    = date('m/d/y', $comment['date']);
			$message = nl2br($comment['message']);
			
			if ($comment['active'] == 0)
			{
				$message = '<span class="inactive">'.$message.'</span>';
			}
			
			
			$_entry = $layout;
			$_entry = str_replace('NAME', $name, $_entry);
			$_entry = str_replace('DATE', $date, $_entry);
			$_entry = str_replace('MESSAGE', $message, $_entry);
			$_entry = str_replace('REPLY', '<span class="click" onclick="Blog_Reply('.$post_id.', '.$comment['comment_id'].')">Reply</span>', $_entry);
			
			$output .= '<div class="comment_entry">';
			$output .= '<div id="blogcomment_'.$comment['comment_id'].'"></div>';
			if (USER_ACCESS > 2)
			{
				$actions = '<img class="icon click" title="Delete Comment" onclick="Blog_DeleteComment('.$post_id.', '.$comment['comment_id'].')" src="'.$body->url('includes/icons/delete.png').'" />';
				if ($comment['active'] == 0)
				{
					$actions .= '<img class="icon click" title="Approve Comment" onclick="Blog_ApproveComment('.$post_id.', '.$comment['comment_id'].')" src="'.$body->url('includes/icons/ok.png').'" />';
				}
				$_entry = str_replace('EMAIL', $comment['email'], $_entry);
				$_entry = str_replace('ACTIONS', $actions, $_entry);
				//$output .= '<img class="icon click" onclick="Blog_DeleteComment('.$post_id.', '.$comment['comment_id'].')" src="'.$body->url('includes/icons/delete.png').'" />';
			}
			$output .= $_entry;
			
			// see if there are any children
			$output .= get_blog_comments($post_id, $layout, $comment['comment_id']);
			
			$output .= '</div>';
		}
	}
	return $output;
}

?>