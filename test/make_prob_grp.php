<?php

require_once '../pre_include.php';
require_once $includes_path . 'problem.php';
$db->delete_item('prob_grps');
$db->delete_item('cache_pgrp_child');

for ($i = 0; $i < 100; $i ++)
{
	$id = $db->insert_into('prob_grps', array('pgid' => rand(0, $i), 'name' => "$i"));
	prob_update_grp_cache_add($id);
}
