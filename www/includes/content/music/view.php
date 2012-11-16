<?php

$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }

if ( (isset($options['player'])) and (strlen($options['player']) > 0) )
{
	$swf_path = $body->url('includes/content/music/players/'.$options['player'].'/player.swf');
	include('includes/content/music/players/'.$options['player'].'/config.php');
	
	$options['swf_width']  = $config['swf_width'];
	$options['swf_height'] = $config['swf_height'];
	
	$xml_url = $body->url('includes/content/music/players/'.$options['player'].'/xml.php?component_id='.$component_id.'&instance_id='.$instance_id);
	$options['flashvars'] = $config['xml_fvname'] . '=' . urlencode($xml_url);
	
	//echo $xml_url;
?>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="<?=$options['swf_width']?>" height="<?=$options['swf_height']?>" align="middle">
	<param name="allowScriptAccess" value="always" />
	<param name="allowFullScreen" value="false" />
	<param name="menu" value="false" />
	<param name="wmode" value="transparent" />
	<param name="movie" value="<?=$swf_path?>" />
	<param name="quality" value="high" />
	<param name="scale" value="noscale" />
	<param name="salign" value="lt" />
	<param name="flashvars" value="<?=$options['flashvars']?>" />
	<embed src="<?=$swf_path?>" scale="noscale" salign="lt" flashvars="<?=$options['flashvars']?>" menu="false" allowScriptAccess="always" allowFullScreen="false" quality="high" wmode="transparent" width="<?=$options['swf_width']?>" height="<?=$options['swf_height']?>" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
<?php
}
else
{
	echo 'Please configure the MP3 player before continuing';
}

/*
if ( ($options['player'] == 'original') or (!isset($options['player'])) )
{
	$swf_path = $body->url('includes/content/music/player.swf');
	$options['swf_width'] = is_numeric($options['width']) ? $options['width'] : 285;
	$options['swf_height'] = is_numeric($options['height']) ? $options['height'] : 109;
	
	$xml_url = $body->url('includes/content/music/xml.php?component_id='.$component_id);
	$options['flashvars'] = 'configpath=' . urlencode($xml_url);
}
elseif ($options['player'] == 'pi-player')
{
	$swf_path = $body->url('includes/content/music/pi.swf');
	$options['swf_width'] = 255;
	$options['swf_height'] = 275;
	
	$xml_url = $body->url('includes/content/music/xml-pi.php?component_id='.$component_id);
	$options['flashvars'] = 'playlist=' . urlencode($xml_url);
	//$options['flashvars'] = 'playlist=http://cib.sisarina.net/includes/content/music/test.xml';
}
else
{
	$swf_path = $body->url('includes/content/music/player-flashy.swf');
	$options['swf_width'] = 408;
	$options['swf_height'] = 226;
	
	$xml_url = $body->url('includes/content/music/xml-flashy.php?component_id='.$component_id);
	$options['flashvars'] = 'playlist=' . urlencode($xml_url);
}*/




?>
