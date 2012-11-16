<?php
if (isset($_GET['instance_id']))
{
	chdir('../../../');
	require_once('core.php');
	require_once('includes/content/media/functions.php');
	$instance_id  = $_GET['instance_id'];
	$component_id = $_GET['component_id'];
	$category_id  = $_GET['category_id'];
	$media_files      = DB_PREFIX . 'pico_media_files';
	$media_categories = DB_PREFIX . 'pico_media_categories';
	$gallery_options  = gallery_get_base_options($component_id);
	
}

if (USER_ACCESS < 3) { exit(); }

if ($gallery_options['categories'] == TRUE)
{
	echo '<div class="click" onclick="MG_ShowCategories()">[back to categories]</div>';
	
	
	$images = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? AND `category_id`=? ORDER BY `position` ASC', $instance_id, $category_id);
}
else
{
	$images = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
}
$image_preview = '';
if ( (sizeof($images) > 0) and (is_array($images)) )
{
	foreach ($images as $image)
	{
		$_file = 'includes/content/media/files/'.$image['file_id'] . '_thumb.jpg';
		if (!file_exists($_file))
		{
			$_file = 'includes/content/media/files/'.$image['file_id'] . '_thumb.png';
		}
		$image_path = $body->url($_file);
		
		$id    = $image['file_id'];
		$left  = $body->url('includes/icons/left-arrow.png');
		$right = $body->url('includes/icons/right-arrow.png');
		
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'" class="click" title="Delete Image" onclick="MG_DeleteImage('.$id.')" />';
		$top    = '<img src="'.$body->url('includes/icons/arrow-up.png').'" class="click" title="Move to Top" onclick="MG_FileTop('.$id.')" />';
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'" class="click" title="Edit Image" onclick="MG_EditImage('.$id.')" />';
		
		$image_preview .= <<<HTML
<div class="image_holder">
	<table border="0" cellpadding="0" cellspacing="1">
	<tr>
		<td><img src="$left" class="clickable" onclick="MG_MoveImage($id, 'up')" /></td>
		<td width="100" height="100" class="clickable" onclick="MG_EditImage($id)">
			<img src="$image_path" />
		</td>
		<td><img src="$right" class="clickable" onclick="MG_MoveImage($id, 'down')" /></td>
	</tr>
	<tr>
		<td colspan="3" style="text-align: center">$top $delete $edit</td>
	</tr>
	</table>
</div>
HTML;
	}
}

echo $image_preview;

if (!is_numeric($category_id)) { $category_id = 0; }
$swf_path = $body->url('includes/content/media/FileUploader.swf');

$upload_path = $body->url('includes/content/media/upload.php');
$uploader    = new Uploader($upload_path, 'MG_AddFile', 'MG_RefreshImages', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'ffffff');
?>
<div class="clear"></div>
<input type="hidden" id="category_id" value="<?=$category_id?>" />
<h3>Upload Images</h3>
<?=$uploader->Output()?>