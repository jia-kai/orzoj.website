<?php

require_once '../pre_include.php';

if(defined('TEST_CONTEST_LIST'))
{
	define('NPAST', 100);
	define('NCURRENT', 100);
	define('NFUTURE', 100);
}
else
{
	define('NPAST', 0);
	define('NCURRENT', 1);
	define('NFUTURE', 0);
}

$db->delete_item('contests');
$db->delete_item('map_prob_ct');

function make($s, $t)
{
	global $db;
	$cid = $db->insert_into('contests', array(
		'type' => defined('TEST_CONTEST_LIST') ? rand(0, 1) : 0,
		'name' => 'contest-' . rand(),
		'desc' => 'this is contest #' . rand(),
		'time_start' => $s,
		'time_end' => $t,
		'perm' => serialize(array(0, 1, array(GID_ALL), array()))
	));
	for ($i = 0; $i < 4; $i ++)
		$db->insert_into('map_prob_ct', array(
			'cid' => $cid,
			'pid' => rand(10, 20),
			'order' => $i,
			'time_start' => $s,
			'time_end' => $t
		));
}

for ($i = 0; $i < NPAST; $i ++)
{
	$end = time() - rand(1, 1000);
	make($end - rand(100, 100000), $end);
}

for ($i = 0; $i < NCURRENT; $i ++)
	make(time() - rand(100, 10000), time() + rand(3600 * 24 * 3, 3600 * 24 * 30));

for ($i = 0; $i < NFUTURE; $i ++)
{
	$start = time() + rand(3600 * 24 * 365, 3600 * 24 * 3650);
	make($start, $start + rand(1000, 10000));
}

