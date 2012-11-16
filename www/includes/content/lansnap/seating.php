<?php
chdir('../../../');
require_once('core.php');

//if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }
require_once('includes/content/lansnap/functions.php');
require_once('includes/fpdf/fpdf.php');

// go thru each table and seat, see if someone is supposed to sit there

$component_id = $_GET['component_id'];

$layout_table = DB_PREFIX . 'lansnap_layouts';
$table_table  = DB_PREFIX . 'lansnap_layout_tables';
$seat_table   = DB_PREFIX . 'lansnap_layout_seats';

require_once('includes/content/lansnap/functions.php');

$host = LS_GetSetting('externaldb_host');
if (strlen($host) > 0)
{
	// test it
	$user   = LS_GetSetting('externaldb_user');
	$pass   = LS_GetSetting('externaldb_pass');
	$dbname = LS_GetSetting('externaldb_name');
	
	$external_table = LS_GetSetting('externaldb_table');
	$external_db    = new DataBase($host, $user, $pass, $dbname);
	
	$seat_field    = LS_GetSetting('edb_linked_seat');
	$user_field    = LS_GetSetting('edb_linked_username');
	$barcode_field = LS_GetSetting('edb_linked_barcode');
	$pwd_field     = LS_GetSetting('edb_linked_password');
	
	if ($external_db->connected == FALSE)
	{
		echo $external_db->error;
		exit();
	}
}
else
{
	echo 'Please set up external database before continuing';
	exit();
}

// get the layout id
$pdf=new FPDF();

$layout_id = $db->result('SELECT `layout_id` FROM `'.$layout_table.'` WHERE `component_id`=?', $component_id);

$tables = $db->force_multi_assoc('SELECT * FROM `'.$table_table.'` WHERE `layout_id`=? ORDER BY `pos_x` ASC, `pos_y` ASC', $layout_id);
if (is_array($tables))
{
	foreach ($tables as $table)
	{
		$num_seats = $table['num_seats'];
		for ($x = 0; $x < $num_seats; $x++)
		{
			$seat_info = $db->assoc('SELECT * FROM `'.$seat_table.'` WHERE `table_id`=? AND `table_seat_number`=?',
			
				$table['table_id'], $x
			);
			
			$seat_num = $table['row'] .'-'.$seat_info['seat_num'];
			
			// check the external database to see if we have anyone in this seat
			
			//echo $seat_num . '<br />';
			
			//$username = $external_db->result('SELECT `'.$user_field.'` FROM `'.$external_table.'` WHERE `'.$seat_field.'`=?', $seat_num);
			
			$external_entry = $external_db->assoc('SELECT * FROM `'.$external_table.'` WHERE `'.$seat_field.'`=?', $seat_num);
			
			if (!is_array($external_entry))
			{
				// then this seat is empty, we need to add an entry to this table with: seat num, barcode
				//$username = 'VACANT';
				do
				{
					$barcode = strtolower(generate_text(6));
					$check = $external_db->result('SELECT count(1) FROM `'.$external_table.'` WHERE `'.$barcode_field.'`=?', $barcode);
				} while ($check != 0);
				
				$external_db->run('INSERT INTO `'.$external_table.'` (`'.$seat_field.'`, `'.$barcode_field.'`) VALUES (?,?)',
					$seat_num, $barcode
				);
				
				$external_entry = $external_db->assoc('SELECT * FROM `'.$external_table.'` WHERE `'.$seat_field.'`=?', $seat_num);
			}
			else
			{
				$barcode = $external_entry[$barcode_field];
			}
			
			// see if this seat has a password
			
			if (strlen($external_entry[$pwd_field]) == 0)
			{
				// generate a password
				$password = rand(100000, 999999);
				$external_db->run('UPDATE `'.$external_table.'` SET `'.$pwd_field.'`=? WHERE `'.$barcode_field.'`=?', $password, $barcode);
			}
			else
			{
				$password = $external_entry[$pwd_field];
			}
			
			$username = $external_entry[$user_field];
			
			if ($_GET['mode'] == 'bags')
			{
				Add_BagLabel($pdf, $username, $seat_num, $password, $barcode);
			}
			else
			{
				Add_TableLabel($pdf, $username, $seat_num, $password, $barcode);
			}
			
			
			
			//echo '<hr />';
		}
	}
}

$pdf->Output();

function Add_BagLabel(&$pdf, $username, $seat_number, $password, $barcode)
{
	$bump_x = 0;

	$pdf->AddPage('L', array(88.9, 28.575));
	$pdf->SetFont('Arial','', 30);
	$pdf->Text(3 + $bump_x,13,$seat_number);
	$pdf->SetFillColor(153, 153, 153);
	
	$pdf->SetFont('Arial','', 18);
	$pdf->Text(30 + $bump_x,11,$username);
	
	$image_file = 'includes/content/lansnap/mlp.jpg';
	
	$mod = 7;
	
	$_w = 111 / $mod;
	$_h = 60 / $mod;
	$pdf->Image($image_file, $bump_x + 3, 15, $_w, $_h);
	
	$pdf->SetFont('Arial','', 9);
	
	/*
	if ($username == 'VACANT')
	{
		$pdf->SetFont('Arial','', 9);
		$pdf->Text(29 + $bump_x, 20, 'Please bring this ticket to registration');
	}
	else
	{
		$pdf->Text(29 + $bump_x, 17, 'To register your computer, please go to');
		$pdf->Text(29 + $bump_x, 20, 'http://mlp.lan in your browser');
	}*/
	
	$barcode_width  = 42;
	$barcode_height = 5;
	
	$barcode_file = LS_GetBarcode($barcode);
	$pdf->Image($barcode_file, 29 + $bump_x, 19, $barcode_width, $barcode_height);
	
	$pdf->Text(29 + $bump_x, 17, 'Registration Password: ' . $password);
	//$pdf->Text(29 + $bump_x, 20, 'Barcode: ' . $barcode);
	
	$x1 = 29 + $bump_x;
	$x2 = 85 + $bump_x;
	$y1 = 5;
	$y2 = 13;
	
	$pdf->line($x1, $y1, $x2, $y1);
	$pdf->line($x1, $y2, $x2, $y2);
	$pdf->line($x1, $y1, $x1, $y2);
	$pdf->line($x2, $y1, $x2, $y2);
}

function Add_TableLabel(&$pdf, $username, $seat_number, $password, $barcode)
{
	$bump_x = 0;

	$pdf->AddPage('L', array(88.9, 28.575));
	$pdf->SetFont('Arial','', 30);
	$pdf->Text(3 + $bump_x,13,$seat_number);
	$pdf->SetFillColor(153, 153, 153);
	
	$pdf->SetFont('Arial','', 18);
	$pdf->Text(30 + $bump_x,11,$username);
	
	$image_file = 'includes/content/lansnap/mlp.jpg';
	
	$mod = 7;
	
	$_w = 111 / $mod;
	$_h = 60 / $mod;
	$pdf->Image($image_file, $bump_x + 3, 15, $_w, $_h);
	
	$pdf->SetFont('Arial','', 9);
	
	/*
	if ($username == 'VACANT')
	{
		$pdf->SetFont('Arial','', 9);
		$pdf->Text(29 + $bump_x, 20, 'Please bring this ticket to registration');
	}
	else
	{
		$pdf->Text(29 + $bump_x, 17, 'To register your computer, please go to');
		$pdf->Text(29 + $bump_x, 20, 'http://mlp.lan in your browser');
	}*/
	
	$barcode_width  = 42;
	$barcode_height = 5;
	
	$pdf->Text(29 + $bump_x, 19, 'Once set up please visit');
	$pdf->Text(29 + $bump_x, 22, 'http://mlp.lan in your browser');
	//$pdf->Text(29 + $bump_x, 20, 'Barcode: ' . $barcode);
	
	$x1 = 29 + $bump_x;
	$x2 = 85 + $bump_x;
	$y1 = 5;
	$y2 = 13;
	
	$pdf->line($x1, $y1, $x2, $y1);
	$pdf->line($x1, $y2, $x2, $y2);
	$pdf->line($x1, $y1, $x1, $y2);
	$pdf->line($x2, $y1, $x2, $y2);
}

?>