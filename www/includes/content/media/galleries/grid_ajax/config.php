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
$options['type'] = 'Grid Gallery'; // what you call your content
$options['description'] = 'Configurable grid thumbnails with side preview'; // a brief description of what your content does, any restrictions, etc
$options['img_width']    = isset($gallery_options['img_width']) ? $gallery_options['img_width'] : 400;
$options['img_height']   = isset($gallery_options['img_height']) ? $gallery_options['img_height'] : 300;
$options['thumb_width']  = isset($gallery_options['thumbnail_size']) ? $gallery_options['thumbnail_size'] : 70; // make zero for none
$options['thumb_height'] = $options['thumb_width']; // make zero for none


$options['categories']   = false;
$options['description']  = true;
$options['title']        = false;
$options['url']          = true;

$settings = array(
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
	'num_per_row'=>array(
		'name'=>'Number of Thumbnails Per Row',
		'type'=>'text',
		'default'=>'6',
	),
	'thumbnail_size'=>array(
		'name'=>'Size of Thumbnails',
		'type'=>'text',
		'default'=>'70',
	),
);
?>