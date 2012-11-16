<?php
if (!isset($component_id))
{
	$component_id = $_GET['component_id'];
	$instance_id  = $_GET['instance_id'];
	chdir('../../../');
	require_once('core.php');
	if (USER_ACCESS < 3) { exit(); }

	$media_files      = DB_PREFIX . 'pico_media_files';
	$media_categories = DB_PREFIX . 'pico_media_categories';
	
	if (isset($_GET['edit']))
	{
		$action = 'edit_category';
		$extra  = '<input type="hidden" name="category_id" value="'.$_GET['edit'].'" />';
		$category_name = $db->result('SELECT `title` FROM `'.$media_categories.'` WHERE `category_id`=?', $_GET['edit']);
	}
	else
	{
		$action = 'add_category';
		$category_name = '';
		$extra = '';
	}
}
else
{
	$action = 'add_category';
	$category_name = '';
	$extra = '';
}

require_once('includes/content/media/functions.php');
$gallery_options = gallery_get_base_options($component_id);

if ($gallery_options['categories'] == FALSE)
{
	echo 'Categories are not available for this gallery';
	return;
}

//echo '<pre>'.print_r($options, TRUE).'</pre>';
?>
<h3>Current Categories:</h3>
<?php
$categories = $db->force_multi_assoc('SELECT * FROM `'.$media_categories.'` WHERE `component_id`=? ORDER BY `position` ASC', $component_id);
$table = '';
if ( (is_array($categories)) and (sizeof($categories) > 0) )
{
	foreach ($categories as $category)
	{
		$name   = $category['title'];
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'" class="icon click" onclick="MG_EditCategory('.$category['category_id'].')" />';
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'" class="icon click" onclick="MG_DeleteCategory('.$category['category_id'].')" />';
		$images = '<img src="'.$body->url('includes/icons/images.png').'" class="icon click" onclick="MG_LoadImages(\''.$instance_id.'\', '.$component_id.', '.$category['category_id'].')" />';
		$up     = '<img src="'.$body->url('includes/icons/arrow-up.png').'" class="icon click" onclick="MG_MoveCategory('.$category['category_id'].', \'up\')" />';
		$down   = '<img src="'.$body->url('includes/icons/arrow-down.png').'" class="icon click" onclick="MG_MoveCategory('.$category['category_id'].', \'down\')" />';
		$html   = '<img src="'.$body->url('includes/icons/content.png').'" class="icon click" onclick="MG_EditHTML('.$category['category_id'].')" />';
		
		$num_image = $db->result('SELECT count(1) FROM `'.$media_files.'` WHERE `category_id`=?', $category['category_id']);
		
		$table .= '<tr><td>'.$name.'</td><td>'.$num_image.'</td><td>'.$images.$edit.$delete.$up.$down.$html.'</td></tr>';
	}
}
?>
<table border="1" cellpadding="3" cellspacing="0">
<tr>
	<th>Category</th>
	<th>Number of Images</th>
	<th width="100">Actions</th>
</tr>
<?=$table?>
</table>

<h3><?=ucwords(str_replace('_', ' ', $action))?></h3>
<form method="post" action="<?=$body->url('includes/content/media/submit.php')?>" style="height: auto" onsubmit="MG_AddCategory(this); return false">
Category Name: <input type="text" name="category_name" value="<?=$category_name?>" /> <input type="submit" name="submitbtn" value="<?=ucwords(str_replace('_', ' ', $action))?>" />
	<?=$extra?>
	<input type="hidden" name="page_action" value="<?=$action?>" />
	<input type="hidden" name="component_id" value="<?=$component_id?>" />
</form>
