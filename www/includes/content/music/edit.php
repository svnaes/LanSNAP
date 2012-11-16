<?php
$music_entries = DB_PREFIX . 'music_data';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$music_entries` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`component_id` BIGINT(11) NOT NULL,
	`music_file` VARCHAR(255) NOT NULL,
	`image_file` VARCHAR(255) NOT NULL,
	`position` INT(8) NOT NULL DEFAULT 0,
	`artist` VARCHAR(255) NOT NULL,
	`song_name` VARCHAR(255) NOT NULL,
	`instance_id` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`entry_id`)
);
SQL
);

$fields = $db->assoc('SHOW COLUMNS FROM `'.$music_entries.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (!in_array('instance_id', $all_fields))
{
	$db->run('ALTER TABLE `'.$music_entries.'` ADD COLUMN `instance_id` VARCHAR(255) NOT NULL');
}

$upload_path = $body->url('includes/content/music/upload.php');
//$uploader = new Uploader($upload_path, 'Browse_FileUploaded', 'Browse_FilesUploaded', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'cccccc');

$uploader = new Uploader($upload_path, 'MP3_FileUploaded', '', '.mp3, .jpg', 'Custom Files (mp3/jpg)', '000000', 'trans');

// verify upload folders are writable
CheckWritable('includes/content/music/upload/');
CheckWritable('includes/content/music/storage/');

?>

<h3>Add a new song</h3>
<?=$uploader->Output()?>
<br />

<form method="post" id="mp3form" action="<?=$body->url('includes/content/music/submit.php')?>" onsubmit="MP3_Submit(this); return false">
<input type="hidden" name="page_action" value="add" />
<input type="hidden" id="component_id" name="component_id" value="<?=$component_id?>" />
<input type="hidden" id="instance_id" name="instance_id" value="<?=$instance_id?>" />
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td>Song File</td>
	<td><input type="text" name="music_file" readonly="readonly" class="disabled" /></td>
</tr>
<tr>
	<td>Image File (optional)</td>
	<td><input type="text" name="image_file" readonly="readonly" class="disabled" /></td>
</tr>
<tr>
	<td>Artist (Top Line)</td>
	<td><input type="text" name="artist" /></td>
</tr>
<tr>
	<td>Song Name (Lower Line)</td>
	<td><input type="text" name="song_name" /></td>
</tr>
</table>
<input type="submit" name="submitbtn" value="Add" disabled="disabled" />
</form>
