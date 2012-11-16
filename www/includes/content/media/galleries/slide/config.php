<?php
$options = array();
$options['type'] = 'Slide Gallery'; // what you call your content
$options['text_description'] = 'Image gallery that has large thumbnails along on the bottom'; // a brief description of what your content does, any restrictions, etc
$options['swf_width'] = 687;
$options['swf_height'] = 460;
$options['img_width'] = 687;
$options['img_height'] = 460;
$options['thumb_width'] = 86; // make zero for none
$options['thumb_height'] = 58; // make zero for none
$options['categories'] = false;
$options['description'] = true;
$options['title'] = false;
$options['url'] = true;

$settings = array(
	'imageUploadMode'=>array(
		'name'=>'Image Upload Mode',
		'type'=>'select',
		'values'=>array('crop', 'pad'),
		'default'=>'crop',
	),
	'thumbnailBurningEffect'=>array(
		'name'=>'Thumbnail Burning Effect',
		'type'=>'select',
		'values'=>array('off', 'on'),
		'default'=>'off',
	),
	'largeImageBurningEffect'=>array(
		'name'=>'Large Image Burning Effect',
		'type'=>'select',
		'values'=>array('off', 'on'),
		'default'=>'off',
	),
	'arrowColor'=>array(
		'name'=>'Arrow Color',
		'type'=>'text',
		'default'=>'999999',
	),
	'arrowRolloverColor'=>array(
		'name'=>'Arrow Rollover Color',
		'type'=>'text',
		'default'=>'666666',
	),
	'largeImageRoundCorner'=>array(
		'name'=>'Large Image Round Corner',
		'type'=>'select',
		'values'=>array('off', 'on'),
		'default'=>'on',
	),
	'thumbnailRoundCorner'=>array(
		'name'=>'Thumbnail Round Corner',
		'type'=>'select',
		'values'=>array('off', 'on'),
		'default'=>'on',
	),
	'slideShow'=>array(
		'name'=>'Slide Show',
		'type'=>'select',
		'values'=>array('off', 'on'),
		'default'=>'off',
	),
	'slideShowTimeInterval'=>array(
		'name'=>'Slide Show Time Interval',
		'type'=>'text',
		'default'=>'4',
	),
	'currentThumbnailColor'=>array(
		'name'=>'Current Thumbnail Color',
		'type'=>'text',
		'default'=>'a3a3a3',
	),
	'leftMarginForDescription'=>array(
		'name'=>'Left Margin For Description',
		'type'=>'text',
		'default'=>'20',
	),
	'urlOpenInNewWindow'=>array(
		'name'=>'URL Open In New Window',
		'type'=>'select',
		'values'=>array('off', 'on'),
		'default'=>'on',
	),
);

if (isset($body))
{
	$settingsURL = urlencode($body->url('includes/content/media/galleries/slide/settings.php?component_id='.$component_id));
	$galleryURL  = urlencode($body->url('includes/content/media/galleries/slide/gallery.php?component_id='.$component_id.'&instance_id='.$instance_id));
	$options['flashvars'] = 'settingsXML='.$settingsURL.'&galleryXML='.$galleryURL;
}
?>