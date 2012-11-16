<?php

$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$gallery_options = unserialize($data);

//print_r($gallery_options);

$config_file     = 'includes/content/media/galleries/'.$gallery_options['gallery_style'].'/config.php';
include($config_file);

$swf_path = $body->url('includes/content/media/galleries/'.$gallery_options['gallery_style'].'/gallery.swf');

$wmode = (isset($gallery['options'])) ? $gallery['options'] : 'transparent';

echo '<input type="hidden" name="gtype" value="'.$gallery_options['gallery_style'].'" />';

$view_file = 'includes/content/media/galleries/'.$gallery_options['gallery_style'].'/view.php';
if (file_exists($view_file))
{
	include($view_file);
}
else
{
?>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="<?=$options['swf_width']?>" height="<?=$options['swf_height']?>" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="menu" value="false" />
	<param name="wmode" value="<?=$wmode?>" />
	<param name="movie" value="<?=$swf_path?>" />
	<param name="quality" value="high" />
	<param name="scale" value="noscale" />
	<param name="salign" value="lt" />
	<param name="flashvars" value="<?=$options['flashvars']?>" />
	<embed src="<?=$swf_path?>" scale="noscale" salign="lt" flashvars="<?=$options['flashvars']?>" menu="false"  allowScriptAccess="sameDomain" allowFullScreen="false" quality="high" wmode="<?=$wmode?>" width="<?=$options['swf_width']?>" height="<?=$options['swf_height']?>" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
<?php
}
?>