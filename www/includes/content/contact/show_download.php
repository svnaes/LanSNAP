<?php
$file_id = $_GET['file_id'];

$storage = 'storage/' . $file_id . '/';
if (is_dir($storage))
{
	if ($dh = opendir($storage))
	{
		while (($file = readdir($dh)) !== false)
		{
			$full_file = $storage . $file;
			if (filetype($full_file) == 'file')
			{
				// download file
				break;
			}
		}
	}
}

if ( (isset($full_file)) and (file_exists($full_file)) )
{
	$file_extension = strtolower(array_pop(explode('.', $full_file)));
	
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
}

?>