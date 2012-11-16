<?php
$quote_table = DB_PREFIX . 'quote_table';

$quote = $db->assoc('SELECT * FROM `'.$quote_table.'` WHERE `instance_id`=? ORDER BY RAND() LIMIT 1', $instance_id);

if (is_array($quote))
{
	echo '<span class="quote">"' . $quote['quote'] . '"</span><span class="who">- ' . $quote['who'] .'</span>';
}
?>