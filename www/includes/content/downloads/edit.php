<?php
$download_files = DB_PREFIX . 'download_files';
$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$download_files` (
	`file_id` BIGINT(11) NOT NULL auto_increment,
	`file_name` TEXT NOT NULL,
	`description` VARCHAR(255),
	`position` BIGINT(11) NOT NULL,
	`instance_id` VARCHAR(32),
	`num_downloads` BIGINT(11) NOT NULL DEFAULT '0',
	`html_description` LONGTEXT,
	PRIMARY KEY(`file_id`));
SQL
);
$swf_path = $body->url('includes/content/downloads/FileUploader.swf');
$upload_path = $body->url('includes/content/downloads/upload.php');
$fv = array();

$fv['uploadType'] = 'all'; // all, text, video, image
$fv['fileCallback'] = 'DL_FileUploaded';
$fv['filesCallback'] = 'DL_FilesUploaded';
$fv['uploadPath'] = $upload_path;
$p = array();
foreach($fv as $key=>$val)
{
	$val = urlencode($val);
	$p[] = "$key=$val";
}
$fvText = implode('&', $p);
?>
<input type="hidden" id="instance_id" value="<?=$instance_id?>" />
<input type="hidden" id="component_id" value="<?=$component_id?>" />
<div id="file_list">
<?php include('includes/content/downloads/file_list.php'); ?>
</div>
<div id="file_upload">
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="500" height="100" id="live_preview" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="<?=$swf_path?>" />
	<param name="loop" value="false" />
	<param name="menu" value="false" />
	<param name="quality" value="high" />
	<param name="wmode" value="transparent" />
	<param name="flashvars" value="<?=$fvText?>" />
	<embed src="<?=$swf_path?>" flashvars="uploadPath=<?=$fvText?>" loop="false" menu="false" quality="high" wmode="transparent" width="500" height="100" name="live_preview" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
</div>
<br />
<button onclick="DL_Close()">Close</button>