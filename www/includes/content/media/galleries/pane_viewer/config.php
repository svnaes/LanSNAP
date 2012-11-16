<?php



$options = array();
$options['type'] = 'Pane Viewer'; // what you call your content
$options['description']  = 'Simple image viewer with 2 columns of images on the right side'; // a brief description of what your content does, any restrictions, etc
$options['img_width']    = 310;
$options['img_height']   = 211;
$options['swf_width']    = 478;
$options['swf_height']   = 240;

$options['thumb_width']  = 75; // make zero for none
$options['thumb_height'] = 68; // make zero for none
$options['categories']   = false;
$options['description']  = true;
$options['title']        = false;
$options['url']          = true;

if (isset($body))
{
	$galleryURL  = urlencode($body->url('includes/content/media/galleries/pane_viewer/xml.php?component_id='.$component_id.'&instance_id='.$instance_id));
	
	//echo urldecode($galleryURL);
	
	$options['flashvars'] = 'xmlURL='.$galleryURL;
}

$settings = array(
	'autoSlide'=>array(
		'name'=>'Auto Slide',
		'type'=>'select',
		'values'=>array(0,1,2,3,4,5,6,7,8,9,10),
		'default'=>5,
	),
);
?>
