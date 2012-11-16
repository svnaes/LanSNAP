<?php

$swf_width = (isset($options['width'])) ? $options['width'] : 285;
$swf_height = (isset($options['height'])) ? $options['height'] : 109;

$config = array(
	'thumbnail_width' => 96,
	'thumbnail_height' => 75,
	'swf_width' => $swf_width,
	'swf_height' => $swf_height,
	'xml_fvname' => 'configpath',
	'additional_settings' => array(
		'width' => array(
			'name'=>'Width',
			'type'=>'text',
			'default'=>'285'
		),
		'height' => array(
			'name'=>'Height',
			'type'=>'text',
			'default'=>'109'
		),
		'volume' => array(
			'name'=>'Volume',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'60'
		),
		'autoload' => array(
			'name'=>'Auto Load',
			'type'=>'select',
			'values'=>array('true', 'false'),
			'default'=>'true'
		),
		'continuous_play' => array(
			'name'=>'Continuous Play',
			'type'=>'select',
			'values'=>array('true', 'false'),
			'default'=>'true'
		),
		'jump' => array(
			'name'=>'Jump to Next Song',
			'type'=>'select',
			'values'=>array('true', 'false'),
			'default'=>'true'
		),
		'jump' => array(
			'name'=>'Repeat',
			'type'=>'select',
			'values'=>array('true', 'false'),
			'default'=>'true'
		)
		
	)
);
?>