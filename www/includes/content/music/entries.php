<?php
if ($_GET['reload'] == 'true')
{
	chdir('../../../');
	require_once('core.php');
	if (USER_ACCESS < 3) { exit(); }
	$component_id = $_GET['component_id'];
	$instance_id  = $_GET['instance_id'];
}

$music_entries = DB_PREFIX . 'music_data';

$entries = $db->force_multi_assoc('SELECT * FROM `'.$music_entries.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
$table   = '';

if ( (is_array($entries)) and (sizeof($entries) > 0) )
{
	$counter = 0;
	foreach ($entries as $entry)
	{
		$class       = ($counter %2 ==0) ? 'a' : 'b'; $counter++;
		$storage     = 'includes/content/music/storage/'.$entry['entry_id'].'/';
		$check_image = $storage . $entry['image_file'];
		$image       = ((file_exists($check_image)) and (is_file($check_image))) ? '<img width="32" height="25" src="'.$body->url($check_image).'" />' : '';
		
		$up     = '<img src="'.$body->url('includes/icons/arrow-up.png').'" class="click icon"   onclick="MP3_Move('.$entry['entry_id'].', \'up\')" />';
		$down   = '<img src="'.$body->url('includes/icons/arrow-down.png').'" class="click icon" onclick="MP3_Move('.$entry['entry_id'].', \'up\')" />';
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'" class="click icon"     onclick="MP3_Delete('.$entry['entry_id'].')" />';
	$table .= '
<tr class="'.$class.'">
	<td>'.$image.'</td>
	<td>'.$entry['music_file'].'</td>
	<td class="click" onclick="MP3_Update(this, '.$entry['entry_id'].', \'artist\')">'.$entry['artist'].'</td>
	<td class="click" onclick="MP3_Update(this, '.$entry['entry_id'].', \'song_name\')">'.$entry['song_name'].'</td>
	<td>'.$up.$down.$delete.'</td>
</tr>
';
	}
}
?>
<div id="entry_window">
	<table border="0" cellpadding="2" cellspacing="1" class="list">
		<tr>
			<th>Image</th>
			<th>Song</th>
			<th>Artist</th>
			<th>Title</th>
			<th>Actions</th>
		</tr>
		<?=$table?>
	</table>
</div>