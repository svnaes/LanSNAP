<?php
require_once('includes/content/contact/functions.php');
require_once('includes/captcha.class.php');
$contact_table = DB_PREFIX . 'pico_contact_form';
$history_table  = DB_PREFIX . 'pico_contact_history';

$settings = $db->assoc('SELECT * FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
$fields   = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);

$fields   = unserialize($fields);
if (!is_array($fields)) { $fields = array(); }
global $params;

if (sizeof($fields) == 0)
{
	echo 'Please configure this component before using it';
	return;
}

// generates a new captcha, or restores it if we are verifying
$captcha = new Captcha($instance_id, $db, getenv('REMOTE_ADDR'));

if ($_POST['submit'] == 'contact')
{
	// do some shit
	
	// verify captcha
	
	if ($captcha->Verify($_POST['verify']))
	{
		$result = CF_Process($_POST, $fields, $component_id, $settings);
		if ($result == TRUE)
		{
			// show success message
			echo $settings['complete_message'];
			return;
		}
		else
		{
			$error = $result;
		}
	}
	else
	{
		$error = 'You did not type the verification image properly.';
		$data = $_POST['fields'];
	}
}
else
{
	$data = array();
}

// generate captcha
$captcha_img = '<img src="'.$body->url($captcha->Image()).'" />';

$output = array();

if (sizeof($fields) > 0)
{
	$counter      = 0;
	$num_required = 0;
	
	ob_start();
	
	if ($settings['layout'] == 'stacked')
	{
		include('includes/content/contact/layouts/stacked.php');
	}
	elseif ($settings['layout'] == 'compact')
	{
		include('includes/content/contact/layouts/compact.php');
	}
	else
	{
		include('includes/content/contact/layouts/classic.php');
	}
	
	$normal_output = ob_get_contents();
	ob_end_clean();
}

echo $settings['preview_message'];

if (isset($error))
{
	echo '<h3>Error</h3><div class="error">'.$error.'</div>';
}

$keyphrase = generate_text(5);
$_SESSION['contact_code'] = encrypt($keyphrase);
?>
<form method="post" action="<?=$body->url(CURRENT_ALIAS)?>" enctype="multipart/form-data">
<input type="hidden" name="max_file_size" value="10485760" />
<input type="hidden" name="submit" value="contact" />
<div class="contact_form">
<?php

if ($show_req) { echo '<div class="indicate">* indicates a required field</div>'; }
echo $normal_output;

echo '<div class="submit_button">';

if (strlen($settings['submit_button']) > 0)
{
	$file = 'includes/content/contact/storage/buttons/'.$settings['submit_button'];
	if (is_file($file))
	{
		echo '<input class="submit" type="image" src="'.$body->url($file).'" />';
	}
	else
	{
		echo '<input class="submit" type="submit" value="Submit" />';
	}
}
else
{
	if (file_exists('includes/content/contact/submit.jpg')) // legacy
	{
		echo '<input class="submit" type="image" src="'.$body->url('includes/content/contact/submit.jpg').'" />';
	}
	else
	{
		echo '<input class="submit" type="submit" value="Submit" />';
	}
}

echo '</div>';

?>
</div>
</form>

<?php
if (USER_ACCESS > 2)
{
	echo '<p><a href="'.$body->url('includes/content/contact/export.php?form_id='.$component_id).'">Contact History</a></p>';
}
?>