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
		
		$xml .= "\t\t<song>\n";
		$xml .= "\t\t\t<url>$song_path</url>\n";
		$xml .= "\t\t\t<songname>$song_name</songname>\n";
		$xml .= "\t\t\t<artist>$artist</artist>\n";
		$xml .= "\t\t\t<image>$image</image>\n";
		$xml .= "\t\t</song>\n";
	}
}
?>
<mp3player>
	<settings>		
		<!-- Component setup -->
		<width><?=is_numeric($options['width']) ? $options['width'] : 285 ?></width>
		<height><?=is_numeric($options['height']) ? $options['height'] : 109 ?></height>
		<albumArtWidth>96</albumArtWidth>
		<albumArtHeight>75</albumArtHeight>
		<albumArtXPos>7</albumArtXPos>
		<albumArtYPos>7</albumArtYPos>
		
		<!-- Player settings -->
		<autoLoad><?=isset($options['autoload']) ? $options['autoload'] : 'true'?></autoLoad>
		<autoPlay><?=isset($options['autoplay']) ? $options['autoplay'] : 'true'?></autoPlay>
		<continuousPlay><?=isset($options['continuous_play']) ? $options['continuous_play'] : 'true'?></continuousPlay>
		<onCompleteJumpToNext><?=isset($options['jump']) ? $options['jump'] : 'true'?></onCompleteJumpToNext>
		<repeat><?=isset($options['repeat']) ? $options['repeat'] : 'true'?></repeat>
		<initialVolume><?=isset($options['volume']) ? ($options['volume']/100) : .75?></initialVolume>
		<bufferTime>1</bufferTime>
		
		<!-- Text slide setup -->
		<textSlideTime>5</textSlideTime>
		<textPauseTime>2</textPauseTime>
	</settings>
	<songs>
<?=$xml?>
	</songs>
</mp3player>