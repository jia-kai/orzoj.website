<?php

require_once '../pre_include.php';
$db->delete_item('prob_grps');
for ($i = 0; $i < 100; $i ++)
	$db->insert_into('prob_grps', array('pgid' => rand(0, $i), 'name' => "$i"));
