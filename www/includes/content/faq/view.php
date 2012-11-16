<?php
$faq_table = DB_PREFIX . 'faq_data';
$faqs = $db->force_multi_assoc('SELECT * FROM `'.$faq_table.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
if ( (is_array($faqs)) and (sizeof($faqs) >0) )
{
	echo '<ul>';
	$answers = '';
	foreach ($faqs as $faq)
	{
		$question = $faq['question'];
		$answer   = nl2br($faq['answer']);
		echo '<li><a href="#answer_'.$faq['faq_id'].'">'.$question.'</a></li>';
		
		$answers .= '<a name="answer_'.$faq['faq_id'].'"></a>';
		$answers .= '<div class="bold">'.$question.'</div>';
		$answers .= '<div class="answer">'.$answer.'</div>';
	}
	echo '</ul>';
}
echo '<hr />';
echo $answers;
?>