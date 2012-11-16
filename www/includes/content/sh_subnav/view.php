<?php
$sh_table = DB_PREFIX . 'pico_site_heirarchy';
require_once('includes/content/sh_subnav/functions.php');

$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

if ($settings['main_nav']==1)
{
	$data = SiteGetHeirarchy(0);
}
else
{
	// need to get the parent of this item
	$current_entry = $db->result('SELECT `entry_id` FROM `'.$sh_table.'` WHERE `page_id`=?', CURRENT_PAGE);
	if (!is_numeric($current_entry)) { return; }
	$parent = SubNav_GetMainParent($current_entry);
	$parent_info = $db->assoc('SELECT * FROM `'.$sh_table.'` WHERE `entry_id`=?', $parent);
	$data = SiteGetHeirarchy($parent);
}

if (strlen($settings['title']) > 0)
{
	echo '<div class="title">'.$settings['title'].'</div>';
}

$class = ($settings['dropdown'] == 1) ? 'dropdown' : '';

if ($settings['test_mode']==1)
{
	$children = array(
		array('id'=>5, 'children'=>array(), 'show_in_nav'=>1, 'test'=>true, 'display'=> 'Test #1'),
		array('id'=>6, 'children'=>array(), 'show_in_nav'=>1, 'test'=>true, 'display'=> 'Test #2'),
		array('id'=>7, 'children'=>array(), 'show_in_nav'=>1, 'test'=>true, 'display'=> 'Test #3'),
		array('id'=>7, 'children'=>array(), 'show_in_nav'=>1, 'test'=>true, 'display'=> 'Test #4'),
	);
	$data = array(
		array('id'=>1, 'children'=>array(), 'show_in_nav'=>1, 'test'=>true, 'display'=> 'Home'),
		array('id'=>2, 'children'=>$children, 'show_in_nav'=>1, 'test'=>true, 'display'=> 'About'),
		array('id'=>3, 'children'=>array(), 'show_in_nav'=>1, 'test'=>true, 'display'=> 'Services'),
		array('id'=>4, 'children'=>array(), 'show_in_nav'=>1, 'test'=>true, 'display'=> 'Contact'),
	);
}

SubNav_Display($data, $class);
?>