<?php

$game_id     = $params[2];
$player_team = LT_GetPlayerTeam($game_id, $user_id);
$output      = '';

$game_info = $db->assoc('SELECT * FROM `'.$tourny_table.'` WHERE `id`=?', $game_id);
echo '<h2>'.$game_info['name'].' - Admin</h2>';

if ($game_info['status'] == 0)
{
	// tournament has not started
	
	if ($_POST['page_action'] == 'start_tournament')
	{
		$return = LT_StartTournament($game_id);
		if ($return == TRUE)
		{
			header('Location: ' . $_SERVER['REQUEST_URI']);
		}
		else
		{
			echo $return;
		}
		
		return;
	}
	
	$num_teams = $db->result('SELECT count(1) FROM `'.$team_table.'` WHERE `game_id`=?', $game_id);
	if ($num_teams < 2)
	{
		$output .= '<p class="error">You must have 2 teams or more to start this tournament</p>';
	}
	else
	{
		// show start tournament button
		
		$all_teams = LT_GetAllTeams($game_id);
		
		$output .= '<p><span class="bold">Number of teams:</span> '.sizeof($all_teams).'</p>';
		
		foreach ($all_teams as $team)
		{
			$output .= '<div class="team">'.$team['name'].' - Captian: '.LT_GetUsername($team['leader_id']).' </div>';
		}
		
		$output .= <<<HTML
<p>
<form method="post" action="$_SERVER[REQUEST_URI]">
<input type="hidden" name="page_action" value="start_tournament" />
<input type="submit" value="Start Tournament" />
</form>
</p>
HTML;

	}
}
elseif ($game_info['status'] == 1)
{
	// tournament round in progress
	
	if ($_POST['page_action'] == 'match_winner')
	{
		$match_id   = $_POST['match_id'];
		$match_info = $db->assoc('SELECT * FROM `'.$matchup_table.'` WHERE `entry_id`=?', $match_id);
		$winner     = $_POST['winner'];
		if (is_numeric($winner))
		{
			// go go gadget bullshit
			$loser = ($match_info['team1'] == $winner) ? $match_info['team2'] : $match_info['team1'];
			
			$db->run('UPDATE `'.$matchup_table.'` SET `winner`=?, `loser`=? WHERE `entry_id`=?',
				$winner, $loser, $match_id
			);
			
			// see how many matches are left
			$check = $db->result('SELECT count(1) FROM `'.$matchup_table.'` WHERE `winner`=? AND `game_id`=? AND `round`=?',
				0, $game_id, $match_info['round']
			);
			
			if ($check == 0)
			{
				// move to the next round!
				$new_round = $match_info['round']+1;
				
				$return = LT_NextRound($game_id, $new_round);
				//echo 'IN! ('.$new_round.') ' . $return;
				//return;
				/*
				if ($return != TRUE)
				{
					echo $return;
					return;
				}*/
			}
		}
		
		header('Location: ' . $_SERVER['REQUEST_URI']);
		return;
	}
	
	// show matches
	
	echo '<h3>Round '.($game_info['current_round']).' matches</h3>';
	
	$matches = $db->force_multi_assoc('SELECT * FROM `'.$matchup_table.'` WHERE `game_id`=? AND `round`=?', $game_id, $game_info['current_round']);
	
	$winners = array();
	$losers  = array();
	//echo $db->query;
	
	foreach ($matches as $match)
	{
		// see if this is a bye
		
		$bye = ($match['bye'] == 1) ? TRUE : FALSE;
		
		if ($bye)
		{
			$team_name = LT_TeamName($match['team1']);
			$output .= <<<HTML
<div class="match_entry">
Bye: $team_name
</div>
HTML;
		}
		else
		{
			$team1 = LT_TeamName($match['team1']);
			$team2 = LT_TeamName($match['team2']);
			
			if ($match['winner'] == 0)
			{
				$output .= <<<HTML
<div class="match_entry">
$team1 vs $team2<br />
<form method="post" action="$_SERVER[REQUEST_URI]" onsubmit="return confirm('Are you sure this is correct?')">
<input type="hidden" name="page_action" value="match_winner" />
<input type="hidden" name="match_id" value="$match[entry_id]" />
Choose Winner: <select name="winner">
<option value=""></option>
<option value="$match[team1]">$team1</option>
<option value="$match[team2]">$team2</option>
</select>
<input type="submit" value="Continue" />
</form>

</div>
HTML;
			}
			else
			{
				$team1class = ($match['winner'] == $match['team1']) ? 'winner' : 'loser';
				$team2class = ($match['winner'] == $match['team2']) ? 'winner' : 'loser';
			
				$output .= <<<HTML
<div class="match_entry">
<span class="$team1class">$team1</span> vs <span class="$team2class">$team2</span><br />
</div>
HTML;
			}
		}
		
	}
	
}
elseif ($game_info['status'] == 2)
{
	$winner = $game_info['winner'];
	$team_name = LT_TeamName($winner);
	
	echo '<p>Winner: '.$team_name.'</p>';
}


echo $output;
?>