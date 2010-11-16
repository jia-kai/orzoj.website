<?php

require_once '../pre_include.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'contest/oi.php';
require_once $includes_path . 'user.php';


function make($s, $t)
{
	global $db;
	$ct = new Ctal_oi(array(
		'type' => 0,
		'name' => 'a+b -' . rand(),
		'desc' => 'this is a+b contest #' . rand(),
		'time_start' => $s,
		'time_end' => $t,
		'perm' => serialize(array(0, 1, array(GID_ALL), array()))
	));
	$cid = $ct->add_contest();
	$db->insert_into('map_prob_ct', array(
		'cid' => $cid,
		'pid' => prob_get_id_by_code('a+b'),
		'order' => 2
	));
	$db->insert_into('map_prob_ct', array(
		'cid' => $cid,
		'pid' => prob_get_id_by_code('a+b2'),
		'order' => 1
	));
}

make(time() + 20, time() + 70);

