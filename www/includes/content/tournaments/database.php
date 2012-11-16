<?php

$tourny_table = DB_PREFIX . 'lantourny_list';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$tourny_table` (
`id` BIGINT(11) AUTO_INCREMENT,
`name` VARCHAR(100),
`team_or_single` TINYINT(1) NOT NULL DEFAULT 0,
`elimination` TINYINT(1) NOT NULL DEFAULT 0,
`max_teams` INT(3),
`team_size` INT(3) NOT NULL DEFAULT 0,
`description` TEXT,
`rules` TEXT,
`time` VARCHAR(100),
`component_id` BIGINT(11) NOT NULL,
`status` TINYINT(1) NOT NULL DEFAULT 0,
`current_round` INT(3) NOT NULL DEFAULT 0,
`winner` BIGINT(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`)
)
SQL
);

$team_table = DB_PREFIX . 'lantourny_teams';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$team_table` (
`team_id` BIGINT(11) AUTO_INCREMENT,
`name` VARCHAR(100),
`join_code` VARCHAR(6),
`leader_id` BIGINT(11) NOT NULL,
`game_id` BIGINT(11) NOT NULL,
`num_byes` INT(3) NOT NULL DEFAULT 0,
PRIMARY KEY (`team_id`)
)
SQL
);

$team_players = DB_PREFIX . 'lantourny_team_players';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$team_players` (
`game_id` BIGINT(11) NOT NULL,
`team_id` BIGINT(11) NOT NULL,
`player_id` BIGINT(11) NOT NULL
)
SQL
);

$matchup_table = DB_PREFIX . 'lantourny_matchups';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$matchup_table` (
`entry_id` BIGINT(11) AUTO_INCREMENT,
`team1` BIGINT(11) NOT NULL,
`team2` BIGINT(11) NOT NULL,
`winner` BIGINT(11) NOT NULL,
`loser` BIGINT(11) NOT NULL,
`game_id` BIGINT(11) NOT NULL,
`bye` BIGINT(11) NOT NULL DEFAULT 0,
`round` INT(3) NOT NULL,
PRIMARY KEY (`entry_id`)
)
SQL
);


?>