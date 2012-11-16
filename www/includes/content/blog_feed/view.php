<?php

require_once('includes/content/blog/functions.php');

$blog_options    = DB_PREFIX . 'pico_blog_options';
$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$blog_categories = DB_PREFIX . 'pico_blog_categories';
$blog_comments   = DB_PREFIX . 'pico_blog_comments';

$options = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);

$settings = unserialize($options);
if (!is_array($settings)) { $settings = array(); }

/*
[blog] => 79
    [title] => Latest News
    [num_entries] => 2
    [num_words] => 25
    [layout] => 
*/

$limit     = (is_numeric($settings['num_entries'])) ? $settings['num_entries'] : 3;
$num_words = (is_numeric($settings['num_words'])) ? $settings['num_words'] : 25;

$blog_settings = $db->assoc('SELECT * FROM `'.$blog_options.'` WHERE `component_id`=?', $settings['blog']);
$blog_page     = $db->result('SELECT `page_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=?', $settings['blog']);
$blog_alias    = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $blog_page); 
$link          = $body->url($blog_alias);

echo '<div class="main_title"><a href="'.$link.'">'.$settings['title'].'</a></div>';

if (is_numeric($settings['blog']))
{
	$layout = trim($settings['layout']);
	if (strlen($layout) == 0)
	{
		$layout = <<<HTML
	<div class="title">TITLE</div>
	<div class="date">DATE</div>
	<div class="story">STORY</div>
HTML;
	}
	
	
	if ($limit < 0)
	{
		$limit = abs($limit);
		$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 ORDER BY RAND() LIMIT ' . $limit, $settings['blog']);
	}
	else
	{
		$show_tags = trim($settings['show_tags']);
		$find_tags = explode(',', $show_tags);
		if (strlen($show_tags) > 0)
		{
			$entries = array();
			$continue = true;
			$_limit = 0;
			
			while ($continue)
			{
				$entry = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 ORDER BY `date` DESC LIMIT  '. $_limit . ',1', $settings['blog']);
				if (isset($entry['post_id']))
				{
					$tag_data = unserialize($entry['tags']);
					if (sizeof($tag_data) > 0)
					{
						foreach ($tag_data as $key=>$val)
						{
							foreach ($find_tags as $tag)
							{
								if ($tag == $val)
								{
									$entries[] = $entry;
									if (sizeof($entries) == $limit)
									{
										$continue = false;
									}
								}
							}
						}
						$tags = implode(', ', $tag_data);
					}
				}
				else
				{
					$continue = false;
				}
				$_limit++;
			}
			
			$entries = array_unique($entries);
		}
		elseif ( ($blog_settings['hide_expired'] == 1) or ($settings['upcoming'] == 1) )
		{
			$today = mktime(0,0,0, date('m'), date('d'), date('Y'));
			$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `date` >= ? AND `published`=1 ORDER BY `date` ASC LIMIT ' . $limit, $settings['blog'], $today);
		}
		elseif ($settings['past'] == 1)
		{
			$today = mktime(0,0,0, date('m'), date('d'), date('Y'));
			$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `date` < ? AND `published`=1 ORDER BY `date` DESC LIMIT ' . $limit, $settings['blog'], $today);
		}
		else
		{
			$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 ORDER BY `date` DESC LIMIT ' . $limit, $settings['blog']);
		}
	}
	if ( (is_array($entries)) and (sizeof($entries) > 0) )
	{
		foreach ($entries as $entry)
		{
			$title   = $entry['title'];
			$date    = date('F j, Y', $entry['date']);
			$subpage = PageNameToAlias($title);
			$link    = $body->url($blog_alias . '/' . $subpage);
			$story   = $entry['post'];
			
			$tag_data = unserialize($entry['tags']);
			if (sizeof($tag_data) > 0)
			{
				foreach ($tag_data as $key=>$val)
				{
					$tag_data[$key] = '<a href="'.$body->url($blog_alias . '/tag/'. PageNameToAlias($val)).'">'.$val.'</a>';
				}
				$tags = implode(', ', $tag_data);
			}
			else
			{
				$tags = '';
			}
			
			$cat_name = get_category($entry['category']);
			$category = '<a href="'.$body->url($blog_alias . '/category/'. PageNameToAlias($cat_name)).'">'.$cat_name.'</a>';
			
			//echo 'asdfasfasdfasdf';
			
			if ($settings['strip'] == 1)
			{
				$story = strip_tags($story);
			}
			
			$words = explode(' ', $story);
			if (sizeof($words) > $num_words)
			{
				while (sizeof($words) > $num_words)
				{
					array_pop($words);
				}
				$story = implode(' ', $words) . '...';
			}
			
			
			$blog_entry = $layout;
			
			if (preg_match('/\{DATE,([^\}]+)\}/', $blog_entry, $matches))
			{
				$custom_date = date($matches[1], $entry['date']);
				$blog_entry = preg_replace('/\{DATE,[^\}]+}/', $custom_date, $blog_entry);
			}
			else
			{
				$blog_entry = str_replace('DATE', $date, $blog_entry);
			}
			
			
			$blog_entry = str_replace('STORY', $story, $blog_entry);
			$blog_entry = str_replace('TITLE', $title, $blog_entry);
			$blog_entry = str_replace('CATEGORY', $category, $blog_entry);
			$blog_entry = str_replace('TAGS', $tags, $blog_entry);
			$blog_entry = str_replace('LINK', $link, $blog_entry);
			
			echo '<div class="blog_entry">'.$blog_entry.'</div>';

		}
	}
	else
	{
		echo nl2br($settings['no_post']);
	}
	
	if ($blog_settings['show_rss'] != 0)
	{
		$blog_url = 'http://' . $_SERVER['SERVER_NAME'] . $body->url($blog_alias . '/feed');
		
		$rss_link = '
<table border="0" cellpadding="1" cellspacing="1" class="rss_link">
<tr>
	<td>
		<a type="application/rss+xml" href="'.$body->url($blog_alias . '/feed').'">
		<img src="'.$body->url('includes/content/blog/rss.png').'" border="0" />
		</a>
	</td>
	<td>
		<a type="application/rss+xml" href="'.$body->url($blog_alias . '/feed').'">
		Subscribe to RSS
		</a>
	</td>
</tr>
</table>';
		
		if ($settings['show_rss'] == 1)
		{
			echo $rss_link;
		}
		
	}
}
?>