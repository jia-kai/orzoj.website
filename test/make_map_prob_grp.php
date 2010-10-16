<?php

require_once '../pre_include.php';

for ($i = 0; $i < 300; $i ++)
	$db->insert_into('map_prob_grp', 
	array('pid' => rand(1, 100),
	'gid' => rand(1, 100)
));

