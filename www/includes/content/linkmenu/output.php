<?php
chdir('../../../');
require_once('core.php');

$request = $_SERVER['REQUEST_URI'];
list($base, $params) = explode('?', $request);
$main  = str_replace('.png', '', $base);
$parts = explode('/', $main);

$menu_item    = array_pop($parts);
$component_id = array_pop($parts);
$page_id      = array_pop($parts);

$component_info = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$instance_id = GenerateInstanceID($component_id, $component_info['view_setting'], $page_id, '');

$options  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
$settings = unserialize($options);
if (!is_array($settings)) { $settings = array(); }

$content = $db->result('SELECT `content` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
if ($content == FALSE) { $content = ''; }

$data = explode(',', $content);
if (!is_array($data)) { $data = array(); }

$links = array();
if (sizeof($data) > 0)
{
	foreach($data as $key=>$val)
	{
		if (strlen($val) > 0)
		{
			$links[$key] = $val;
		}
	}
}

$link = $links[$menu_item];
//echo '<pre>'.print_r($link, TRUE).'</pre>';

if (substr($link, 0, 2) == 'l_')
{
	$link_id = substr($link, 2);
	$link_data = $db->assoc('SELECT * FROM `'.DB_LINKS.'` WHERE `link_id`=?', $link_id);
	$text = $link_data['name'];
}
else
{
	$link_id = $link;
	$link_data = $db->assoc('SELECT `alias`, `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $link_id);
	$text = $link_data['name'];
}
// ============================================================================================

$text .= ' ';

$font       = 'includes/content/linkmenu/fonts/'.$settings['font'];
$fontsize   = $settings['font_size'];
$text_color = ($_GET['active'] == 'true') ? $settings['active_color'] : $settings['font_color'];

//$text = 'asdfasdfasdfasdfa ';

$text_data = imagettfbbox($fontsize, 0, $font, $text);
$text_data_height = imagettfbbox($fontsize, 0, $font, $text . 'Gg');
$color_data = html2rgb($text_color);
$r = $color_data['r'];
$g = $color_data['g'];
$b = $color_data['b'];

$image_width = $text_data[2] - $text_data[0];
$image_height = $text_data_height[1] - $text_data[7] + 5;

$image_width = $text_data[4] + (0 - $text_data[6]);
$image_height = $text_data_height[1] + (0 - $text_data_height[7]) + 2;

//$image_height = ($fontsize * 1.5);
$im = imagecreatetruecolor($image_width, $image_height);

$font_color = imagecolorallocate($im, $r, $g, $b);

imagesavealpha($im, true);

$trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $trans_colour);

if ($settings['dropshadow'] == 'yes')
{
	$black = imagecolorallocate($im, 0, 0, 0);
	imagettftext($im, $fontsize, 0, (2 -$text_data[6]), (2 - $text_data[7]), $black, $font, $text);
}

imagettftext($im, $fontsize, 0, (0 -$text_data[6]), (0 - $text_data[7]), $font_color, $font, $text);

header("Content-type: image/png"); //Picture Format
header("Expires: Mon, 01 Jul 2003 00:00:00 GMT"); // Past date
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Consitnuously modified
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Pragma: no-cache"); // NO CACHE

imagepng($im);

function html2rgb($color)
{
	if ($color[0] == '#')
		$color = substr($color, 1);

	if (strlen($color) == 6)
		list($r, $g, $b) = array($color[0].$color[1],
								 $color[2].$color[3],
								 $color[4].$color[5]);
	elseif (strlen($color) == 3)
		list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
	else
		return false;

	$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

	return array('r'=>$r, 'g'=>$g, 'b'=>$b);
}
?>
