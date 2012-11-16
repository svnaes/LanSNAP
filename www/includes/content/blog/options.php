<?php
require_once('includes/content/blog/functions.php');
$options = $db->assoc('SELECT * FROM `'.$blog_options.'` WHERE `component_id`=?', $component_id);

$show_short_layout = unserialize($options['show_short_layout']);
if (!is_array($show_short_layout)) { $show_short_layout = array(); }

$image_settings = unserialize($options['image_settings']);

?>
<div class="ap_overflow">
<form method="post" action="<?=$body->url('includes/content/blog/submit.php')?>" onsubmit="Blog_UpdateOptions(this); return false">
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action" value="install" />
<h3 class="blog_choice">Blog Options</h3>
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td class="bold" width="170">Show RSS Link?</td>
	<td>
		<select name="show_rss">
			<option value="0" <?=($options['show_rss']==0)? 'selected="selected"' :''?>>No</option>
			<option value="1" <?=($options['show_rss']==1)? 'selected="selected"' :''?>>Yes - Top</option>
			<option value="2" <?=($options['show_rss']==2)? 'selected="selected"' :''?>>Yes - Bottom</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Comments Enabled?</td>
	<td>
		<select name="allow_comments">
			<option value="0" <?=($options['allow_comments']==0)? 'selected="selected"' :''?>>No</option>
			<option value="1" <?=($options['allow_comments']==1)? 'selected="selected"' :''?>>Yes - Automatically Approve</option>
			<option value="2" <?=($options['allow_comments']==2)? 'selected="selected"' :''?>>Yes - Moderation Required</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Display Bottom Nav</td>
	<td>
		<select name="show_bottom_nav">
			<option value="0" <?=($options['show_bottom_nav']==0)? 'selected="selected"' :''?>>No</option>
			<option value="1" <?=($options['show_bottom_nav']==1)? 'selected="selected"' :''?>>Yes</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Number of entries to show</td>
	<td>
		<input type="text" name="num_entries" class="ap_text" value="<?=$options['num_entries']?>" />
	</td>
</tr>
<tr>
	<td class="bold">Moderator E-mail Address</td>
	<td>
		<input type="text" name="moderator_address" class="ap_text" value="<?=$options['moderator_address']?>" />
	</td>
</tr>
<tr>
	<td class="bold">Hide expired entries</td>
	<td>
		<select name="hide_expired">
			<option value="0" <?=($options['hide_expired']==0)? 'selected="selected"' :''?>>No</option>
			<option value="1" <?=($options['hide_expired']==1)? 'selected="selected"' :''?>>Yes</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Preview Image Size</td>
	<td>
		Width: <input type="text" name="image_settings[preview_width]" size="5" value="<?=$image_settings['preview_width']?>" />
		Height: <input type="text" name="image_settings[preview_height]" size="5" value="<?=$image_settings['preview_height']?>" />
		Crop: <input type="checkbox" name="image_settings[preview_crop]" value="1" <?=(($image_settings['preview_crop']==1)?'checked="checked"' : '')?> />
	</td>
</tr>
<tr>
	<td class="bold">Full Image Size</td>
	<td>
		Width: <input type="text" name="image_settings[full_width]" size="5" value="<?=$image_settings['full_width']?>" />
		Height: <input type="text" name="image_settings[full_height]" size="5" value="<?=$image_settings['full_height']?>" />
		Crop: <input type="checkbox" name="image_settings[full_crop]" value="1" <?=(($image_settings['full_crop']==1)?'checked="checked"' : '')?> />
	</td>
</tr>
<tr>
	<td class="bold">Full Layout</td>
	<td>
		<textarea name="full_layout" class="ap_textarea_lg"><?=htmlspecialchars($options['full_layout'])?></textarea><br />
		Variables:<br />
		DATE, STORY, TITLE, CATEGORY, TAGS, LINK, TOGGLE_COMMENTS, COMMENTS, NUM_COMMENTS<br />
	</td>
</tr>
<tr>
	<td class="bold">Synopsis Layout</td>
	<td>
		<textarea name="layout" class="ap_textarea_lg"><?=htmlspecialchars($options['layout'])?></textarea><br />
		Show on:<br />
		<?php
		$show_short_areas = array('main', 'archives', 'tags', 'categories');
		
		
		foreach ($show_short_areas as $a)
		{
			$checked = (in_array($a, $show_short_layout)) ? 'checked="checked"' : '';
			echo '<input type="checkbox" name="show_short_layout[]" value="'.$a.'" '.$checked.' /> '.ucfirst($a).'<br />';
		}
		
		?>
	</td>
</tr>
<tr>
	<td class="bold">Comment Layout</td>
	<td>
		<textarea name="comment_layout" class="ap_textarea_lg"><?=$options['comment_layout']?></textarea><br />
		Variables:<br />
		NAME, DATE, EMAIL, MESSAGE
	</td>
</tr>

</table>

<input type="submit" name="submitbtn" value="Update" />
</form>
</div>