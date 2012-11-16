<?php
if (isset($_GET['refresh']))
{
	$entry_id = $_GET['entry_id'];
	chdir('../../../');
	require_once('core.php');
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

require_once('includes/content/blog/functions.php');

$blog_comments  = DB_PREFIX . 'pico_blog_comments';

$layout = <<<HTML
<div class="blog_admin_comment">
	<div class="name">NAME<br />EMAIL<br />DATE</div>
	<div class="message">MESSAGE</div>
	<div class="actions">ACTIONS</div>
	<div class="clear"></div>
</div>
HTML;

?>
<div class="ap_overflow" style="height: 425px">
	<h3 class="blog_choice">Comments</h3>
	<?=get_blog_comments($entry_id, $layout, 0, true)?>
</div>