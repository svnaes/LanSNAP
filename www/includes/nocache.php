<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Cache-Control: max-age=0");
header("Pragma: no-cache");

$im = imagecreatetruecolor(1, 1);
$transparent = imagecolorallocate($im, 0, 0, 0);
imagefill($im, 0, 0, $transparent);
imagecolortransparent($im, $transparent);
header("Content-type: image/gif");
imagegif($im);
exit();
?>