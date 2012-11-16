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
$options['type'] = 'Simple Gallery'; // what you call your content
$options['text_description']  = 'Thumbnails and images, can auto-rotate'; // a brief description of what your content does, any restrictions, etc
$options['img_width']    = isset($gallery_options['img_width']) ? $gallery_options['img_width'] : 640;
$options['img_height']   = isset($gallery_options['img_height']) ? $gallery_options['img_height'] : 480;
$options['swf_width']    = isset($gallery_options['swf_width']) ? $gallery_options['swf_width'] : 720;
$options['swf_height']   = isset($gallery_options['swf_height']) ? $gallery_options['swf_height'] : 620;

$options['thumb_width']  = 40; // make zero for none
$options['thumb_height'] = 40; // make zero for none
$options['categories']   = false;
$options['description']  = true;
$options['title']        = true;
$options['url']          = true;

if (isset($body))
{
	$galleryURL  = urlencode($body->url('includes/content/media/galleries/simple/xml.php?component_id='.$component_id.'&instance_id='.$instance_id));
	
	//echo urldecode($galleryURL);
	
	$options['flashvars'] = 'xmlURL='.$galleryURL;
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
	'swf_width'=>array(
		'name'=>'SWF Width',
		'type'=>'text',
		'default'=>'720',
	),
	'swf_height'=>array(
		'name'=>'SWF Height',
		'type'=>'text',
		'default'=>'620',
	),
	'imageUploadMode'=>array(
		'name'=>'Image Upload Mode',
		'type'=>'select',
		'values'=>array('crop', 'pad'),
		'default'=>'crop',
	),
	'displayThumbs'=>array(
		'name'=>'# Of thumbnails to display',
		'type'=>'text',
		'default'=>'8',
	),
	'thumbsScrollSpeed'=>array(
		'name'=>'Thumbs Scroll Speed',
		'type'=>'text',
		'default'=>'5',
	),
	'delay'=>array(
		'name'=>'Delay',
		'type'=>'text',
		'default'=>'2',
	),
	'linkColor'=>array(
		'name'=>'Link Color',
		'type'=>'text',
		'default'=>'FF6600',
	),
	'hoverColor'=>array(
		'name'=>'Hover Color',
		'type'=>'text',
		'default'=>'FE9966',
	),
);
?>
