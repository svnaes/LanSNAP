<?php

class Captcha
{
	var $instance_id;
	var $db;
	var $ip_addr;
	
	function Captcha($instance_id, $db, $ip_addr)
	{
		$this->instance_id = $instance_id;
		$this->db          = $db;
		$this->ip_addr     = $ip_addr;
	}
	
	function Image()
	{
		$db = $this->db;
		// see if this user has a captcha entry in the system, if so, we'll use that and return it. If not, we'll make a new one and return that.
		$captcha_table = DB_PREFIX . 'captcha_entries';
		$db->run("CREATE TABLE IF NOT EXISTS `$captcha_table` (
			`instance_id` VARCHAR(32) NOT NULL,
			`ip_addr` VARCHAR(32) NOT NULL,
			`code` VARCHAR(5) NOT NULL,
			`entry_time` BIGINT(11) NOT NULL
		)");
		
		// prune, captcha's are good for 1 hour
		$need_pruned = $db->force_multi_assoc('SELECT * `'.$captcha_table.'` WHERE `entry_time` < ?',  time() - 3600);
		if (is_array($need_pruned))
		{
			foreach ($need_pruned as $info)
			{
				$filename = md5($info['instance_id'] . '_' . $info['ip_addr'] . '_' . $info['code']) . '.jpg';
				if (is_file('includes/tmp/' . $filename))
				{
					unlink('includes/tmp/' . $filenane);
				}
			}
			// wipe db
			$db->run('DELETE FROM `'.$captcha_table.'` WHERE `entry_time` < ?',  time() - 3600);
		}
		
		$code = $db->result('SELECT `code` FROM `'.$captcha_table.'` WHERE `instance_id`=? AND `ip_addr`=?',
			$this->instance_id, $this->ip_addr
		);
		
		if (strlen($code) != 5)
		{
			$code = $this->RandText();
			$db->run('INSERT INTO `'.$captcha_table.'` (`instance_id`, `ip_addr`, `code`, `entry_time`) VALUES (?,?,?,?)',
				$this->instance_id, $this->ip_addr, $code, time()
			);
		}
		
		$filename = 'includes/tmp/' . md5($this->instance_id . '_' . $this->ip_addr . '_' . $code) . '.jpg';
		if (!is_file($filename))
		{
			// generate captcha
			$image = imagecreatefrompng('includes/noise.png'); 
			$font  = 'includes/calibrib.ttf'; // Tells the script where our font is located and it's name.
			$black = imagecolorallocate($image, 0,0,0); // Sets color to black 
			
			imagettftext($image, 20, -10, 5, 35, $black, $font, substr($code, 0, 1));
			imagettftext($image, 20, 20, 35, 35, $black, $font, substr($code, 1, 1));
			imagettftext($image, 20, -35, 65, 35, $black, $font, substr($code, 2, 1));
			imagettftext($image, 20, 25, 95, 35, $black, $font, substr($code, 3, 1));
			imagettftext($image, 20, -15, 125, 35, $black, $font, substr($code, 4, 1));

			imagejpeg($image, $filename, 100);
		}
		return $filename;
	}
	
	function Verify($check)
	{
		// verify that a user's current captcha is correct, if not then return FALSE. If so, return TRUE and remove the entry from the system so it cannot be used again.
		$captcha_table = DB_PREFIX . 'captcha_entries';
		$db            = $this->db;
		
		$code = $db->result('SELECT `code` FROM `'.$captcha_table.'` WHERE `instance_id`=? AND `ip_addr`=?',
			$this->instance_id, $this->ip_addr
		);
		
		if ($code == $check)
		{
			// erase crap
			$filename = 'includes/tmp/' . md5($this->instance_id . '_' . $this->ip_addr . '_' . $code) . '.jpg';
			if ( (is_file($filename)) and (is_writable($filename)) )
			{
				unlink($filename);
				
			}
			
			$db->run('DELETE FROM `'.$captcha_table.'` WHERE `instance_id`=? AND `ip_addr`=?', 
				$this->instance_id, $this->ip_addr
			);
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	function RandText($length = 5)
	{
		// returns some random text for the captcha
		$phrase   = "";
		$possible = "23456789ABCDEFGHJKMNPQRSTUVWXYZ"; 
		$i        = 0; 
		
		while ($i < $length)
		{ 
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
			
			if (!strstr($phrase, $char))
			{ 
				$phrase .= $char;
				$i++;
			}
		}
		return $phrase;
	}
}
?>