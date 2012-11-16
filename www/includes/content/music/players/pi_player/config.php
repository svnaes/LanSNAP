<?php

$swf_width = (isset($options['playerWidth'])) ? $options['playerWidth'] : 285;

$config = array(
	'thumbnail_width' => 40,
	'thumbnail_height' => 40,
	'swf_width' => $swf_width,
	'swf_height' => 275,
	'xml_fvname' => 'playlist',
	'additional_settings' => array(
		'playerWidth' => array(
			'name'=>'Width',
			'type'=>'text',
			'default'=>'340'
		),
		'defaultVolume' => array(
			'name'=>'Volume',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'60'
		),
		'autoLoopMode' => array(
			'name'=>'Play Next Song',
			'type'=>'select',
			'values'=>array('On', 'Off'),
			'default'=>'On'
		),
		'repeatMode' => array(
			'name'=>'Repeat Mode',
			'type'=>'select',
			'values'=>array('Single', 'All'),
			'default'=>'all'
		),
		'shuffleMode' => array(
			'name'=>'Shuffle Mode',
			'type'=>'select',
			'values'=>array('On', 'Off'),
			'default'=>'Off'
		),
		'randomPlayOnStart' => array(
			'name'=>'Random Play On Start',
			'type'=>'select',
			'values'=>array('On', 'Off'),
			'default'=>'Off'
		),
		'playlistButtonDisplay' => array(
			'name'=>'Display Playlist Button',
			'type'=>'select',
			'values'=>array('On', 'Off'),
			'default'=>'On'
		),
		'playerMode' => array(
			'name'=>'Player Mode',
			'type'=>'select',
			'values'=>array('Pill', 'Compact', 'Full', 'Playlist'),
			'default'=>'Playlist'
		),
		'playerColor' => array(
			'name'=>'Player Color (hex)',
			'type'=>'text',
			'default'=>'111111'
		),
		'playerOpacity' => array(
			'name'=>'Player Opacity',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'100'
		),
		'playerGlossy' => array(
			'name'=>'Player Glossy',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'10'
		),
		'controlsColor' => array(
			'name'=>'Controls Color (hex)',
			'type'=>'text',
			'default'=>'ffffff'
		),
		'controlsColorOver' => array(
			'name'=>'Controls Color Over (hex)',
			'type'=>'text',
			'default'=>'00ffff'
		),
		'controlsOpacity' => array(
			'name'=>'Controls Opacity',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'50'
		),
		'controlsOpacityOver' => array(
			'name'=>'Controls Opacity Over',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'100'
		),
		'glossyOpacity' => array(
			'name'=>'Glossy Opacity',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'10'
		),
		'playListPosition' => array(
			'name'=>'Playlist Position',
			'type'=>'select',
			'values'=>array('Below', 'Above'),
			'default'=>'Below'
		),
		'playListHeight' => array(
			'name'=>'Playlist Height',
			'type'=>'text',
			'default'=>'182'
		),
		'boxesOpacity' => array(
			'name'=>'Opacity of controls background',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'10'
		),
		'boxesOpacityOver' => array(
			'name'=>'Opacity of controls background (mouse over)',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'20'
		),
		'boxesColor' => array(
			'name'=>'Color of controls background and others backgrounds',
			'type'=>'text',
			'default'=>'ffffff'
		),
		'boxesColorOver' => array(
			'name'=>'Color of controls background and others backgrounds (mouse over)',
			'type'=>'text',
			'default'=>'ffffff'
		),
		'ease_speed' => array(
			'name'=>'Playlist menu speed',
			'type'=>'select',
			'values'=>array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'default'=>'10'
		),
	)
);
?>