<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

require_once('includes/content/blog/functions.php');

$action = $_REQUEST['page_action'];

if ($action == 'install')
{
	$component_id = $_POST['component_id'];
	$layout       = stripslashes($_POST['layout']);
	$f_layout     = stripslashes($_POST['full_layout']);
	$c_layout     = stripslashes($_POST['comment_layout']);
	$num_entries  = (is_numeric($_POST['num_entries'])) ? $_POST['num_entries'] : 3;
	
	$show_short_layout = $_POST['show_short_layout'];
	if (!is_array($show_short_layout)) { $show_short_layout = array(); }
	
	$image_settings = serialize($_POST['image_settings']);
	
	$count = $db->result('SELECT count(1) FROM `'.$blog_options.'` WHERE `component_id`=?', $component_id);
	if ((int) $count === 0)
	{
		$result = $db->run('INSERT INTO `'.$blog_options.'` (`component_id`, `allow_comments`, `show_rss`, `moderator_address`, `hide_expired`, `layout`, `comment_layout`, `full_layout`, `num_entries`, `show_short_layout`, `show_bottom_nav`, `image_settings`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
			$component_id, $_POST['allow_comments'], $_POST['show_rss'], $_POST['moderator_address'], $_POST['hide_expired'], $layout, $c_layout, $f_layout, $num_entries, serialize($show_short_layout), $_POST['show_bottom_nav'], $image_settings);
	}
	else
	{
		$result = $db->run('UPDATE `'.$blog_options.'` SET `allow_comments`=?, `show_rss`=?, `moderator_address`=?, `layout`=?, `comment_layout`=?, `hide_expired`=?, `full_layout`=?, `num_entries`=?, `show_short_layout`=?, `show_bottom_nav`=?, `image_settings`=? WHERE `component_id`=?', 
			$_POST['allow_comments'], $_POST['show_rss'], $_POST['moderator_address'], $layout, $c_layout, $_POST['hide_expired'], $f_layout, $num_entries, serialize($show_short_layout), $_POST['show_bottom_nav'], $image_settings, $component_id);
	}
}

if ($action == 'start_post')
{
	$title = trim(stripslashes($_POST['title']));
	$date  = trim(stripslashes($_POST['date']));
	$component_id = $_POST['component_id'];
	
	$check = $db->result('SELECT count(1) FROM `'.$blog_entries.'` WHERE `alias` =? AND `component_id`=?', PageNameToAlias($title), $component_id);
	
	if ($check > 0)
	{
		//if ($check === FALSE) { echo $db->error; exit(); }
		exit('1|That title is already in use, please change your title and try again');
	}
	elseif (strlen($title) == 0)
	{
		exit('1|You must specify a unique title');
	}
	else
	{
		$date_ts = strtotime($date);
		$alias   = PageNameToAlias($title);
		
		$new_story = $db->insert('INSERT INTO `'.$blog_entries.'` (`component_id`, `date`, `title`, `tags`, `post`, `category`, `alias`,`published`) VALUES (?,?,?,?,?,?,?,?)', 
			$component_id, $date_ts, $title, '', '', 0, $alias, 0
		);
		exit('0|'.$new_story);
	}
}
elseif ($action == 'draft')
{
	// prepare to autosave
	$blog_entry_text = trim(stripslashes($_POST['blog_entry_text']));
	
	$entry_id = $_POST['entry_id'];
	
	//$auto_saved_post = $db->result('SELECT `auto_saved_post` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$current_post    = $db->result('SELECT `post` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$difference      = abs(strlen($blog_entry_text) - strlen($current_post));
	
	if ($difference >= 100)
	{
		// auto save
		$db->run('UPDATE `'.$blog_entries.'` SET `auto_saved_post`=?, `auto_saved_date`=? WHERE `post_id`=?',
			$blog_entry_text, time(), $entry_id
		);
		
		echo 'Autosaved... ' . date('h:ia');
	}
}
elseif ( ($action == 'edit_story') or ($action == 'publish') )
{
	$component_id = $_POST['component_id'];
	$date         = (strlen($_POST['date']) > 0) ? strtotime($_POST['date']) : time();
	$title        = trim(stripslashes($_POST['title']));
	$story        = stripslashes($_POST['blog_entry_text']);
	$entry_id     = $_POST['entry_id'];
	$published      = ($_POST['published'] == 1) ? 1 : 0;
	$allow_comments = ($_POST['allow_comments'] == 1) ? 1 : 0;
	
	if ($action == 'publish')
	{
		$published = 1;
	}
	
	$tags = explode(',', $_POST['tags']);
	if (sizeof($tags) > 0)
	{
		foreach ($tags as $key=>$val)
		{
			$tags[$key] = stripslashes(trim($val));
		}
	}
	$tags = serialize($tags);
	
	$current_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	
	// save post
	
	$last_saved_post = $current_info['post'];
	$last_saved_date = $current_info['saved_date'];
	
	$allow_comments  = (isset($_POST['allow_comments'])) ? $_POST['allow_comments'] : 0;
	
	$update_story = $db->run('UPDATE `'.$blog_entries.'` SET `date`=?, `saved_date`=?, `tags`=?, `post`=?, `category`=?, `published`=?, `allow_comments`=?, `last_saved_post`=?, `last_saved_date`=? WHERE `post_id`=?',
		$date, time(), $tags, $story, $_POST['category'], $published, $allow_comments, $last_saved_post, $last_saved_date, $entry_id
	);
	
	// change title (if needed)
	
	
	if ($current_info['title'] != $title)
	{
		$check = $db->result('SELECT count(1) FROM `'.$blog_entries.'` WHERE `alias` =? AND `component_id`=?', PageNameToAlias($title), $component_id);
		if ( ($check > 0) or (strlen($title) == 0) )
		{
			//if ($check === FALSE) { echo $db->error; exit(); }
			echo 'Post saved, but the title you entered is invalid.';
			exit();
		}
		else
		{
			$db->run('UPDATE `'.$blog_entries.'` SET `title`=?, `alias`=? WHERE `post_id`=?',
				$title, PageNameToAlias($title), $entry_id
			);
		}
	}
	
	echo 'Post saved.';
	exit();
}

if ($action == 'delete_entry')
{
	$entry_id = $_GET['entry_id'];
	if (is_numeric($entry_id))
	{
		$db->run('DELETE FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	}
	exit();
}

if ($action == 'add_category')
{
	$component_id = $_GET['component_id'];
	
	$title = stripslashes($_GET['category']);
	$alias = PageNameToAlias($title);
	
	$check = $db->result('SELECT count(1) FROM `'.$blog_categories.'` WHERE `alias`=?', $alias);
	if ($check == 1)
	{
		echo 'That category already exists';
		exit();
	}
	elseif (strlen($alias) == 0)
	{
		echo 'Invalid category name';
		exit();
	}
	else
	{
		$new_category = $db->insert('INSERT INTO `'.$blog_categories.'` (`title`, `component_id`, `alias`) VALUES (?,?,?)', 
			$title, $component_id, $alias
		);
	}
	
	exit();
}

if ($action == 'reload_category')
{
	$component_id = $_GET['component_id'];
	echo get_category_drop('category', $component_id, '');
}

if ($action == 'edit_category')
{
	$category_id  = $_GET['category_id'];
	$new_name     = urldecode($_GET['category']);
	$new_alias    = PageNameToAlias($new_name);
	
	$old_alias = $db->result('SELECT `alias` FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
	$check = $db->result('SELECT count(1) FROM `'.$blog_categories.'` WHERE `alias`=?', $new_alias);
	
	if ( ($check == 1) and ($old_alias != $new_alias) )
	{
		echo 'That category already exists';
		exit();
	}
	elseif (strlen($new_alias) == 0)
	{
		echo 'Invalid category name';
		exit();
	}
	else
	{
		$db->run('UPDATE `'.$blog_categories.'` SET `title`=?, `alias`=? WHERE `category_id`=?', $new_name, $new_alias, $category_id);
	}
	
	exit();
}

if ($action == 'delete_category')
{
	$category_id  = $_GET['category_id'];
	$component_id = $db->result('SELECT `component_id` FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
	
	$db->run('DELETE FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
	$db->run('UPDATE `'.$blog_entries.'` SET `category`=0 WHERE `category`=?', $category_id);
	exit();
}

if ($action == 'blog_image')
{
	$filename = urldecode($_GET['filename']);
	$entry_id = $_GET['entry_id'];
	
	// see if this entry has any other files
	$saved_image  = $db->result('SELECT `story_image` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$storage      = 'includes/content/blog/storage/';
	$full_file    = $storage . $entry_id . '/' .$saved_image;
	$preview_file = $storage . $entry_id . '/' . md5('preview_'.$saved_image) . '.png';
	
	if (is_file($full_file)) { unlink($full_file); }
	if (is_file($preview_file)) { unlink($preview_file); }
	// end: file check
	
	$tmp_file = 'includes/tmp/' . $filename;
	if (!is_file($tmp_file))
	{
		exit('Could not find file');
	}
	else
	{
		$db->run('UPDATE `'.$blog_entries.'` SET `story_image`=? WHERE `post_id`=?', $filename, $entry_id);
		// move the file
		$storage = 'includes/content/blog/storage/'.$entry_id.'/';
		$new_file = $storage . $filename;
		
		rename($tmp_file, $new_file);
		chmod($new_file, 0666);
	}
	exit();
}

if ($action == 'delete_blog_image')
{
	$entry_id = $_GET['entry_id'];
	
	$saved_image  = $db->result('SELECT `story_image` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$storage      = 'includes/content/blog/storage/';
	$full_file    = $storage . $entry_id . '/' .$saved_image;
	$preview_file = $storage . $entry_id . '/' . md5('preview_'.$saved_image) . '.png';
	
	if (is_file($full_file)) { unlink($full_file); }
	if (is_file($preview_file)) { unlink($preview_file); }
	
	$db->run('UPDATE `'.$blog_entries.'` SET `story_image`=? WHERE `post_id`=?', '', $entry_id);
	exit();
}

if ($action == 'delete_comment')
{
	$comment_id = $_GET['comment_id'];
	$db->run('DELETE FROM `'.$blog_comments.'` WHERE `comment_id`=? LIMIT 1', $comment_id);
	exit();
}

if ($action == 'approve_comment')
{
	$comment_id = $_GET['comment_id'];
	$db->run('UPDATE `'.$blog_comments.'` SET `active`=? WHERE `comment_id`=?', 1, $comment_id);
	exit();
}

?>