<?php
$options = array();
$options['type']         = 'Marquee'; // what you call your content
$options['description']  = 'Side scrolling image Marquee'; // a brief description of what your content does, any restrictions, etc
$options['swf_width']    = 926;
$options['swf_height']   = 150;
$options['img_width']    = 0;
$options['img_height']   = 150;
$options['thumb_width']  = 0; // make zero for none
$options['thumb_height'] = 0; // make zero for none
$options['categories']   = false;
$options['description']  = false;
$options['title']        = false;
$options['url']          = false;

$galleryURL  = urlencode($body->url('includes/content/media/galleries/marquee/gallery.php?component_id='.$component_id.'&instance_id='.$instance_id));
$options['flashvars'] = '&galleryXML='.$galleryURL;

?>