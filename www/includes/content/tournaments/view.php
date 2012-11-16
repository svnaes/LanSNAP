<?php
$tourny_table  = DB_PREFIX . 'lantourny_list';
$team_table    = DB_PREFIX . 'lantourny_teams';
$team_players  = DB_PREFIX . 'lantourny_team_players';
$matchup_table = DB_PREFIX . 'lantourny_matchups';

require_once('includes/content/tournaments/functions.php');

$output = '';

global $params;
if ($params[1] == 'teams')
{
	include('includes/content/tournaments/view_teams.php');
	echo '<a href="'.$body->url(CURRENT_ALIAS).'">[Back]</a>';
	return;
}
elseif ($params[1] == 'leave_team')
{
	$team_id   = $params[2];
	$team_info = $db->assoc('SELECT * FROM `'.$team_table.'` WHERE `team_id`=?', $team_id);
	$user_id   = LT_GetUserID();
	
	$game_info = $db->assoc('SELECT * FROM `'.$tourny_table.'` WHERE `id`=?', $team_info['game_id']);
	if ($game_info['status'] != 0)
	{
		echo '<p class="error">Sorry, but you cannot leave a team while the tournament is in progress</p>';
		return;
	}
	
	if ($team_info['leader_id'] == $user_id)
	{
		// disband team
		$db->run('DELETE FROM `'.$team_players.'` WHERE `team_id`=?',
			$team_id
		);
		$db->run('DELETE FROM `'.$team_table.'` WHERE `team_id`=?',
			$team_id
		);
	}
	else
	{
		// just remove player from team
		$db->run('DELETE FROM `'.$team_players.'` WHERE `team_id`=? AND `player_id`=?',
			$team_id, $user_id
		);
	}
	
	// redirect
	header('Location: ' . $body->url(CURRENT_ALIAS . '/teams/'.$team_info['game_id']));
	return;
}
elseif ($params[1] == 'matchups')
{
	include('includes/content/tournaments/view_matchups.php');
	echo '<a href="'.$body->url(CURRENT_ALIAS).'">[Back]</a>';
	return;
}
elseif ($params[1] == 'admin')
{
	if (USER_ACCESS > 1)
	{
		include('includes/content/tournaments/mod_matchups.php');
	}
	
	echo '<a href="'.$body->url(CURRENT_ALIAS).'">[Back]</a>';
	return;
}

$all_tournys = $db->force_multi_assoc('SELECT * FROM `'.$tourny_table.'` WHERE `component_id`=? ORDER BY `name` ASC', $component_id);
if (is_array($all_tournys))
{
	$counter = 0;
	foreach ($all_tournys as $tourny)
	{
		$url1 = $body->url(CURRENT_ALIAS . '/teams/'.$tourny['id']);
		$url2 = $body->url(CURRENT_ALIAS . '/matchups/'.$tourny['id']);
		$url3 = $body->url(CURRENT_ALIAS . '/admin/'.$tourny['id']);
		
		$admin = (USER_ACCESS > 1) ? ' | <a href="'.$url3.'">Admin</a>' : '';
		
		$url1text = ($tourny['team_or_single'] == 0) ? 'Create/Join Team' : 'Join this tournament';
		
		$rules = nl2br($tourny['rules']);
		
		$output .= <<<HTML
<div class="tournament_entry">
	<div class="name">$tourny[name] - $tourny[time]</div>
	<!--div class="desc">Description: $tourny[description]</div-->
	<div class="rules"><span class="bold">Rules:</span><br />$rules</div>
	<div class="more">
		<a href="$url1">$url1text</a> | 
		<a href="$url2">View Matchups</a>
		$admin
	</div>
</div>
HTML;

	}
}

echo $output;
?>