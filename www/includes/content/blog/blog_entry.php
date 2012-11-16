<?php
chdir('../../../');
require_once('core.php');
require_once('includes/content/blog/functions.php');

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

$blog_options    = DB_PREFIX . 'pico_blog_options';
$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$blog_categories = DB_PREFIX . 'pico_blog_categories';
$blog_comments   = DB_PREFIX . 'pico_blog_comments';

$entry_id = $_GET['id'];
$entry_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);

$tags = unserialize($entry_info['tags']);
if (!is_array($tags)) { $tags = array(); }

$tags = implode(', ', $tags);
$pcheck = ($entry_info['published'] == 1) ? 'checked="checked"' : '';
$ccheck = ($entry_info['allow_comments'] == 1) ? 'checked="checked"' : '';
$component_id = $entry_info['component_id'];

$save_table = <<<HTML
<table border="0" cellpadding="0" cellspacing="0">
<tr>
	<td style="width: 450px"><div id="blog_status"></div></td>
	<td>
		<button onclick="Blog_Save(); return false">Save</button>
		<button onclick="Blog_Home(); return false">Back</button>
	</td>
</tr>
</table>
HTML;

$save_table2 = <<<HTML
<table border="0" cellpadding="0" cellspacing="0">
<tr>
	<td style="width: 450px"><div id="blog_status2"></div></td>
	<td>
		<button onclick="Blog_Save(); return false">Save</button>
		<button onclick="Blog_Home(); return false">Back</button>
	</td>
</tr>
</table>
HTML;

?>
<table border="0" cellpadding="0" cellspacing="0" class="blog_tablist">
<tr>
	<td><div class="tab_button" onclick="Blog_TabActivate('blog_content')">Content</div></td>
	<td><div class="tab_button" onclick="Blog_TabActivate('blog_options')">Options</div></td>
	<td><div class="tab_button" onclick="Blog_TabActivate('blog_saves')">History</div></td>
	<td><div class="tab_button" onclick="Blog_TabActivate('blog_image')">Image</div></td>
	<td><div class="tab_button" onclick="Blog_TabActivate('blog_comments')">Comments</div></td>
</tr>
</table>

<form method="post" id="blog_content_form" style="height: auto" action="<?=$body->url('includes/content/blog/submit.php')?>" onsubmit="return false">
<input type="hidden" name="page_action" value="edit_story" />
<input type="hidden" name="entry_id" value="<?=$entry_id?>" />
<input type="hidden" name="blog_entry_text" value="" />
<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />

<div class="tabbed_content" style="display: block" id="blog_content">
	<textarea name="blog_story" id="blog_story"><?=$entry_info['post']?></textarea>
	<?=$save_table?>
</div>
<div class="tabbed_content" id="blog_options">
	<div class="ap_overflow" style="height: 425px">
		<h3 class="blog_choice">Options</h3>
		
		<table border="0" cellpadding="0" cellspacing="0" class="blog_options">
		<tr>
			<td>Title</td>
			<td colspan="2"><input type="text" class="ap_text" name="title" value="<?=$entry_info['title']?>" /></td>
		</tr>
		<tr>
			<td>Date</td>
			<td colspan="2"><input type="text" class="ap_text" name="date" value="<?=date('m/d/Y', $entry_info['date'])?>" /></td>
		</tr>
		<tr>
			<td>Tags</td>
			<td colspan="2"><textarea name="tags" class="ap_textarea"><?=$tags?></textarea></td>
		</tr>
		<tr>
			<td>Category</td>
			<td width="10">
				<div id="blog_category"><?=get_category_drop('category', $component_id, $entry_info['category'])?></div>
			</td>
			<td>
				<img src="<?=$body->url('includes/icons/plus.png')?>" title="Add Category" class="click icon" onclick="Blog_AddCategory()" />
				<img src="<?=$body->url('includes/icons/edit.png')?>" title="Rename Category" class="click icon" onclick="Blog_RenameCategory()" />
				<img src="<?=$body->url('includes/icons/delete.png')?>" title="Delete Category" class="click icon" onclick="Blog_DeleteCategory()" />
			</td>
		</tr>
		<tr>
			<td>Published</td>
			<td colspan="2"><input type="checkbox" name="published" value="1" <?=$pcheck?> /></td>
		</tr>
		<tr>
			<td>Allow Comments</td>
			<td colspan="2"><input type="checkbox" name="allow_comments" value="1" <?=$ccheck?> /></td>
		</tr>
		</table>
		<?=$save_table2?>
		</form>
	</div>
</div>
<div class="tabbed_content" id="blog_saves">
	<div class="ap_overflow" style="height: 425px">
		<div id="blog_restore">
		<?php
		include('includes/content/blog/blog_restore.php');
		?>
		</div>
	</div>
</div>
<div class="tabbed_content" id="blog_image">
	<div id="blog_image">
		<?php
		include('includes/content/blog/blog_image.php');
		?>
	</div>
</div>
<div class="tabbed_content" id="blog_comments">
	<div id="blog_admin_comments">
		<?php
		include('includes/content/blog/blog_comments.php');
		?>
	</div>
</div>