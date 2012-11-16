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
	<song>
		<title>$song_name</title>
		<artist>$artist</artist>
		<filename>$song_path</filename>
		<songimage>$image</songimage>
		<buyLink></buyLink>
	</song>

XML;
	}
}

$auto_play = ($options['autoplay'] == 'true') ? 'On' : 'Off';

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<mp3player>
	<options>
		<defaultVolume><?=$options['defaultVolume']?></defaultVolume>
		<playerWidth><?=$options['playerWidth']?></playerWidth>
		<thumbnailsDisplay>On</thumbnailsDisplay>
		<startSong>1</startSong>
		<autoPlayOnStart><?=$auto_play?></autoPlayOnStart>
		<autoLoopMode><?=$options['autoLoopMode']?></autoLoopMode>
		<repeatMode><?=$options['repeatMode']?></repeatMode>
		<shuffleMode><?=$options['shuffleMode']?></shuffleMode>
		<randomPlayOnStart><?=$options['randomPlayOnStart']?></randomPlayOnStart>
		<buyLinkDisplay>Off</buyLinkDisplay>
		<playlistButtonDisplay><?=$options['playlistButtonDisplay']?></playlistButtonDisplay>
		<playerMode><?=$options['playerMode']?></playerMode>
		<bgColor>0x<?=$options['bgColor']?></bgColor>
		<playerColor>0x<?=$options['playerColor']?></playerColor>
		<playerOpacity><?=$options['playerOpacity']?></playerOpacity>
		<playerGlossy><?=$options['playerGlossy']?></playerGlossy>
		<controlsColor>0x<?=$options['controlsColor']?></controlsColor>
		<controlsOpacity><?=$options['controlsOpacity']?></controlsOpacity>
		<controlsColorOver>0x<?=$options['controlsColorOver']?></controlsColorOver>
		<controlsOpacityOver><?=$options['controlsOpacityOver']?></controlsOpacityOver>
		<glossyOpacity><?=$options['glossyOpacity']?></glossyOpacity>
		<playListPosition><?=$options['playListPosition']?></playListPosition>
		<playListHeight><?=$options['playListHeight']?></playListHeight>
		<boxesOpacity><?=$options['boxesOpacity']?></boxesOpacity>
		<boxesOpacityOver><?=$options['boxesOpacityOver']?></boxesOpacityOver>
		<boxesColor>0x<?=$options['boxesColor']?></boxesColor>
		<boxesColorOver>0x<?=$options['boxesColorOver']?></boxesColorOver>
		<buyAlpha>10</buyAlpha>
		<buyAlphaOver>50</buyAlphaOver>
		<buyColor>0xffffff</buyColor>
		<buyColorOver>0xff0000</buyColorOver>
		<ease_speed><?=$options['ease_speed']?></ease_speed>
	</options>
<?=$xml?>
</mp3player>