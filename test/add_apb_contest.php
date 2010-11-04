<?php

require_once '../pre_include.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'contest/ctal.php';
require_once $includes_path . 'user.php';


function make($s, $t)
{
	global $db;
	$cid = $db->insert_into('contests', array(
		'type' => 0,
		'name' => 'a+b -' . rand(),
		'desc' => 'this is a+b contest #' . rand(),
		'time_start' => $s,
		'time_end' => $t,
		'perm' => serialize(array(0, 1, array(GID_ALL), array()))
	));
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
	$ct = ctal_get_class_by_cid($cid);
	$ct->add_contest();
}

make(time() + 10, time() + 70);

