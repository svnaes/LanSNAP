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
$options['type'] = 'Multiple Gallery'; // what you call your content
$options['text_description'] = 'Gallery with multiple albums and large toggable thumbnails'; // a brief description of what your content does, any restrictions, etc
$options['img_width']    = (isset($gallery_options['img_width'])) ? $gallery_options['img_width'] : 400;
$options['img_height']   = (isset($gallery_options['img_height'])) ? $gallery_options['img_height'] : 300;
$options['swf_width']    = $options['img_width']+150;
$options['swf_height']   = $options['img_height']+90;

$options['thumb_width']  = 135; // make zero for none
$options['thumb_height'] = 85; // make zero for none
$options['categories']   = true;
$options['description']  = true;
$options['title']        = true;
$options['url']          = false;

if (isset($body))
{
	$galleryURL  = urlencode($body->url('includes/content/media/galleries/multiple/settings.php?component_id='.$component_id.'&instance_id='.$instance_id));
	
	//echo urldecode($galleryURL);
	
	$options['flashvars'] = 'galleryXML='.$galleryURL;
}

$settings = array(
	'slideshow'=>array(
		'name'=>'Slide Show',
		'type'=>'select',
		'values'=>array('on', 'off'),
		'default'=>'off',
	),
	'img_width'=>array(
		'name'=>'Image Width',
		'type'=>'text',
		'default'=>'400',
	),
	'img_height'=>array(
		'name'=>'Image Height',
		'type'=>'text',
		'default'=>'300',
	),
	'imageUploadMode'=>array(
		'name'=>'Image Upload Mode',
		'type'=>'select',
		'values'=>array('crop', 'pad'),
		'default'=>'crop',
	),
	'delay'=>array(
		'name'=>'Delay',
		'type'=>'text',
		'default'=>'3',
	),
);
?>