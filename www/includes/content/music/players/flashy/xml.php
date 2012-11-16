<?php
chdir('../../../../../');
require_once('core.php');

$music_entries = DB_PREFIX . 'music_data';
$component_id  = $_GET['component_id'];

$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }

$entries = $db->force_multi_assoc('SELECT * FROM `'.$music_entries.'` WHERE `component_id`=? ORDER BY `position` ASC', $component_id);
$xml     = '';

if ( (is_array($entries)) and (sizeof($entries) > 0) )
{
	foreach ($entries as $entry)
	{
		$folder    = 'includes/content/music/storage/' . $entry['entry_id'] .'/';
		$song_path = $body->url($folder . $entry['music_file']);
		$song_name = $entry['song_name'];
		$artist    = $entry['artist'];
		
		$check_image = $folder . $entry['image_file'];
		//$image       = ((file_exists($check_image)) and (is_file($check_image))) ? $body->url($check_image) : '';
		$image = 'includes/content/music/'.$entry['entry_id'].'.jpg';
		
		$xml .= <<<XML
	<song location = "$song_path" 
		cover = "$image" 
		title = "$artist - $song_name"
		link  = ""/>

XML;
	}
}

$auto_play = ($options['autoplay'] == 'true') ? 'yes' : 'no';

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<mp3 autoplay="<?=$auto_play?>" artlink="yes" colour1="0x<?=$options['color1']?>" colour2="0x<?=$options['color2']?>" textcolour="0x<?=$options['text_color']?>" playerskin="<?=$options['skin']?>" scrolltype="bar">
<?=$xml?>
</mp3>