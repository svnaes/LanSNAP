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
$options['type'] = 'Plain Images'; // what you call your content
$options['text_description'] = 'Simple image rotator. Displays a single flat image'; // a brief description of what your content does, any restrictions, etc
$options['img_width']    = isset($gallery_options['img_width']) ? $gallery_options['img_width'] : 640;
$options['img_height']   = isset($gallery_options['img_height']) ? $gallery_options['img_height'] : 480;

$options['categories']   = false;
$options['description']  = false;
$options['title']        = false;
$options['url']          = true;

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