<?php

require_once('includes/content/media/functions.php');
require_once('includes/content/blog/functions.php');
require_once('includes/captcha.class.php');

$blog_options    = DB_PREFIX . 'pico_blog_options';
$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$blog_categories = DB_PREFIX . 'pico_blog_categories';
$blog_comments   = DB_PREFIX . 'pico_blog_comments';

// options

$options = $db->assoc('SELECT * FROM `'.$blog_options.'` WHERE `component_id`=?', $component_id);

$image_settings = unserialize($options['image_settings']);
if (!is_array($image_settings)) { $image_settings = array(); }

$show_short_layout = unserialize($options['show_short_layout']);
if (!is_array($show_short_layout)) { $show_short_layout = array(); }

// default synopsis

$layout = trim($options['layout']);
if (strlen($layout) == 0)
{
	$layout = <<<HTML
	<table border="0" cellpadding="0" cellspacing="0" class="blog_synopsis">
	<tr>
		<td class="left">IMAGE</td>
		<td class="right">
			<h3><a href="LINK">TITLE</a></h3>
			{STORY,25} <a href="LINK">more</a>
		</td>
	</tr>
	</table>
HTML;
}

// default story layout

if ( (!isset($options['full_layout'])) and (isset($options['layout'])) )
{
	$full_layout = $layout;
	// someone has upgraded, and has not re-set their options yet.
}
else
{
	$full_layout = trim($options['full_layout']);
	if (strlen($full_layout) == 0)
	{
		$full_layout = <<<HTML
	<h2><a href="LINK">TITLE</a></h2>
	<div class="info">Posted on DATE in CATEGORY</div>
	<div class="story">STORY</div>
	<div class="tags">Tags: TAGS</div>
HTML;
	}
}

$comment_layout = trim($options['comment_layout']);
if (strlen($comment_layout) == 0)
{
	$comment_layout = <<<HTML
<div class="comment_name">NAME said on DATE:</div>
<div class="comment_message">MESSAGE</div>
HTML;
}

$asc_or_desc = ($options['hide_expired'] == 1) ? 'DESC' : 'DESC';
$bottom_nav  = '';
$show        = $options['num_entries']; # of entries to show each page

if (!is_numeric($show)) { $show = 3; }

// rss link

if ($options['show_rss'] != 0)
{
	$blog_url = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS . '/feed');
	$blog_title = $db->result('SELECT `description` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$body->add_head('<link rel="alternate" type="application/rss+xml" href="'.$blog_url.'" title="'.$blog_title.'" />');
}

global $params;

if ( (isset($params[1])) and ($params[1] == 'tag') )
{
	// if tag:
	$tag = urldecode($params[2]);
	echo '<h1>Entries tagged "'.$tag.'"</h1>';
	$tag = strtolower($tag);
	
	$all_entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 ORDER BY `date` ' . $asc_or_desc, $component_id);
	$entries = array();
	
	if ( (is_array($all_entries)) and (sizeof($all_entries) > 0) )
	{
		foreach ($all_entries as $entry)
		{
			$tags = unserialize($entry['tags']);
			if (sizeof($tags) > 0)
			{
				foreach ($tags as $key=>$val)
				{
					$val = str_replace('#', '', $val);
					$tags[$key] = strtolower($val);
				}
			}
			if (in_array($tag, $tags)) { $entries[] = $entry; }
		}
	}
	
	//$body->title .= ' - ' . $tag;
	$body->title = $tag . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
}
elseif ( (isset($params[1])) and ($params[1] == 'category') )
{
	// if category:
	
	$show_layout = (in_array('categories', $show_short_layout)) ? 'short' : 'full';
	
	$cat_alias = $params[2];
	if ($cat_alias == 'uncategorized')
	{
		$category_id = 0;
	}
	else
	{
		$category_id = $db->result('SELECT `category_id` FROM `'.$blog_categories.'` WHERE `alias`=? AND `component_id`=?', $cat_alias, $component_id);
	}
	
	if ($category_id === FALSE)
	{
		echo 'Invalid category: ' . $cat_alias;
		return;
	}
	$category_name = get_category($category_id);
	
	echo '<h1>'.$category_name.'</h1>';
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `category`=? AND `component_id`=? AND `published`=1', $category_id, $component_id);
	
	//$body->title .= ' - ' . $category_name;
	$body->title = $category_name . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
}
elseif ( (isset($params[1])) and ($params[1] == 'year') )
{
	// if year
	
	$show_layout = (in_array('archives', $show_short_layout)) ? 'short' : 'full';
	
	$year = $params[2];
	$start = mktime(0,0,0, 1, 1, $year);
	$end   = mktime(23,59,59, 12, 31, $year);
	
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `date`>=? AND `date`<=? AND `component_id`=? AND `published`=1 ORDER BY `date` ASC', $start, $end, $component_id);
	$body->title = date('Y', $start) . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
}
elseif ( (isset($params[1])) and ($params[1] == 'date') )
{
	// if archives
	
	$show_layout = (in_array('archives', $show_short_layout)) ? 'short' : 'full';
	
	$year  = $params[2];
	$month = $params[3];
	
	$start = mktime(0,0,0, $month, 1, $year);
	
	$last_day = date('t', $start);
	$end = mktime(23,59,59, $month, $last_day, $year);
	
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `date`>=? AND `date`<=? AND `component_id`=? AND `published`=1 ORDER BY `date` ASC', $start, $end, $component_id);
	
	$body->title = date('F Y', $start) . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
}
elseif ( (isset($params[1])) and ($params[1] == 'feed') )
{
	// if rss feed
	$items = '';
	$rss_entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 ORDER BY `date` '.$asc_or_desc.' LIMIT 3', $component_id);
	if ( (is_array($rss_entries)) and (sizeof($rss_entries) > 0) )
	{
		//DATE, STORY, TITLE, CATEGORY, TAGS, LINK
		foreach ($rss_entries as $entry)
		{
			$title   = $entry['title'];
			$date    = date('r', $entry['date']);
			if (!isset($pub_ts))
			{
				$pub_ts = $entry['date'];
			}
			$subpage = PageNameToAlias($title);
			$link    = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS . '/' . $subpage);
			$story   = $entry['post'];
			$story   = htmlspecialchars($story);
			
			$guid    = md5($entry['post_id']);
			
			$items .= <<<RSS
		<item>
			<title>$title</title>         
			<link>$link</link>
			<description>$story</description>
			<guid>$link</guid>
		</item>

RSS;
		}
	}
	
	$site_name = $_SERVER['SERVER_NAME'];
	$pub_date  = date('r', $pub_ts);
	
	header('Content-type: text/xml');
	
	$page_text = <<<RSS
<?xml version="1.0" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="$blog_url" rel="self" type="application/rss+xml" />
		<title>$blog_title</title>
		<link>$blog_url</link>
		<description>Website blog for $site_name</description>
		<pubDate>$pub_date</pubDate>
$items
	</channel>
</rss> 
RSS;
	
	define('STATIC_HTML', $page_text);
}
elseif ( (isset($params[1])) and ($params[1] == 'page') )
{
	// if older/new posts
	$show_layout = (in_array('main', $show_short_layout)) ? 'short' : 'full';
	
	$page_num = $params[2];
	$limit = $page_num * $show;
	
	$entries     = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 ORDER BY `date` '.$asc_or_desc.' LIMIT '.$limit.','.$show, $component_id);
	$num_entries = $db->result('SELECT count(1) FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1', $component_id);
	
	if ($num_entries > ($limit+$show))
	{
		// show older
		$bottom_nav .= '<a href="'.$body->url(CURRENT_ALIAS . '/page/'.($page_num+1)).'" class="older-posts">&laquo; Older Posts</a>  ';
	}
	
	$prev_page = $page_num - 1;
	$link      = ($prev_page > 0) ? $body->url(CURRENT_ALIAS . '/page/'.$prev_page) : $body->url(CURRENT_ALIAS);
	$bottom_nav .= '<a href="'.$link.'" class="newer-posts">Newer Posts &raquo;</a>';
}
elseif (isset($params[1]))
{
	$show_layout = 'full';
	// if title:
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `alias`=? AND `component_id`=? AND `published`=1', $params[1], $component_id);
	
	if ( (is_array($entries)) and (sizeof($entries) == 1) )
	{
		$e = $entries[0];
		//$body->title .= ' - ' . $e['title'];
		
		$body->title = $e['title'] . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
	}
}
else
{
	// else: main
	$show_layout = (in_array('main', $show_short_layout)) ? 'short' : 'full';
	
	if ($options['hide_expired'] == 1)
	{
		$today = mktime(0,0,0, date('m'), date('d'), date('Y'));
		$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `date` >= ? ORDER BY `date` ASC', $component_id, $today);
	}
	else
	{
		$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 ORDER BY `date` '.$asc_or_desc.' LIMIT '.$show, $component_id);
	
		$num_entries = $db->result('SELECT count(1) FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1', $component_id);
		if ($num_entries > $show)
		{
			$bottom_nav .= '<a href="'.$body->url(CURRENT_ALIAS . '/page/1').'">&laquo; Older Posts</a>';
		}
	}
}

$rss_link = '
<table border="0" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<a type="application/rss+xml" href="'.$body->url(CURRENT_ALIAS . '/feed').'">
		<img src="'.$body->url('includes/content/blog/rss.png').'" border="0" />
		</a>
	</td>
	<td>
		<a type="application/rss+xml" href="'.$body->url(CURRENT_ALIAS . '/feed').'" class="rss">
		Subscribe to RSS
		</a>
	</td>
</tr>
</table>';

if ($options['show_rss'] == 1)
{
	// show rss link
	echo $rss_link;
}

if ( (is_array($entries)) and (sizeof($entries) > 0) )
{
	//DATE, STORY, TITLE, CATEGORY, TAGS, LINK, COMMENTS
	foreach ($entries as $entry)
	{
		$title   = nl2br($entry['title']);
		//$date    = date('F j, Y', $entry['date']);
		$edate   = date('F j, Y', $entry['date']);
		$subpage = PageNameToAlias($title);
		$link    = $body->url(CURRENT_ALIAS . '/' . $subpage);
		$story   = $entry['post'];
		
		$tag_data = unserialize($entry['tags']);
		if (sizeof($tag_data) > 0)
		{
			foreach ($tag_data as $key=>$val)
			{
				$tag_data[$key] = '<a href="'.$body->url(CURRENT_ALIAS . '/tag/'. $val).'">'.$val.'</a>';
			}
			$tags = implode(', ', $tag_data);
		}
		else
		{
			$tags = '';
		}
		
		if ($options['allow_comments'] != 0)
		{
			$post_id  = $entry['post_id'];
			$num_comments = $db->result('SELECT count(1) FROM `'.$blog_comments.'` WHERE `post_id`=? AND `active`=?', $post_id, 1);
			
			$instance_id = md5('blog_' .  $component_id . '_' . $post_id);
			$captcha     = new Captcha($instance_id, $db, getenv('REMOTE_ADDR'));
			$captcha_img = '<img src="'.$body->url($captcha->Image()).'" />';
			
			$comments = '<div id="comment_form_'.$component_id.'">';
			$comments .= '<div id="comment_container_'.$post_id.'">';
			$comments .= get_blog_comments($post_id, $comment_layout);
			$comments .= '</div>';
			$comments .= '<div class="bold click" onclick="ToggleComment('.$post_id.')">Add a comment</div>';
			
			$keyphrase = generate_text(5);
			$pass      = encrypt($keyphrase);
			$_SESSION['verify_'.$post_id] = $pass; // we use this to verify individual captcha's
			
			$comments .= '<div id="comment_'.$post_id.'" style="display: none">';
			$comments .= '<form method="post" id="comment_form_'.$post_id.'" action="'.$body->url('includes/content/blog/comments.php').'" onsubmit="AddComment(this); return false">';
			$comments .= '<input type="hidden" name="page_action" value="post_new" />';
			$comments .= '<input type="hidden" name="post_id" value="'.$post_id.'" />';
			$comments .= '<input type="hidden" name="parent" value="0" />';
			$comments .= '<input type="hidden" name="verifyphrase" value="'.$pass.'" />';
			$comments .= '<table border="0" cellpadding="2" cellspacing="1">';
			$comments .= '<tr><td>Name</td><td><input type="text" name="name" /></td></tr>';
			$comments .= '<tr><td>Website Address (optional)</td><td><input type="text" name="url" /></td></tr>';
			$comments .= '<tr><td>Email Address<br /><span style="font-size: x-small">for admin\'s eyes only, not posted to site</span></td><td><input type="text" name="email" /></td></tr>';
			$comments .= '<tr><td>Message</td><td><textarea class="comment" name="message"></textarea></td></tr>';
			$comments .= '<tr><td>Verification<br />(Case sensitive)</td><td>
				<div id="captcha_'.$post_id.'">'.$captcha_img.'</div>
			<br />Please type the phrase above into the box below<br /><input type="text" name="verify" /></td></tr>';
			$comments .= '</table><input type="submit" value="Add Comment" name="submitbtn" />';
			$comments .= '</form></div></div>';
			
			// new comment form
			
			$comments = '<div class="comment_hide" id="comment_show_'.$post_id.'">'.$comments.'</div>';
		}
		else
		{
			$comments = '';
			
		}
		
		$cat_name = get_category($entry['category']);
		$category = '<a href="'.$body->url(CURRENT_ALIAS . '/category/'. PageNameToAlias($cat_name)).'">'.$cat_name.'</a>';
		
		$toggle   = 'ShowComments('.$post_id.')';
		
		//$dts = strtotime($entry['']);
		//$date = date('F j, Y', $entry['date']);
		//$date = $entry['date'];
		
		//echo $d
		
		$blog_entry = ($show_layout == 'full') ? $full_layout : $layout;
		//$blog_entry = $full_layout;
		$blog_entry = str_replace('DATE', $edate, $blog_entry);
		$blog_entry = str_replace('ID', $entry['post_id'], $blog_entry);
		$blog_entry = str_replace('TITLE', $title, $blog_entry);
		$blog_entry = str_replace('CATEGORY', $category, $blog_entry);
		$blog_entry = str_replace('TAGS', $tags, $blog_entry);
		
		
		// get next,prev
		
		preg_match('/\{PREV,([^\}]+)\}/', $blog_entry, $matches);
		$prev = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `date`<? ORDER BY `date` DESC LIMIT 1', $component_id, $entry['date']);
		$next = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `date`>? ORDER BY `date` ASC LIMIT 1', $component_id, $entry['date']);
		
		// PREVIOUS
		
		if (isset($matches[1]))
		{
			$_link  = $body->url(CURRENT_ALIAS.'/'.$prev['alias']);
			
			if (is_array($prev))
			{
				// replace with link
				$replace = str_replace('PREV_LINK', $_link, $matches[1]);
				$blog_entry = preg_replace('/\{PREV,[^\}]+}/', $replace, $blog_entry);
			}
			else
			{
				// replace with nothing
				$blog_entry = preg_replace('/\{PREV,[^\}]+}/', '', $blog_entry);
			}
		}
		else
		{
			
			if (sizeof($prev) > 0)
			{
				$_link = '<a href="'.$body->url(CURRENT_ALIAS.'/'.$prev['alias']).'">&laquo; Previous</a>';
				$blog_entry = str_replace('PREV', $_link, $blog_entry);
				unset($_link);
			}
			else
			{
				$blog_entry = str_replace('PREV', '', $blog_entry);
			}
		}
		
		// NEXT
		
		preg_match('/\{NEXT,([^\}]+)\}/', $blog_entry, $matches);
		if (isset($matches[1]))
		{
			$_link  = $body->url(CURRENT_ALIAS.'/'.$next['alias']);
			
			if (is_array($next))
			{
				// replace with link
				$replace = str_replace('NEXT_LINK', $_link, $matches[1]);
				$blog_entry = preg_replace('/\{NEXT,[^\}]+}/', $replace, $blog_entry);
			}
			else
			{
				// replace with nothing
				$blog_entry = preg_replace('/\{NEXT,[^\}]+}/', '', $blog_entry);
			}
		}
		else
		{
			
			if (sizeof($next) > 0)
			{
				$_link = '<a href="'.$body->url(CURRENT_ALIAS.'/'.$next['alias']).'">&laquo; Next</a>';
				$blog_entry = str_replace('NEXT', $_link, $blog_entry);
				unset($_link);
			}
			else
			{
				$blog_entry = str_replace('NEXT', '', $blog_entry);
			}
		}
		
		$blog_entry = str_replace('LINK', $link, $blog_entry);
		
		
		/*
		
		if (sizeof($next) > 0)
		{
			$_link = '<a href="'.$body->url(CURRENT_ALIAS.'/'.$next['alias']).'">Next &raquo;</a>';
			$blog_entry = str_replace('NEXT', $_link, $blog_entry);
			unset($_link);
		}
		else
		{
			$blog_entry = str_replace('NEXT', '', $blog_entry);
		}
		
		$prev = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `date`<? ORDER BY `date` DESC LIMIT 1', $component_id, $entry['date']);
		if (sizeof($prev) > 0)
		{
			$_link = '<a href="'.$body->url(CURRENT_ALIAS.'/'.$prev['alias']).'">&laquo; Previous</a>';
			$blog_entry = str_replace('PREV', $_link, $blog_entry);
			unset($_link);
		}
		else
		{
			$blog_entry = str_replace('PREV', '', $blog_entry);
		}*/
		
		$source_image  = 'includes/content/blog/storage/'.$entry['post_id'].'/'. $entry['story_image'];
		$image_replace = '';
		if (is_file($source_image))
		{
			blog_check_image_dir($entry['post_id']);
			// image replace
			if ($show_layout == 'full') 
			{
				$img_width  = (is_numeric($image_settings['full_width'])) ? $image_settings['full_width'] : 200;
				$img_height = (is_numeric($image_settings['full_height'])) ? $image_settings['full_height'] : 150;
				$extension  = ($image_settings['full_crop'] == 1) ? 'jpg' : 'png';
			}
			else
			{
				$img_width  = (is_numeric($image_settings['preview_width'])) ? $image_settings['preview_width'] : 200;
				$img_height = (is_numeric($image_settings['preview_height'])) ? $image_settings['preview_height'] : 150;
				$extension  = ($image_settings['preview_crop'] == 1) ? 'jpg' : 'png';
			}
			
			$img_func = ($extension == 'png') ? 'make_new_image_ws' : 'make_new_image';
			$image_file = 'includes/content/blog/storage/'.$entry['post_id'].'/' . md5($entry['story_image'] . '_' . $img_width . '_' . $img_height) . '.' . $extension;
			if (!is_file($image_file))
			{
				call_user_func($img_func, $source_image, $image_file, $img_width, $img_height);
			}
			
			if (is_file($image_file))
			{
				$image_replace = '<img src="'.$body->url($image_file).'" />';
			}
		}
		$blog_entry = str_replace('IMAGE', $image_replace, $blog_entry);
		
		if (preg_match('/\{STORY,(\d+)\}/', $blog_entry, $matches))
		{
			$num_words = $matches[1];
			$story     = strip_tags($story);
			$words     = explode(' ', $story);
			while (sizeof($words) > $num_words)
			{
				array_pop($words);
			}
			$story = implode(' ', $words) . '...';
			
			$blog_entry = preg_replace('/\{STORY,\d+\}/', $story, $blog_entry);
		}
		else
		{
			$blog_entry = str_replace('STORY', $story, $blog_entry);
		}
		
		if ($entry['allow_comments'] == 1)
		{
			$blog_entry = str_replace('NUM_COMMENTS', $num_comments, $blog_entry);
			$blog_entry = str_replace('TOGGLE_COMMENTS', $toggle, $blog_entry);
			$blog_entry = str_replace('COMMENTS', $comments, $blog_entry);
			
			if (preg_match('/COMMENT\{.*?\}/s', $blog_entry))
			{
				$blog_entry = preg_replace('/COMMENT\{(.*?)\}/s', '\\1', $blog_entry);
			}
		}
		else
		{
			$blog_entry = str_replace('NUM_COMMENTS', '', $blog_entry);
			$blog_entry = str_replace('TOGGLE_COMMENTS', '', $blog_entry);
			$blog_entry = str_replace('COMMENTS', '', $blog_entry);
			
			if (preg_match('/COMMENT\{.*?\}/s', $blog_entry))
			{
				$blog_entry = preg_replace('/COMMENT\{(.*?)\}/s', '', $blog_entry);
			}
		}
	?>
<div class="blog_entry"><?=$blog_entry?></div>
	<?php
	}
	
	if ($options['show_bottom_nav'] == 1)
	{
		echo '<div class="bottom-nav">'.$bottom_nav.'</div>';
	}
}

if ($options['show_rss'] == 2)
{
	// show rss link
	echo $rss_link;
}
?>