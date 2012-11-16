<?php

$pages = $db->force_multi_assoc('SELECT `alias`, `name` FROM `'.DB_PAGES_TABLE.'` ORDER BY `name` ASC');

if ( (sizeof($pages) > 0) and (is_array($pages)) )
{
	$build = array();
	foreach($pages as $page)
	{
		$selected  = (CURRENT_ALIAS == $page['alias']) ? ' selected="selected"' : '';
		if (CURRENT_ALIAS == $page['alias'])
		{
			$current_page = $page['name'];
		}
		$navigate .= '<option value="'.$page['alias'].'"'.$selected.'>'.$page['name'].'</option>';
	}
}

$add    = (USER_ACCESS > 3) ? '<span class="click" onclick="Pico_AddPage()">Add New Page</span>' : '<span class="inactive">Add New Page</span>';
$edit   = (USER_ACCESS > 2) ? '<span class="click" onclick="Pico_EditPage()">Edit This Page</span>' : '<span class="inactive">Edit This Page</span>';
$delete = (USER_ACCESS > 3) ? '<span class="click" onclick="Pico_DeletePage()">Delete This Page</span>' : '<span class="inactive">Delete This Page</span>';
$clone  = (USER_ACCESS > 2) ? '<span class="click" onclick="Pico_ClonePage()">Clone This Page</span>' : '<span class="inactive">Clone This Page</span>';
$bulk   = (USER_ACCESS > 4) ? '<span class="click" onclick="Pico_BulkClonePage()">Bulk Add Pages</span>' : '<span class="inactive">Bulk Add Pages</span>';
$sh     = (USER_ACCESS > 3) ? '<span class="click" onclick="Pico_SiteHeirarchy()">Site Hierarchy</span>' : '<span class="inactive">Site Hierarchy</span>';
$bulkc  = (USER_ACCESS > 4) ? '<span class="click" onclick="Pico_BulkAddContent()">Bulk Add Content</span>' : '<span class="inactive">Bulk Add Content</span>';
$payment = (USER_ACCESS > 3) ? '<span class="click" onclick="Pico_PaymentSettings()">Payment Settings</span>' : '<span class="inactive">Payment Settings</span>';

$content_add_new      = (USER_ACCESS > 3) ? '<div class="click" onclick="Pico_ToggleAddContent(0)">Add New Content</div>'      : '<div class="inactive">Add New Content</div>';
$content_add_existing = (USER_ACCESS > 3) ? '<div class="click" onclick="Pico_ToggleAddContent(1)">Add Existing Content</div>' : '<div class="inactive">Add Existing Content</div>';
$content_edit         = (USER_ACCESS > 2) ? '<div class="click" onclick="Pico_ToggleEditContent()">Edit Content</div>'         : '<div class="inactive">Edit Content</div>';
$content_remove       = (USER_ACCESS > 3) ? '<div class="click" onclick="Pico_ToggleDeleteContent()">Remove Content</div>'     : '<div class="inactive">Remove Content</div>';
$content_move         = (USER_ACCESS > 3) ? '<div class="click" onclick="Pico_ToggleMoveContent()">Move Content</div>'         : '<div class="inactive">Move Content</div>';

?>
<table border="0" cellpadding="0" cellspacing="3" id="ap_table">
<tr>
	<td valign="top" width="100">
		<div class="title menu_item click" onclick="Pico_ShowPanel('lap_content')"><img src="<?=$body->url('/includes/icons/content.png')?>" class="icon" align="left" />Content</div>
		<div class="clear title menu_item click" onclick="Pico_ShowPanel('lap_pages')"><img src="<?=$body->url('/includes/icons/pages.png')?>" class="icon" align="left" />Pages</div>
		<div class="clear title menu_item click" onclick="Pico_ShowPanel('lap_users')"><img src="<?=$body->url('/includes/icons/users.png')?>" class="icon" align="left" />Users</div>
	</td>
	<td valign="top">
		<div id="lap_pages">
			<ul>
				<li><?=$add?></li>
				<li><?=$edit?></li>
				<li><?=$delete?></li>
				<li><?=$clone?></li>
				<li><?=$bulk?></li>
			</ul>
			<ul>
				<li><?=$sh?></li>
			</ul>
		</div>
		<div id="lap_content">
			<ul>
				<li><?=$content_add_new?></li>
				<li><?=$content_add_existing?></li>
				<li><?=$content_edit?></li>
				<li><?=$content_remove?></li>
				<li><?=$content_move?></li>
			</ul>
			<ul>
				<li><?=$bulkc?></li>
				<li><?=$payment?></li>
			</ul>
		</div>
		<div id="lap_users">
			<ul>
				<li><div class="click" onclick="Pico_AddUser()">Add New User</div></li>
				<li><div class="click" onclick="Pico_ManageUsers()">Manage Current Users</div></li>
				<li><div class="click" onclick="Pico_ManageGroups()">Manage User Groups</div></li>
				<li><div class="click" onclick="Pico_UserProfiles()">User Profiles</div></li>
			</ul>
		</div>
		&nbsp;
	</td>
	<td valign="top" width="300">
		<div class="clear menu_item">
			Navigate: 
			<select id="navigate" onchange="Navigate(this)">
			<?=$navigate?>
			</select>
		</div>
		<table border="0" cellpadding="0" cellspacing="1" width="100%">
		<tr>
			<td valign="top" width="50%">
				<div class="clear title menu_item"><img src="<?=$body->url('/includes/icons/logout.png')?>" class="icon" align="left" /><a href="<?=$body->url('logout')?>">Log Out</a></div>
				<div class="clear title menu_item"><img src="<?=$body->url('/includes/icons/help.png')?>" class="icon" align="left" /><a target="_blank" href="<?=$body->url('help.pdf')?>">Help</a></div>
			</td>
			<td valign="top" width="50%">
				<div class="clear title menu_item"><img src="<?=$body->url('/includes/icons/info.png')?>" class="icon" align="left" /><div class="click" onclick="Pico_FTPSettings()">FTP Settings</div></div>
				<div class="clear title menu_item"><img src="<?=$body->url('/includes/icons/edit.png')?>" class="icon" align="left" /><div class="click" onclick="Pico_Update()">Check for updates</div></div>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>