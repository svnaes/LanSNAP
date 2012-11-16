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
$options['type'] = 'Portfolio'; // what you call your content
$options['text_description'] = 'Simple category-based portfolio style gallery'; // a brief description of what your content does, any restrictions, etc
$options['img_width']    = 640;
$options['img_height']   = 480;
$options['swf_width']    = 800;
$options['swf_height']   = 600;

$options['thumb_width']  = 60; // make zero for none
$options['thumb_height'] = 45; // make zero for none
$options['categories']   = true;
$options['description']  = true;
$options['title']        = true;
$options['url']          = false;

if (isset($body))
{
	$galleryURL  = urlencode($body->url('includes/content/media/galleries/portfolio/xml.php?component_id='.$component_id.'&instance_id='.$instance_id));
	
	//echo urldecode($galleryURL);
	
	$options['flashvars'] = 'xmlURL='.$galleryURL;
}

$settings = array(
	'imageUploadMode'=>array(
		'name'=>'Image Upload Mode',
		'type'=>'select',
		'values'=>array('crop', 'pad'),
		'default'=>'crop',
	),
	'controlColor'=>array(
		'name'=>'Control Color',
		'type'=>'text',
		'default'=>'DDDDDD',
	),
	'controlFontSize'=>array(
		'name'=>'Control Font Size',
		'type'=>'text',
		'default'=>'10',
	),
	'controlTextColor'=>array(
		'name'=>'Control Text Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'controlTextColorHover'=>array(
		'name'=>'Control Text Color (Hover)',
		'type'=>'text',
		'default'=>'000000',
	),
	'bgColor'=>array(
		'name'=>'BG Color',
		'type'=>'text',
		'default'=>'000000',
	),
	'borderColor'=>array(
		'name'=>'Border Color',
		'type'=>'text',
		'default'=>'DCE4EF',
	),
	'thumbnailTitleColor'=>array(
		'name'=>'Thumbnail Title Color',
		'type'=>'text',
		'default'=>'EEF2D9',
	),
	'thumbnailFontSize'=>array(
		'name'=>'Thumbnail Font Size',
		'type'=>'text',
		'default'=>'12',
	),
	'descriptionColor'=>array(
		'name'=>'Description Color',
		'type'=>'text',
		'default'=>'FF304A',
	),
	'descriptionBgColor'=>array(
		'name'=>'Description Bg Color',
		'type'=>'text',
		'default'=>'DDDDDD',
	),
	'preloaderColor'=>array(
		'name'=>'Preloader Color',
		'type'=>'text',
		'default'=>'DCE4EF',
	),
);
?>