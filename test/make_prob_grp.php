<?php

require_once '../pre_include.php';
for ($i = 0; $i < 50; $i ++)
	$db->insert_into('prob_grps', array('pgid' => rand(0, $i), 'name' => "$i"));
