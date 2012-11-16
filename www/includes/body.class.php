<?php
/* body.class.php
 *
 * class that helps build the page output
 * 
 */

$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'body.class.php') { echo 'You cannot access this file directly'; exit(); }
 
class Body
{
	var $output = array(); // this variable will contain anything in <body>... an array that each key/val will be a line
	var $base_url; // variable to prefix all URLs with so it is directory independant
	var $head; // variable to add stuff to the <head>
	
	public function url($url)
	{
		$full_url = $this->base_url . $url;
		$full_url = str_replace('//', '/', $full_url);
		return $full_url;
	}
	
	public function add_head($text)
	{
		$this->head .= "\t" . $text . "\n";
	}
	
	public function get_head()
	{
		return $this->head;
	}
}
