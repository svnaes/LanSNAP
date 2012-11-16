<?php

$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$player_options = unserialize($data);
if (!is_array($player_options)) { echo 'You must configure the video player before using it'; return; }

$storage = 'includes/content/video/storage/'.$instance_id.'/';

$video_file = $storage . $component_id . '.flv';
if (!file_exists($video_file))
{
	$video_file = $storage . $component_id . '.mov';
}
if (!file_exists($video_file))
{
	return;
}

$image_file = $storage . $component_id . '.jpg';

$swf_path = $body->url('includes/content/video/player.swf');
$flashvars = array();

if (sizeof($player_options) > 0)
{
	foreach ($player_options as $key=>$val)
	{
		$flashvars[] = $key . '=' . urlencode($val);
		
	}
}

$flashvars[] = 'videoPath='.urlencode($body->url($video_file));
if (file_exists($image_file))
{
	$flashvars[] = 'imagePath='.urlencode($body->url($image_file));
}

$fv = implode('&', $flashvars);

?>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="<?=$player_options['newWidth']?>" height="<?=$player_options['newHeight']+25?>" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="menu" value="false" />
	<param name="wmode" value="transparent" />
	<param name="movie" value="<?=$swf_path?>" />
	<param name="quality" value="high" />
	<param name="flashvars" value="<?=$fv?>" />
	<embed src="<?=$swf_path?>" flashvars="<?=$fv?>" menu="false" allowScriptAccess="sameDomain" allowFullScreen="false" quality="high" wmode="transparent" width="<?=$player_options['newWidth']?>" height="<?=$player_options['newHeight']+25?>" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>

