<?php
require_once '../pre_include.php';

define('NTEAM', 50);

$db->insert_into('user_teams', array(
	'id' => USER_TID_NONE,
	'name' => 'None',
	'desc' => 'no team',
	'img' => 'none.gif'
));


for ($i = 0; $i < NTEAM; $i ++)
	$db->insert_into('user_teams', array(
		'name' => 'team-' . rand(),
		'desc' => 'team-desc-' . rand(),
		'img' => 'none.gif'
	));

