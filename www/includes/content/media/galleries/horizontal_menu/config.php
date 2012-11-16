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
$options['type'] = 'Horizontal Menu'; // what you call your content
$options['text_description']  = 'Thumbnails and images, can auto-rotate, thumbnails on right'; // a brief description of what your content does, any restrictions, etc
$options['img_width']    = isset($gallery_options['img_width']) ? $gallery_options['img_width'] : 640;
$options['img_height']   = isset($gallery_options['img_height']) ? $gallery_options['img_height'] : 480;
$options['swf_width']    = $gallery_options['img_width']+120;
$options['swf_height']   = $gallery_options['img_height']+52;

$options['thumb_width']  = 120; // make zero for none
$options['thumb_height'] = 90; // make zero for none
$options['categories']   = true;
$options['description']  = true;
$options['title']        = true;
$options['url']          = false;

if (isset($body))
{
	$galleryURL  = urlencode($body->url('includes/content/media/galleries/horizontal_menu/xml.php?component_id='.$component_id.'&instance_id='.$instance_id));
	
	//echo urldecode($galleryURL);
	
	$options['flashvars'] = 'xmlFile='.$galleryURL;
}

$settings = array(
	'img_width'=>array(
		'name'=>'Image Width',
		'type'=>'text',
		'default'=>'640',
	),
	'img_height'=>array(
		'name'=>'Image Height',
		'type'=>'text',
		'default'=>'480',
	),
	'imageUploadMode'=>array(
		'name'=>'Image Upload Mode',
		'type'=>'select',
		'values'=>array('crop', 'pad'),
		'default'=>'crop',
	),
);
?>
