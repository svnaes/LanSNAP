<?php
if (isset($_GET['refresh']))
{
	$component_id = $_GET['component_id'];
	chdir('../../../');
	require_once('core.php');
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

$blog_options    = DB_PREFIX . 'pico_blog_options';
$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$blog_categories = DB_PREFIX . 'pico_blog_categories';
$blog_comments   = DB_PREFIX . 'pico_blog_comments';

require_once('includes/content/blog/check_database.php'); // make sure we have the latest database shtuff

$blog_posts = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? ORDER BY `date` DESC', $component_id);
$blog_op = '';
if (is_array($blog_posts))
{
	$blog_op = '<table border="0" cellpadding="0" cellspacing="0" class="blog_entry_list">';
	$counter = 0;
	foreach ($blog_posts as $entry)
	{
		$id     = $entry['post_id'];
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'" title="Edit" class="icon click" onclick="Blog_EditStory('.$id.')" />';
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'" title="Delete" class="icon click" onclick="Blog_DeleteEntry('.$id.')" />';
		$class  = ($counter % 2 == 0) ? 'a' : 'b';
		$counter++;
		
		$extra = ($entry['published'] == 0) ? '<span style="font-style: italic"> - unpublished</span>' : '';
		
		$blog_op .= '<tr class="'.$class.'">';
		$blog_op .= '<td class="title">'.$entry['title'].$extra.'</td>';
		$blog_op .= '<td class="date">'.date('m/d/Y', $entry['date']).'</td>';
		$blog_op .= '<td class="actions">'.$edit.$delete.'</td>';
		$blog_op .= '</tr>';
	}
	$blog_op .= '</table>';
}
else
{
}

?>
<input type="hidden" id="component_id" value="<?=$component_id?>" />
<div class="blog_pad">
	<div class="blog_home">
		<h3 class="blog_choice">Start a new post</h3>

		<form method="post" action="<?=$body->url('includes/content/blog/submit.php')?>" onsubmit="Blog_NewStory(this); return false" style="height: auto">
		<input type="hidden" name="page_action" value="start_post" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<table border="0" cellpadding="0" cellspacing="0" class="blog_start">
		<tr>
			<td>Post/Story Title:</td>
			<td><input type="text" name="title" value="" class="ap_text" /></td>
		</tr>
		<tr>
			<td>Post/Story Date:</td>
			<td><input type="text" name="date" value="<?=date('m/d/Y')?>"  class="ap_text" /></td>
		</tr>
		</table>
		<input type="submit" value="Start new post" />
		</form>

		<p class="blog_or">-or-</p>
		<h3 class="blog_choice">Edit an existing post</h3>
		<div class="blog_existing">
			<?=$blog_op?>
		</div>
	</div>
</div>