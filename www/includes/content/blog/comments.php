<?php

chdir('../../../');
require_once('core.php');
require_once('includes/captcha.class.php');
require_once('includes/content/blog/functions.php');

$action = $_REQUEST['page_action'];

if ($action == 'post_new')
{
	// verify captcha
	// return "0|thankyou or 1|error"
	
	//echo "0|mmmmmm";
	
	$post_id   = $_POST['post_id'];
	$key       = 'verify_'.$post_id;
	$keyphrase = decrypt($_POST['verifyphrase']);
	$email     = $_POST['email'];
	$email_regex  = '/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}$/';
	$component_id = $db->result('SELECT `component_id` FROM `'.$blog_entries.'` WHERE `post_id`=? LIMIT 1', $post_id);
	
	$instance_id = md5('blog_' .  $component_id . '_' . $post_id);
	$captcha     = new Captcha($instance_id, $db, getenv('REMOTE_ADDR'));
	
	if (strlen($_POST['name']) == 0)
	{
		echo "1|Invalid name, please correct and try again";
	}
	elseif (strlen($_POST['message']) < 2)
	{
		echo "1|Invalid message, please correct and try again";
	}
	elseif (!preg_match($email_regex, $email))
	{
		echo "1|Invald E-mail Address, please correct and try again";
	}
	elseif (!$captcha->Verify($_POST['verify']))
	{
		echo "1|Invalid image verification, please correct and try again";
	}
	else
	{
		// add
		if ($component_id === FALSE)
		{
			echo "1|Error adding comment";
			return;
		}
		$options = $db->assoc('SELECT * FROM `'.$blog_options.'` WHERE `component_id`=?', $component_id);
		
		$name    = strip_tags(stripslashes($_POST['name']));
		$message = strip_tags(stripslashes($_POST['message']));
		$url     = strip_tags(stripslashes($_POST['url']));
		$parent  = strip_tags(stripslashes($_POST['parent']));
		
		$page_id      = $db->result('SELECT `page_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=? LIMIT 1', $component_id);
		$alias        = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
		$blog_alias   = $db->result('SELECT `alias` FROM `'.$blog_entries.'` WHERE `post_id`=? LIMIT 1', $post_id);
		$comment_link = 'http://'.$_SERVER['SERVER_NAME'] . $body->url($alias . '/' . $blog_alias);
		
		if ($options['allow_comments'] == 1)
		{
			$new_post = $db->insert('INSERT INTO `'.$blog_comments.'` (`post_id`, `name`, `active`, `date`, `ip_address`, `message`, `email`, `url`, `parent`) VALUES (?,?,?,?,?,?,?,?,?)',
				$post_id, $name, 1, time(), getenv('REMOTE_ADDR'), $message, $email, $url, $parent
			);
			
			$scroll = ($parent != 0) ? $parent : $new_post;
			echo "2|".$scroll;
			
			$_message = nl2br($message);
			$link = 'http://'.$_SERVER['SERVER_NAME'] . $body->url('includes/content/blog/comments.php?page_action=deny&deny='.$new_post);
			
			$comment_message = "$name ($email) just posted a comment on your blog (<a href=\"$comment_link\">here</a>). To remove it, click here: $link (this link will only work if you are logged in)<br /><br />The posted message is as follows:<br />$_message";
		}
		else
		{
			$new_post = $db->insert('INSERT INTO `'.$blog_comments.'` (`post_id`, `name`, `active`, `date`, `ip_address`, `message`, `email`, `url`, `parent`) VALUES (?,?,?,?,?,?,?,?,?)',
				$post_id, $name, 0, time(), getenv('REMOTE_ADDR'), $message, $email, $url, $parent
			);
			
			$_message = nl2br($message);
			$link = 'http://'.$_SERVER['SERVER_NAME'] . $body->url('includes/content/blog/comments.php?page_action=approve&approve='.$new_post);
			echo "0|Your message will be posted as soon as it is approved. Thank you";
			$comment_message = "$name ($email) just posted a comment on your blog (<a href=\"$comment_link\">here</a>). To approve it, click here: $link (this link will only work if you are logged in)<br /><br />The posted message is as follows:<br />$_message";
		}
		
		// send an e-mail
		
		require_once('includes/class.phpmailer.php');
		$mail = new PHPMailer();
		$mail->From = ADMIN_EMAIL;
		$mail->FromName = ADMIN_FROM;
		$mail->AddAddress($options['moderator_address']);
		$mail->IsHTML(true);
		$mail->Subject = 'New Blog Comment';
		$mail->Body    = $comment_message;
		$mail->Send();
	}
	
	return;
}
elseif ($action == 'approve')
{
	$comment_id = $_GET['approve'];
	$db->run('UPDATE `'.$blog_comments.'` SET `active`=? WHERE `comment_id`=?', 1, $comment_id);
	
	echo "Comment approved, you may now close this window";
	if (USER_ACCESS < 3) { exit(); }
	// echo out comments
}
elseif ($action == 'deny')
{
	$comment_id = $_GET['deny'];
	$db->run('UPDATE `'.$blog_comments.'` SET `active`=? WHERE `comment_id`=?', 0, $comment_id);
	
	echo "Comment denied, you may now close this window";
	if (USER_ACCESS < 3) { exit(); }
	// echo out comments
}
elseif ($action == 'reload_comments')
{
	$post_id = $_GET['post_id'];
	$component_id = $db->result('SELECT `component_id` FROM `'.$blog_entries.'` WHERE `post_id`=?', $post_id);
	$options      = $db->assoc('SELECT * FROM `'.$blog_options.'` WHERE `component_id`=?', $component_id);
	
	$comment_layout = trim($options['comment_layout']);
	if (strlen($comment_layout) == 0)
	{
		$comment_layout = <<<HTML
	<div class="comment_name">NAME said on DATE:</div>
	<div class="comment_message">MESSAGE</div>
HTML;
	}
	
	$op = get_blog_comments($post_id, $comment_layout);
	echo $op;
}
