<?php
$question_list = $db->force_multi_assoc('SELECT * FROM `'.$survey_questions.'` WHERE `component_id`=? ORDER BY `question_id` ASC',
	$component_id
);


$results = '<table border="0" cellpadding="0" cellspacing="0" class="survey_answers">';
$question_ids = array();

if (is_array($question_list))
{
	$results .= '<tr><th></th>';
	foreach ($question_list as $question)
	{
		$question_ids[] = $question['question_id'];
		$results .= '<th>'.$question['question'].'</th>';
	}
	$results .= '</tr>';
}

// get all the results

$user_results = $db->force_multi_assoc('SELECT * FROM `'.$survey_results.'` WHERE `component_id`=?', $component_id);
if (is_array($user_results))
{
	$counter = 0;
	foreach ($user_results as $result)
	{
		$class = ($counter % 2 == 0) ? 'a': 'b'; $counter++;
		$results .= '<tr class="'.$class.'">';
		
		$username = $db->result('SELECT `username` FROM `'.$user_table.'` WHERE `user_id`=?', $result['user_id']);
		$results .= '<td>'.$username.'</td>';
		
		$answers = unserialize($result['answers']);
		if (!is_array($answers)) { $answers = array(); }
		
		foreach ($question_ids as $id)
		{
			$results .= '<td>'.$answers[$id].'</td>';
		}
		$results .= '</tr>';
	}
}

$results .= '</table>';

$stylesheet = $body->url('includes/content/lansnap/survey_results.css');

$header = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Survey Results</title>
	<link href="$stylesheet" type="text/css" rel="stylesheet" />
</head>
<body>
HTML;

$footer = '<p><a href="'.$body->url(CURRENT_ALIAS).'">Back</a></p></body></html>';

$html = $header . $results . $footer;

define ('STATIC_HTML', $html);
?>