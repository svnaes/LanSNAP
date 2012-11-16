<?php
chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
$component_id = $_GET['component_id'];
$instance_id  = $_GET['instance_id'];

$flash_settings = gallery_get_settings($component_id); // loads the settings as well as defaults if needed

$media_files = DB_PREFIX . 'pico_media_files';
$num_images  = $db->result('SELECT count(1) FROM `'.$media_files.'` WHERE `instance_id`=?', $instance_id);
$auto_play   = ($num_images > 1) ? $flash_settings['auto_play'] : 'false';

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<banner
	 width="<?=$flash_settings['swf_width']?>" height="<?=$flash_settings['swf_height']?>" gap="3" 	
	 transitionType="<?=$flash_settings['transitionType']?>" delay="<?=$flash_settings['delay']?>" autoStart="<?=$auto_play?>"
	 bgColor="0x<?=$flash_settings['bgColor']?>"	displayBorder="<?=$flash_settings['displayBorder']?>" borderColor="0x<?=$flash_settings['borderColor']?>"
	 displayClock="<?=$flash_settings['displayClock']?>" displayPlayPauseButton="<?=$flash_settings['displayPlayPauseButton']?>"
	 displayDirectionalButtons="<?=$flash_settings['displayDirectionalButtons']?>" mouseoverDirectionalButtons="<?=$flash_settings['mouseoverDirectionalButtons']?>"
	 displayThumbButtons="<?=$flash_settings['displayThumbButtons']?>" thumbnailsAlign="<?=$flash_settings['thumbnailsAlign']?>">
  <preloader 
		color="0x0066FF" size="28"
		textColor="0xFFFFFF" textSize="10"/>
  <thumbnail 
		size="<?=$flash_settings['thumb_size']?>" shape="<?=$flash_settings['thumbnailsShape']?>" 
		displayImage="<?=$flash_settings['thumbnailIsImage']?>" textSize="<?=$flash_settings['thumbnailTextSize']?>"
		color="0x<?=$flash_settings['thumbnailTextColor']?>" mouseoverColor="0x<?=$flash_settings['thumbnailMouseoverColor']?>"
		bgColor="0x<?=$flash_settings['thumbnailBGColor']?>" mouseoverBgColor="0x<?=$flash_settings['thumbnailMouseoverBgColor']?>"
		displayBorder="<?=$flash_settings['thumbnailDisplayBorder']?>" borderColor="0x<?=$flash_settings['thumbnailBorderColor']?>" mouseoverBorderColor="0x<?=$flash_settings['thumbnailMouseoverBorderColor']?>"/>
  <button
		size="<?=$flash_settings['thumb_size']?>" shape="<?=$flash_settings['thumbnailsShape']?>"
		color="0x<?=$flash_settings['buttonColor']?>" mouseoverColor="0x<?=$flash_settings['buttonMouseoverColor']?>"
		bgColor="0x<?=$flash_settings['buttonBGColor']?>" semiTransparent="<?=$flash_settings['buttonSemiTransparent']?>"/>
  <textpanel
		mouseover="0x<?=$flash_settings['tpmouseover']?>" textSize="<?=$flash_settings['tptextSize']?>" textColor="0x<?=$flash_settings['tptextColor']?>"
		bgColor="0x<?=$flash_settings['tpbgColor']?>" semiTransparent="<?=$flash_settings['tpsemiTransparent']?>" display="<?=$flash_settings['tpdisplay']?>"/>
  <tooltip
		textSize="<?=$flash_settings['tttextSize']?>" textColor="0x<?=$flash_settings['tttextColor']?>" bgColor="0x<?=$flash_settings['ttbgColor']?>" 
		roundCorners="<?=$flash_settings['ttroundCorners']?>" semiTransparent="<?=$flash_settings['ttsemiTransparent']?>" display="<?=$flash_settings['ttdisplay']?>"/>
</banner>