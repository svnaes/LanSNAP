<?php

$username = LT_GetUsername();
$user_id  = LT_GetUserID();
if ($username == FALSE) { echo '<p>You do not have access to this page</p>'; return; }

// see if this user is in a team for this game

$game_id     = $params[2];
$player_team = LT_GetPlayerTeam($game_id, $user_id);
$output      = '';

$game_info = $db->assoc('SELECT * FROM `'.$tourny_table.'` WHERE `id`=?', $game_id);
echo '<h2>'.$game_info['name'].' - Matchups</h2>';

if ($game_info['status'] == 0)
{
	echo '<p>This tournament has not started yet</p>';
}
elseif ($game_info['status'] == 2)
{
	$winner = $game_info['winner'];
	$team_name = LT_TeamName($winner);
	echo '<p class="winner">Winner: '.$team_name.'</p>';
}

// get all the rounds

$winners = array();
$losers  = array();
$elim    = array();

// get all the teams

// 3 types of matchups. 1 = winners matches. 2 = losers. 3 = finals

$rounds = array(); // $rounds[$x] = $x = the round number. 

// get all matchups by round

for ($round = 1; $round <= $game_info['current_round']; $round++)
{
	$rounds[$x] = array();
	$matchups = $db->force_multi_assoc('SELECT * FROM `'.$matchup_table.'` WHERE `round`=? AND `game_id`=?', $round, $game_id);
	if (is_array($matchups))
	{
		$rounds[$round]['winners'] = array();
		$rounds[$round]['losers']  = array();
		$rounds[$round]['finals']  = array();
		$rounds[$round]['byes']    = array();
			
		foreach ($matchups as $match)
		{
			$team1_losses = $db->result('SELECT count(1) FROM `'.$matchup_table.'` WHERE `loser`=? AND `game_id`=? AND `round` < ?', $match['team1'], $game_id, $round);
			$team2_losses = $db->result('SELECT count(1) FROM `'.$matchup_table.'` WHERE `loser`=? AND `game_id`=? AND `round` < ?', $match['team2'], $game_id, $round);
			
			if ($match['bye'] == 1)
			{
				$rounds[$round]['byes'][] = $match;
			}
			elseif (($team1_losses == 0) and ($team2_losses == 0))
			{
				// winners match
				$rounds[$round]['winners'][] = $match;
			}
			elseif (($team1_losses == 1) and ($team2_losses == 1) and (sizeof($matchups) != 1))
			{
				// losers match
				$rounds[$round]['losers'][] = $match;
			}
			else
			{
				// finals
				$rounds[$round]['finals'][] = $match;
			}
			
		}
	}
}


for ($round = 1; $round <= $game_info['current_round']; $round++)
{
	$total_matches = sizeof($rounds[$round]['winners']) + sizeof($rounds[$round]['losers']) + sizeof($rounds[$round]['finals']);
	if ($total_matches > 0)
	{
		$output .= '<div class="round">';
		$output .= '<div class="round_num">Round '.$round.'</div>';
		
		// winners
		if (sizeof($rounds[$round]['winners']) > 0)
		{
			$output .= '<div class="bracket"><div class="title">Winners Bracket</div>';
			foreach ($rounds[$round]['winners'] as $match)
			{
				$team1class = ($match['team1'] == $match['winner']) ? 'winner' : 'loser';
				$team2class = ($match['team2'] == $match['winner']) ? 'winner' : 'loser';
				if ($match['winner'] == 0) { $team1class = $team2class = ''; }
				$output .= '<div class="match"><span class="'.$team1class.'">'.LT_TeamName($match['team1']).'</span> vs <span class="'.$team2class.'">'.LT_TeamName($match['team2']).'</span></div>';
			}
			$output .= '</div>';
		}
		
		// losers
		if ( (sizeof($rounds[$round]['losers']) > 0) and ($game_info['elimination'] == 1) )
		{
			$output .= '<div class="bracket"><div class="title">Loser Bracket</div>';
			foreach ($rounds[$round]['losers'] as $match)
			{
				$team1class = ($match['team1'] == $match['winner']) ? 'winner' : 'loser';
				$team2class = ($match['team2'] == $match['winner']) ? 'winner' : 'loser';
				if ($match['winner'] == 0) { $team1class = $team2class = ''; }
				$output .= '<div class="match"><span class="'.$team1class.'">'.LT_TeamName($match['team1']).'</span> vs <span class="'.$team2class.'">'.LT_TeamName($match['team2']).'</span></div>';
			}
			$output .= '</div>';
		}
		
		// finals
		if (sizeof($rounds[$round]['finals']) > 0)
		{
			$output .= '<div class="bracket"><div class="title">Finals Bracket</div>';
			foreach ($rounds[$round]['finals'] as $match)
			{
				$team1class = ($match['team1'] == $match['winner']) ? 'winner' : 'loser';
				$team2class = ($match['team2'] == $match['winner']) ? 'winner' : 'loser';
				if ($match['winner'] == 0) { $team1class = $team2class = ''; }
				$output .= '<div class="match"><span class="'.$team1class.'">'.LT_TeamName($match['team1']).'</span> vs <span class="'.$team2class.'">'.LT_TeamName($match['team2']).'</span></div>';
			}
			$output .= '</div>';
		}
		
		// byes
		if (sizeof($rounds[$round]['byes']) > 0)
		{
			$output .= '<div class="bracket"><div class="title">Byes</div>';
			foreach ($rounds[$round]['byes'] as $match)
			{
				// see if this team has any losses
				$team_losses = $db->result('SELECT count(1) FROM `'.$matchup_table.'` WHERE `loser`=? AND `game_id`=? AND `round` < ?', $match['team1'], $game_id, $round);
				$wol = ($team_losses == 0) ? 'W' : 'L';
				$output .= '<div class="match">'.LT_TeamName($match['team1']).' ('.$wol.')</div>';
			}
			$output .= '</div>';
		}
		
		$output .= '</div>';
	}
	if ($round % 4 == 0) { $output .= '<div class="clear"></div>'; }
}

$output .= '<div class="clear"></div>';

echo $output;


/*
$all_teams = LT_GetAllTeams($game_id);

for ($round = 1; $round <= $game_info['current_round']; $round++)
{
	$winners[$round] = array();
	$losers[$round]  = array();
	$elim[$round]    = array();

	foreach ($all_teams as $team)
	{
		$team_id = $team['team_id'];
		// see how many losses they have
		
		$num_losses = $db->result('SELECT count(1) FROM `'.$matchup_table.'` WHERE `loser`=? AND `game_id`=? AND `round` < ?', $team_id, $game_id, $round);
		//echo $db->query .': '.$num_losses.'<br />';
		
		
		if ($num_losses == 0)
		{
			$winners[$round][] = $team_id;
		}
		elseif ( ($num_losses == 1) and ($game_info['elimination'] == 1) )
		{
			$losers[$round][] = $team_id;
		}
		else
		{
			$elim[$round][] = $team_id;
		}
	}
}

$col = array();
$col[1] = array();
$col[2] = array();
$col[3] = array();

//echo '<pre>'.print_r($winners, true).'</pre>';
//echo '<pre>'.print_r($losers, true).'</pre>';
//echo '<pre>'.print_r($elim, true).'</pre>';


for ($round = 1; $round <= $game_info['current_round']; $round++)
{
	if (sizeof($winners[$round]) > 0)
	{
		$_col = '';
		foreach ($winners[$round] as $team_id)
		{
			$_col .= '<div>'.LT_TeamName($team_id).'</div>';
		}
		$col[1][] = $_col;
	}
	else
	{
		$col[1][] = '';
	}
	
	if (sizeof($losers[$round]) > 0)
	{
		$_col = '';
		foreach ($losers[$round] as $team_id)
		{
			$_col .= '<div>'.LT_TeamName($team_id).'</div>';
		}
		$col[2][] = $_col;
	}
	else
	{
		$col[2][] = '';
	}
	
	if (sizeof($elim[$round]) > 0)
	{
		$_col = '';
		foreach ($elim[$round] as $team_id)
		{
			$_col .= '<div>'.LT_TeamName($team_id).'</div>';
		}
		$col[3][] = $_col;
	}
	else
	{
		$col[3][] = '';
	}
}

$col1text = '<td>'.implode('</td><td>', $col[1]).'</td>';
$col2text = '<td>'.implode('</td><td>', $col[2]).'</td>';
$col3text = '<td>'.implode('</td><td>', $col[3]).'</td>';


echo '<table>';
echo '<tr><td>Winners</td>'.$col1text.'</tr>';
echo '<tr><td>Losers</td>'.$col2text.'</tr>';
echo '<tr><td>Eliminated</td>'.$col3text.'</tr>';
echo '</table>';
*/
?>