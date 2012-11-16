<?php

function LT_GetUserID()
{
	global $db;
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	$ip_table   = DB_PREFIX . 'lansnap_addresses';
	$user_id  = $db->result('SELECT `user_id` FROM `'.$ip_table.'` WHERE `ip_address`=? AND `source`=?', $ip, 0);
	
	if (is_numeric($user_id))
	{
		return $user_id;
	}
	return FALSE;
}

function LT_GetUsername($id = 0)
{
	// gets the current username!
	global $db;
	$user_table = DB_PREFIX . 'lansnap_users';
	$user_id    = ($id == 0) ? LT_GetUserID() : $id;
	
	if (is_numeric($user_id))
	{
		$username = $db->result('SELECT `username` FROM `'.$user_table.'` WHERE `user_id`=?', $user_id);
		if (is_string($username))
		{
			return $username;
		}
	}
	return FALSE;
}

function LT_GetTeamPlayers($team_id)
{
	$team_players = DB_PREFIX . 'lantourny_team_players';
	global $db;
	
	$player_list = array();
	$players = $db->force_multi_assoc('SELECT * FROM `'.$team_players.'` WHERE `team_id`=?', $team_id);
	if (!is_array($players))
	{ 
		$player_list = array(0);
	} 
	else 
	{
		foreach ($players as $player)
		{
			$player_list[] = $player['player_id'];
		}
	}
	
	return $player_list;
}

/** sees if the player is on a team
	returns:
		false - no team
		array - array containing team info, and any players
*/
function LT_GetPlayerTeam($game_id, $user_id)
{
	$team_players = DB_PREFIX . 'lantourny_team_players';
	$team_table   = DB_PREFIX . 'lantourny_teams';
	global $db;
	
	$team_id = $db->result('SELECT `team_id` FROM `'.$team_players.'` WHERE `player_id`=? AND `game_id`=?', $user_id, $game_id);
	if (!is_numeric($team_id))
	{
		return FALSE;
	}
	
	$return = array();
	$team_info = $db->assoc('SELECT * FROM `'.$team_table.'` WHERE `team_id`=?', $team_id);
	$return['team_info'] = $team_info;
	
	// get players
	$player_list = LT_GetTeamPlayers($team_id);
	$return['player_list'] = $player_list;
	return $return;
}

function LT_GetAllTeams($game_id)
{
	global $db;
	$team_table = DB_PREFIX . 'lantourny_teams';
	
	$all_teams = $db->force_multi_assoc('SELECT * FROM `'.$team_table.'` WHERE `game_id`=? ORDER BY `name` ASC', $game_id);
	return $all_teams;
}

function LT_NextRound($game_id, $round_num)
{
	global $db;
	
	$team_table    = DB_PREFIX . 'lantourny_teams';
	$matchup_table = DB_PREFIX . 'lantourny_matchups';
	$tourny_table  = DB_PREFIX . 'lantourny_list';
	
	$game_info     = $db->assoc('SELECT * FROM `'.$tourny_table.'` WHERE `id`=?', $game_id);
	
	// get all teams that can play in this round, have winners bracket, could have loser bracket
	
	$brackets = array(
		'winners' => array(),
		'losers' => array()
	);

	// get winners (or rather non-losers)
	$all_teams = LT_GetAllTeams($game_id);
	
	foreach ($all_teams as $team)
	{
		// count losses
		$num_losses = $db->result('SELECT count(1) FROM `'.$matchup_table.'` WHERE `loser`=?', $team['team_id']);
		if ($num_losses == 1)
		{
			$brackets['losers'][] = $team['team_id'];
		}
		elseif ($num_losses == 0)
		{
			$brackets['winners'][] = $team['team_id'];
		}
	}
	
	if ( ((sizeof($brackets['winners']) == 1) and (sizeof($brackets['losers']) == 0)) or
		((sizeof($brackets['winners']) == 1) and ($game_info['elimination'] == 0)) or
		((sizeof($brackets['winners']) == 0) and (sizeof($brackets['losers']) == 1) and ($game_info['elimination'] == 1)) 
	) 
	{
		// WE HAVE A WINNER!
		// find winner
		if ((sizeof($brackets['winners']) == 1) and (sizeof($brackets['losers']) == 0))
		{
			$winner = $brackets['winners'][0];
		}
		elseif ((sizeof($brackets['winners']) == 1) and ($game_info['elimination'] == 0))
		{
			$winner = $brackets['winners'][0];
		}
		else
		{
			$winner = $brackets['losers'][0];
		}
		
		$db->run('UPDATE `'.$tourny_table.'` SET `status`=?, `current_round`=?, `winner`=? WHERE `id`=?', 2, $round_num, $winner, $game_id);
		return TRUE;
	}
	
	if ($game_info['elimination'] == 1)
	{
		// double elim
		$total_teams = sizeof($brackets['winners']) + sizeof($brackets['losers']);
		$all_teams = array_merge($brackets['winners'], $brackets['losers']);
	}
	else
	{
		$total_teams = sizeof($brackets['winners']);
		$all_teams = $brackets['winners'];
	}
	
	$modes = array('winners');
	if ($game_info['elimination'] == 1) { $modes[] = 'losers'; }
	
	// this bit will go thru each bracket (if needed) and pick a bye (if needed) and matchup everyone else, PROVIDED that there are 2 teams in said brackets
	
	foreach ($modes as $m)
	{
		if (sizeof($brackets[$m]) > 1)
		{
			if (sizeof($brackets[$m]) % 2 != 0)
			{
				// someone gets a bye
				$potential_teams = implode(',', $brackets[$m]);
				$team_ids  = implode(',', $all_teams);
				
				$most_byes = $db->result('SELECT `num_byes` FROM `'.$team_table.'` WHERE `game_id`=? AND `team_id` IN ('.$potential_teams.') ORDER BY `num_byes` DESC LIMIT 1', $game_id);
				$check     = $db->result('SELECT count(1) FROM `'.$team_table.'` WHERE `num_byes`=? AND `game_id`=? AND `team_id` IN ('.$potential_teams.')', $most_byes, $game_id);
				
				$max_byes = ($check == sizeof($brackets[$m])) ? $most_byes + 1 : $most_byes;
				
				$random_bye_team = $db->result('SELECT `team_id` FROM `'.$team_table.'` WHERE `game_id`=? AND `num_byes` < ? AND `team_id` IN ('.$potential_teams.') ORDER BY RAND() LIMIT 1', $game_id, $max_byes);
				
				if (!is_numeric($random_bye_team))
				{	
					$size = sizeof($brackets[$m]);
					return 'Error selecting bye! (check: '.$check.'/'.$size.')' . $db->error . ', ' . $db->query;
				}
				
				// insert a match for this team
				$db->run('INSERT INTO `'.$matchup_table.'` (`team1`, `team2`, `winner`, `loser`, `game_id`, `bye`, `round`) VALUES (?,?,?,?,?,?,?)',
					$random_bye_team, 0, $random_bye_team, 0, $game_id, 1, $round_num
				);
				
				$db->run('UPDATE `'.$team_table.'` SET `num_byes`=(`num_byes`+1) WHERE `team_id`=?', $random_bye_team);
				
				// remake winners variable
				$new_teams = array();
				foreach ($brackets[$m] as $team)
				{
					if ($team != $random_bye_team) { $new_teams[] = $team; }
				}
				$brackets[$m] = $new_teams;
			}
			
			// go thru the rest of the teams in $brackets[$m] and match people up
			$num_matchups = sizeof($brackets[$m]) / 2;
			for ($x = 0; $x < $num_matchups; $x++)
			{
				$team1_key = ($x * 2);
				$team2_key = ($x * 2) + 1;
				
				$team1 = $brackets[$m][$team1_key];
				$team2 = $brackets[$m][$team2_key];
				
				// assign a match for these 2 teams
				
				$db->run('INSERT INTO `'.$matchup_table.'` (`team1`, `team2`, `winner`, `loser`, `game_id`, `bye`, `round`) VALUES (?,?,?,?,?,?,?)',
					$team1, $team2, 0, 0, $game_id, 0, $round_num
				);
			}
		}
	}
	
	// it may be possible that we go thru the above and there are single teams that did not get paired up, as they are the last in their bracket.
	
	if ($game_info['elimination'] == 1)
	{
		// double elim
		
		if ( (sizeof($brackets['winners']) == 1) and (sizeof($brackets['losers']) == 1) )
		{
			// finals
			$team1 = $brackets['winners'][0];
			$team2 = $brackets['losers'][0];
			$db->run('INSERT INTO `'.$matchup_table.'` (`team1`, `team2`, `winner`, `loser`, `game_id`, `bye`, `round`) VALUES (?,?,?,?,?,?,?)',
				$team1, $team2, 0, 0, $game_id, 0, $round_num
			);
		}
		elseif (sizeof($brackets['winners']) == 1)
		{
			// bye that doesn't count towards total, need an entry so we can get to the last round
			$team1 = $brackets['winners'][0];
			$db->run('INSERT INTO `'.$matchup_table.'` (`team1`, `team2`, `winner`, `loser`, `game_id`, `bye`, `round`) VALUES (?,?,?,?,?,?,?)',
				$team1, 0, $team1, 0, $game_id, 1, $round_num
			);
		}
		elseif (sizeof($brackets['losers']) == 1)
		{
			// bye that doesn't count towards total, need an entry so we can get to the last round
			$team1 = $brackets['losers'][0];
			$db->run('INSERT INTO `'.$matchup_table.'` (`team1`, `team2`, `winner`, `loser`, `game_id`, `bye`, `round`) VALUES (?,?,?,?,?,?,?)',
				$team1, 0, $team1, 0, $game_id, 1, $round_num
			);
		}
	}
	
	$db->run('UPDATE `'.$tourny_table.'` SET `current_round`=? WHERE `id`=?', $round_num, $game_id);
	return TRUE;
}

function LT_StartTournament($game_id)
{
	global $db;
	// see how many teams there are
	
	$tourny_table = DB_PREFIX . 'lantourny_list';
	
	$return = LT_NextRound($game_id, 1);
	$db->run('UPDATE `'.$tourny_table.'` SET `status`=? WHERE `id`=?', 1, $game_id);
	return $return;
}

function LT_TeamName($team_id)
{
	global $db;
	$team_table = DB_PREFIX . 'lantourny_teams';
	$team_name  = $db->result('SELECT `name` FROM `'.$team_table.'` WHERE `team_id`=?', $team_id);
	
	return $team_name;
}
?>
