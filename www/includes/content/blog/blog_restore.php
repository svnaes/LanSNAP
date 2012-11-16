<?php
if (isset($_GET['refresh']))
{
	$entry_id = $_GET['entry_id'];
	chdir('../../../');
	require_once('core.php');
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

if (!isset($entry_info))
{
	$blog_options    = DB_PREFIX . 'pico_blog_options';
	$blog_entries    = DB_PREFIX . 'pico_blog_entries';
	$blog_categories = DB_PREFIX . 'pico_blog_categories';
	$blog_comments   = DB_PREFIX . 'pico_blog_comments';

	$entry_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
}
/*
$lup   = strip_tags($entry_info['last_saved_post']);
$words = explode(' ', $lup);
$slice = array_slice($words, 0, 50);
$lup   = implode(' ', $slice);

$lap  = strip_tags($entry_info['auto_saved_post']);
$words = explode(' ', $lup);
$slice = array_slice($words, 0, 50);
$lap   = implode(' ', $slice);
*/
?>
<h3 class="blog_choice">Restore Previous/Auto Save</h3>
<table border="0" cellpadding="0" cellspacing="0" class="blog_options">
<tr>
	<td class="bold">Last User-Saved Post - <?=date('h:i:sa m/d/Y', $entry_info['last_saved_date'])?></td>
</tr><tr>
	<td>
		<textarea id="last_user_saved_post"><?=$entry_info['last_saved_post']?></textarea>
	</td>
</tr>
<tr>
	<td class="bold">Last Auto-Saved Post - <?=date('h:i:sa m/d/Y', $entry_info['auto_saved_date'])?></td>
</tr><tr>
	<td>
		<textarea id="last_auto_saved_post"><?=$entry_info['auto_saved_post']?></textarea>
	</td>
</tr>
</table>