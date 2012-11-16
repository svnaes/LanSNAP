<?php
chdir('../../../');
require_once('core.php');

$download_files = DB_PREFIX . 'download_files';

$file_id = $_GET['id'];;

if (!is_numeric($file_id)) { exit(); }

$filename = $db->result('SELECT `file_name` FROM `'.$download_files.'` WHERE `file_id`=?', $file_id);
if ($filename === FALSE) { exit(); }

$full_file = 'includes/content/downloads/storage/'.$file_id.'/'.$filename;
if (!file_exists($full_file)) { exit(); }

$db->run('UPDATE `'.$download_files.'` SET `num_downloads`=(`num_downloads`+1) WHERE `file_id`=?', $file_id);

$file_extension = strtolower(array_pop(explode('.', $filename)));

switch( $file_extension )
{
	case "pdf": $ctype="application/pdf"; break;
	case "exe": $ctype="application/octet-stream"; break;
	case "zip": $ctype="application/zip"; break;
	case "doc": $ctype="application/msword"; break;
	case "xls": $ctype="application/vnd.ms-excel"; break;
	case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
	case "gif": $ctype="image/gif"; break;
	case "png": $ctype="image/png"; break;
	case "jpeg":
	case "jpg": $ctype="image/jpg"; break;
	default: $ctype="application/force-download";
}

header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); // required for certain browsers 
header("Content-Type: $ctype");
header("Content-Disposition: attachment; filename=\"".basename($full_file)."\";" );
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($full_file));
readfile($full_file);
exit();

?>