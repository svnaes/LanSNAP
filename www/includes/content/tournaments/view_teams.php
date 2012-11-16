<?php
// get the person's username

$username = LT_GetUsername();
$user_id  = LT_GetUserID();
if ($username == FALSE) { echo '<p>You do not have access to this page</p>'; return; }

// see if this user is in a team for this game

$game_id     = $params[2];
$player_team = LT_GetPlayerTeam($game_id, $user_id);
$output      = '';

$game_info = $db->assoc('SELECT * FROM `'.$tourny_table.'` WHERE `id`=?', $game_id);
echo '<h2>'.$game_info['name'].' - teams</h2>';

if ($_POST['page_action'] == 'create_team')
{
	// only create if they dont have a team
	if ($game_info['status'] != 0)
	{
		$output .= '<p class="error">Can\'t create a team for a tournament in progress</p>';
	}
	elseif ($player_team == false)
	{
		// see how many teams there are
		$num_teams = $db->result('SELECT count(1) FROM `'.$team_table.'` WHERE `game_id`=?', $game_id);
		if ($num_teams == $game_info['max_teams'])
		{
			echo '<h3>Error</h3>';
			echo '<p class="error">This tournament is full</p>';
		}
		else
		{
			if ($game_info['team_or_single'] == 0)
			{
				$team_name = trim(stripslashes($_POST['team_name']));
			}
			else
			{
				$team_name = LT_GetUsername($user_id);
			}
			
			$join_code = strtolower(generate_text(6));
			$team_id = $db->insert('INSERT INTO `'.$team_table.'` (`name`, `join_code`, `leader_id`, `game_id`) VALUES (?,?,?,?)',
				$team_name, $join_code, $user_id, $game_id
			);
			
			$db->run('INSERT INTO `'.$team_players.'` (`game_id`, `player_id`, `team_id`) VALUES (?,?,?)',
				$game_id, $user_id, $team_id
			);
			
			echo '<h3>Success!</h3>';
			
			if ($game_info['team_or_single'] == 0)
			{
				echo '<p>Created your team: <span class="bold">'.$team_name.'</span>. To have others join your team you will need to give them this code: ' . $join_code .'</p>';
				echo '<p><a href="'.$body->url(CURRENT_ALIAS . '/teams/'.$game_id).'">[Back to Teams]</a></p>';
			}
			else
			{
				echo '<p>You are now signed up</p>';
				echo '<p><a href="'.$body->url(CURRENT_ALIAS . '/teams/'.$game_id).'">[Back to Tournament]</a></p>';
			}
			
		}
		
		
		return;
	}
}
elseif ($_POST['page_action'] == 'join_team')
{
	$team_id   = $_POST['team_id'];
	$team_info = $db->assoc('SELECT * FROM `'.$team_table.'` WHERE `team_id`=?', $team_id);
	$team_code = $_POST['team_code'];
	
	if ($game_info['status'] != 0)
	{
		$output .= '<p class="error">Can\'t join a game that is in progress</p>';
	}
	elseif ($team_code != $team_info['join_code'])
	{
		$output .= '<p class="error">Invalid Join Code</p>';
	}
	else
	{
		// see how many players are on this team
		$num_players = $db->result('SELECT count(1) FROM `'.$team_players.'` WHERE `team_id`=?', $team_id);
		if ($num_players == $game_info['team_size'])
		{
			echo '<p class="error">This team is full</p>';
		}
		else
		{
			$db->run('INSERT INTO `'.$team_players.'` (`game_id`, `player_id`, `team_id`) VALUES (?,?,?)',
			$team_info['game_id'], $user_id, $team_id
			);
			header('Location: ' . $body->url(CURRENT_ALIAS . '/teams/'.$team_info['game_id']));
		}
		
		return;
	}
}

$all_teams = LT_GetAllTeams($game_id);

if ($player_team == false)
{
	// show create/signup form
	if ($game_info['team_or_single'] == 0)
	{
		$output .= '<p>You are not on a team, please join or create a team below.</p>';
	}
	else
	{
		$output .= '<p>You are not signed up for this tournament. Please sign up below.</p>';
	}
	

	if (is_array($all_teams))
	{
		
		$header = ($game_info['team_or_single'] == 0) ? 'Current Teams' : 'Current Players';
		$output .= '<h3>'.$header.'</h3>';
		foreach ($all_teams as $team)
		{
			if ($game_info['team_or_single'] == 0)
			{
				$captain_name = LT_GetUsername($team['leader_id']);
				$output .= <<<HTML
<div class="team_entry">
	<div class="team_name">$team[name]</div>
	<div class="captain">Captain: $captain_name</div>
	<div class="more">
		<form method="post" action="$_SERVER[REQUEST_URI]">
		<input type="hidden" name="page_action" value="join_team" />
		<input type="hidden" name="team_id" value="$team[team_id]" />
		Join this team: <input type="text" name="team_code" onfocus="this.value=''" value="Team Code" />
		<input type="submit" value="Join" />
		</form>
	</div>
</div>
HTML;
			}
			else
			{
				$output .= <<<HTML
<div class="player">$team[name]</div>
HTML;
			}
			


		}
	}
	
	// show create form
	
	$header = ($game_info['team_or_single'] == 0) ? 'Create New Team' : 'Join Tournament';
	$field  = ($game_info['team_or_single'] == 0) ? 'Team Name: <input type="text" name="team_name" value="" />' : '';
	
	$output .= <<<HTML
<h3>$header</h3>
<form method="post" action="$_SERVER[REQUEST_URI]">
<input type="hidden" name="page_action" value="create_team" />
$field <input type="submit" value="$header" />
</form>
HTML;
}
else
{
	// show team info
	//echo '<pre>'.print_r($player_team).'</pre>';
	
	if ($game_info['team_or_single'] == 0)
	{
		$output .= '<h3>Your Team</h3>';
		
		$output .= '<div class="team_entry">';
		$output .= '<div class="team_name">'.$player_team['team_info']['name'].'</div>';
		if ($player_team['team_info']['leader_id'] == $user_id)
		{
			$output .= '<div class="join_code">Join Code: '.$player_team['team_info']['join_code'].'</div>';
		}
		$output .= '<div class="bold players">Players:</div>';
		foreach ($player_team['player_list'] as $player)
		{
			$captain = ($player == $player_team['team_info']['leader_id']) ? ' - Captain' : '';
			$output .= '<div class="player">'.LT_GetUsername($player).$captain.'</div>';
		}
		
		$output .= '<div class="more"><a href="'.$body->url(CURRENT_ALIAS.'/leave_team/'.$player_team['team_info']['team_id']).'" onclick="return confirm(\'Are you sure you want to leave this team? If you are the team captain this will disband your team.\')">Leave This Team</a></div>';
		$output .= '</div>';
		
		$output .= '<h3>Other Teams</h3>';
		
		foreach ($all_teams as $team)
		{
			if ($team['team_id'] != $player_team['team_info']['team_id'])
			{
				$output .= '<div class="team_entry">';
				$output .= '<div class="team_name">'.$team['name'].'</div>';
				$output .= '<div class="bold players">Players:</div>';
				$player_list = LT_GetTeamPlayers($team['team_id']);
				foreach ($player_list as $player)
				{
					$captain = ($player == $team['leader_id']) ? ' - Captain' : '';
					$output .= '<div class="player">'.LT_GetUsername($player).$captain.'</div>';
				}
				$output .= '</div>';
			}
		}
	}
	else
	{
		$output .= '<h3>Tournament Players</h3>';
		
		foreach ($all_teams as $team)
		{
			$output .= '<div class="player">'.$team['name'].'</div>';
		}
		
		$output .= '<p><a href="'.$body->url(CURRENT_ALIAS.'/leave_team/'.$player_team['team_info']['team_id']).'" onclick="return confirm(\'Are you sure you want to leave this tournament?\')">Leave This Tournament</a></p>';
	}
}

echo $output;
?>