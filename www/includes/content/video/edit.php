<?php
//echo $instance_id . '<br />'. $component_id;
$swf_path  = $body->url('includes/content/video/FileUploaderVideo.swf');
$swf_path2 = $body->url('includes/content/video/FileUploaderImage.swf');
?>
<input type="hidden" id="instance_id" value="<?=$instance_id?>" />
<input type="hidden" id="component_id" value="<?=$component_id?>" />

<h3>Upload a Video</h3>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="500" height="100" id="live_preview" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="<?=$swf_path?>" />
	<param name="loop" value="false" />
	<param name="menu" value="false" />
	<param name="quality" value="high" />
	<param name="wmode" value="transparent" />
	<embed src="<?=$swf_path?>" loop="false" menu="false" quality="high" wmode="transparent" width="500" height="100" name="live_preview" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
<h3>Upload an Image</h3>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="500" height="100" id="live_preview" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="<?=$swf_path2?>" />
	<param name="loop" value="false" />
	<param name="menu" value="false" />
	<param name="quality" value="high" />
	<param name="wmode" value="transparent" />
	<embed src="<?=$swf_path2?>" loop="false" menu="false" quality="high" wmode="transparent" width="500" height="100" name="live_preview" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>