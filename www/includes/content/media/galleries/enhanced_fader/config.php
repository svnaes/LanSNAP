<?php

if (isset($component_id))
{
	$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$gallery_options = unserialize($data);
}
else
{
	$gallery_options = array();
}

$options = array();
$options['type'] = 'Fade Gallery'; // what you call your content
$options['text_description'] = 'Image gallery that has image transition effects.'; // a brief description of what your content does, any restrictions, etc
$options['swf_width']    = (isset($gallery_options['swf_width'])) ? $gallery_options['swf_width'] : 400;
$options['swf_height']   = (isset($gallery_options['swf_height'])) ? $gallery_options['swf_height'] : 300;
$options['img_width']    = $options['swf_width'];
$options['img_height']   = $options['swf_height'];
$options['thumb_width']  = (isset($gallery_options['thumb_size'])) ? $gallery_options['thumb_size'] : 28; // make zero for none
$options['thumb_height'] = $options['thumb_width']; // make zero for none
$options['categories']   = false;
$options['description']  = true;
$options['title']        = true;
$options['url']          = true;

if (isset($body))
{
	$settingsURL = urlencode($body->url('includes/content/media/galleries/enhanced_fader/settings.php?component_id='.$component_id.'&instance_id='.$instance_id));
	$galleryURL  = urlencode($body->url('includes/content/media/galleries/enhanced_fader/gallery.php?component_id='.$component_id.'&instance_id='.$instance_id));
	$options['flashvars'] = 'configpath='.$settingsURL.'&datapath='.$galleryURL;
}

$settings = array(
	'auto_play'=>array(
		'name'=>'Auto Play',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'swf_width'=>array(
		'name'=>'Width',
		'type'=>'text',
		'default'=>'400',
	),
	'swf_height'=>array(
		'name'=>'Height',
		'type'=>'text',
		'default'=>'300',
	),
	'imageUploadMode'=>array(
		'name'=>'Image Upload Mode',
		'type'=>'select',
		'values'=>array('crop', 'pad'),
		'default'=>'crop',
	),
	'transitionType'=>array(
		'name'=>'Transition Type',
		'type'=>'select',
		'values'=>array('fade', 'slide'),
		'default'=>'fade',
	),
	'delay'=>array(
		'name'=>'Delay',
		'type'=>'text',
		'default'=>'5',
	),
	'bgColor'=>array(
		'name'=>'BG Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'displayBorder'=>array(
		'name'=>'Display Border',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'borderColor'=>array(
		'name'=>'Border Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'displayClock'=>array(
		'name'=>'Display Clock',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'displayPlayPauseButton'=>array(
		'name'=>'Display Play Pause Button',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'displayDirectionalButtons'=>array(
		'name'=>'Display Directional Buttons',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'mouseoverDirectionalButtons'=>array(
		'name'=>'Mouse-Over Directional Buttons',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'text1'=>array(
		'type'=>'info',
		'text'=>'Thumbnail Options'
	),
	'displayThumbButtons'=>array(
		'name'=>'Display Thumbnails',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'thumb_size'=>array(
		'name'=>'Thumbnail Size',
		'type'=>'text',
		'default'=>28,
	),
	'thumbnailsAlign'=>array(
		'name'=>'Alignment',
		'type'=>'select',
		'values'=>array('TL', 'TR', 'BL', 'BR'),
		'default'=>'BR',
	),
	'thumbnailsShape'=>array(
		'name'=>'Shape',
		'type'=>'select',
		'values'=>array('circle', 'square', 'round_square'),
		'default'=>'round_square',
	),
	'thumbnailIsImage'=>array(
		'name'=>'Thumbnail Is Image',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'thumbnailTextSize'=>array(
		'name'=>'Text Size',
		'type'=>'text',
		'default'=>'12',
	),
	'thumbnailTextColor'=>array(
		'name'=>'Text Color',
		'type'=>'text',
		'default'=>'ffffff',
	),
	'thumbnailMouseoverColor'=>array(
		'name'=>'Mouse-Over Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'thumbnailBGColor'=>array(
		'name'=>'BG Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'thumbnailMouseoverBgColor'=>array(
		'name'=>'BG Color',
		'type'=>'text',
		'default'=>'0066FF',
	),
	'thumbnailDisplayBorder'=>array(
		'name'=>'Display Border',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'thumbnailBorderColor'=>array(
		'name'=>'Border Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'thumbnailMouseoverBorderColor'=>array(
		'name'=>'Mouse-Over Border Color',
		'type'=>'text',
		'default'=>'0066FF',
	),
	'text2'=>array(
		'type'=>'info',
		'text'=>'Button Options'
	),
	'buttonColor'=>array(
		'name'=>'Color',
		'type'=>'text',
		'default'=>'ffffff',
	),
	'buttonMouseoverColor'=>array(
		'name'=>'Mouse-Over Color',
		'type'=>'text',
		'default'=>'0066FF',
	),
	'buttonBGColor'=>array(
		'name'=>'BG Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'buttonSemiTransparent'=>array(
		'name'=>'Semi Transparent',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'text3'=>array(
		'type'=>'info',
		'text'=>'Text Panel'
	),
	'tpdisplay'=>array(
		'name'=>'Display',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'tpmouseover'=>array(
		'name'=>'Mouse-Over',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'tptextSize'=>array(
		'name'=>'Text Size',
		'type'=>'text',
		'default'=>'12',
	),
	'tptextColor'=>array(
		'name'=>'Text Color',
		'type'=>'text',
		'default'=>'ffffff',
	),
	'tpBGColor'=>array(
		'name'=>'BG Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'tpsemiTransparent'=>array(
		'name'=>'Semi Transparent',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'text4'=>array(
		'type'=>'info',
		'text'=>'Tooltip'
	),
	'ttdisplay'=>array(
		'name'=>'Display',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'tttextSize'=>array(
		'name'=>'Text Size',
		'type'=>'text',
		'default'=>'12',
	),
	'tttextColor'=>array(
		'name'=>'Text Color',
		'type'=>'text',
		'default'=>'ffffff',
	),
	'ttBGColor'=>array(
		'name'=>'BG Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'ttsemiTransparent'=>array(
		'name'=>'Semi Transparent',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'ttroundCorners'=>array(
		'name'=>'Round Corners',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
);
?>