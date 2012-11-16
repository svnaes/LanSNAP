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
$options['type'] = 'Javascript Gallery'; // what you call your content
$options['text_description'] = ''; // a brief description of what your content does, any restrictions, etc
$options['img_width']    = isset($gallery_options['img_width']) ? $gallery_options['img_width'] : 640;
$options['img_height']   = isset($gallery_options['img_height']) ? $gallery_options['img_height'] : 300;

// thumbnails
$options['num_thumbnails']   = isset($gallery_options['num_thumbnails']) ? $gallery_options['num_thumbnails'] : 5;
$options['thumb_width']   = isset($gallery_options['thumb_width']) ? $gallery_options['thumb_width'] : 80;
$options['thumb_height']   = isset($gallery_options['thumb_height']) ? $gallery_options['thumb_height'] : 50;

$options['categories']   = false;
$options['description']  = true;
$options['title']        = true;
$options['url']          = true;
$options['is_html']      = true;

$settings = array(
	'img_width'=>array(
		'name'=>'Image Width',
		'type'=>'text',
		'default'=>'640',
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
	'num_thumbnails'=>array(
		'name'=>'# Thumbnails',
		'type'=>'text',
		'default'=>5,
	),
	'thumb_width'=>array(
		'name'=>'Thumbnail Width',
		'type'=>'text',
		'default'=>28,
	),
	'thumb_height'=>array(
		'name'=>'Thumbnail Height',
		'type'=>'text',
		'default'=>28,
	),
);
?>