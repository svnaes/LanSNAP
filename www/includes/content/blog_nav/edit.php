<?php
$options = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);

$settings = unserialize($options);
if (!is_array($settings)) { $settings = array(); }

$choose_blog = '';
$blogs = $db->force_multi_assoc('SELECT `component_id`, `description` FROM `'.DB_COMPONENT_TABLE.'` WHERE `folder`=?', 'blog');

$blog_drop = '<select name="settings[blog]"><option value=""></option>';
if ( (is_array($blogs)) and (sizeof($blogs) > 0) )
{
	foreach($blogs as $blog)
	{
		$selected = ($blog['component_id'] == $settings['blog']) ? 'selected="selected"' : '';
		$blog_drop .= '<option value="'.$blog['component_id'].'" '.$selected.'>'.$blog['description'].'</option>';
	}
}
$blog_drop .= '</select>';
?>
<h3>Settings</h3>
<form id="blognav_settings" method="post" action="<?=$body->url('includes/content/blog_nav/submit.php')?>" onsubmit="BlogNav_Update(this); return false">
<input type="hidden" name="instance_id"  value="<?=$instance_id?>" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action"  value="update" />
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td class="bold">Blog Source</td>
	<td colspan="2"><?=$blog_drop?></td>
</tr>
<tr>
	<td class="bold">This Month</td>
	<td>Enabled <input type="checkbox" name="settings[this_month]" value="enabled" <?=($settings['this_month'] == 'enabled') ? 'checked="checked"': '' ?>/></td>
	<td>Position: <input type="text" size="3" maxlength="3" name="settings[this_month_position]" value="<?=$settings['this_month_position']?>" /></td>
	<td>Label: <input type="text" name="settings[this_month_label]" value="<?=$settings['this_month_label']?>" /></td>
	<td>
		View: <select name="settings[this_month_view]">
			<option value="list"     <?=($settings['this_month_view'] == 'list')?'selected="selected"':''?>>List</option>
			<option value="dropdown" <?=($settings['this_month_view'] == 'dropdown')?'selected="selected"':''?>>Dropdown</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Archives</td>
	<td>Enabled <input type="checkbox" name="settings[archives]" value="enabled" <?=($settings['archives'] == 'enabled') ? 'checked="checked"': '' ?>/></td>
	<td>Position: <input type="text" size="3" maxlength="3" name="settings[archives_position]" value="<?=$settings['archives_position']?>" /></td>
	<td>Label: <input type="text" name="settings[archives_label]" value="<?=$settings['archives_label']?>" /></td>
	<td>
		View: <select name="settings[archives_view]">
			<option value="list"     <?=($settings['archives_view'] == 'list')?'selected="selected"':''?>>List</option>
			<option value="dropdown" <?=($settings['archives_view'] == 'dropdown')?'selected="selected"':''?>>Dropdown</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Categories</td>
	<td>Enabled <input type="checkbox" name="settings[categories]" value="enabled" <?=($settings['categories'] == 'enabled') ? 'checked="checked"': '' ?>/></td>
	<td>Position: <input type="text" size="3" maxlength="3" name="settings[categories_position]" value="<?=$settings['categories_position']?>" /></td>
	<td>Label: <input type="text" name="settings[categories_label]" value="<?=$settings['categories_label']?>" /></td>
	<td>
		View: <select name="settings[categories_view]">
			<option value="list"     <?=($settings['categories_view'] == 'list')?'selected="selected"':''?>>List</option>
			<option value="dropdown" <?=($settings['categories_view'] == 'dropdown')?'selected="selected"':''?>>Dropdown</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Tags</td>
	<td>Enabled <input type="checkbox" name="settings[tags]" value="enabled" <?=($settings['tags'] == 'enabled') ? 'checked="checked"': '' ?>/></td>
	<td>Position: <input type="text" size="3" maxlength="3" name="settings[tags_position]" value="<?=$settings['tags_position']?>" /></td>
	<td>Label: <input type="text" name="settings[tags_label]" value="<?=$settings['tags_label']?>" /></td>
	<td>
		View: <select name="settings[tags_view]">
			<option value="list"     <?=($settings['tags_view'] == 'list')?'selected="selected"':''?>>List</option>
			<option value="dropdown" <?=($settings['tags_view'] == 'dropdown')?'selected="selected"':''?>>Dropdown</option>
			<option value="dynamic" <?=($settings['tags_view'] == 'dynamic')?'selected="selected"':''?>>Dynamic</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">All Posts</td>
	<td>Enabled <input type="checkbox" name="settings[all]" value="enabled" <?=($settings['all'] == 'enabled') ? 'checked="checked"': '' ?>/></td>
	<td>Position: <input type="text" size="3" maxlength="3" name="settings[all_position]" value="<?=$settings['all_position']?>" /></td>
	<td>Label: <input type="text" name="settings[all_label]" value="<?=$settings['all_label']?>" /></td>
	<td>
		View: <select name="settings[all_view]">
			<option value="list"     <?=($settings['all_view'] == 'list')?'selected="selected"':''?>>List</option>
			<option value="dropdown" <?=($settings['all_view'] == 'dropdown')?'selected="selected"':''?>>Dropdown</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Latest Posts</td>
	<td>Enabled <input type="checkbox" name="settings[latest_posts]" value="enabled" <?=($settings['latest_posts'] == 'enabled') ? 'checked="checked"': '' ?>/></td>
	<td>Position: <input type="text" size="3" maxlength="3" name="settings[latest_posts_position]" value="<?=$settings['latest_posts_position']?>" /></td>
	<td>Label: <input type="text" name="settings[latest_posts_label]" value="<?=$settings['latest_posts_label']?>" /></td>
	<td>
		View: <select name="settings[latest_posts_view]">
			<option value="list"     <?=($settings['latest_posts_view'] == 'list')?'selected="selected"':''?>>List</option>
			<option value="dropdown" <?=($settings['latest_posts_view'] == 'dropdown')?'selected="selected"':''?>>Dropdown</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Yearly</td>
	<td>Enabled <input type="checkbox" name="settings[yearly]" value="enabled" <?=($settings['yearly'] == 'enabled') ? 'checked="checked"': '' ?>/></td>
	<td>Position: <input type="text" size="3" maxlength="3" name="settings[yearly_position]" value="<?=$settings['yearly_position']?>" /></td>
	<td>Label: <input type="text" name="settings[yearly_label]" value="<?=$settings['yearly_label']?>" /></td>
	<td>
		View: <select name="settings[yearly_view]">
			<option value="list"     <?=($settings['yearly_view'] == 'list')?'selected="selected"':''?>>List</option>
			<option value="dropdown" <?=($settings['yearly_view'] == 'dropdown')?'selected="selected"':''?>>Dropdown</option>
		</select>
	</td>
</tr>
</table>
<input type="submit" value="Update" />
</form>