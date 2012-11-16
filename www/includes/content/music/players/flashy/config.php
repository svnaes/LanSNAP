<?php

$config = array(
	'thumbnail_width' => 150,
	'thumbnail_height' => 150,
	'swf_width' => 408,
	'swf_height' => 226,
	'xml_fvname' => 'playlist',
	'additional_settings' => array(
		'skin' => array(
			'name'=>'Skin',
			'type'=>'select',
			'values'=>array('black', 'white'),
			'default'=>'black'
		),
		'text_color' => array(
			'name'=>'Text Color (hex)',
			'type'=>'text',
			'default'=>'ffffff'
		),
		'color1' => array(
			'name'=>'Color 1',
			'type'=>'text',
			'default'=>'007CB9'
		),
		'color2' => array(
			'name'=>'Color 2',
			'type'=>'text',
			'default'=>'005680'
		),
	)
);
?>