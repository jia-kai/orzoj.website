<?php

require_once '../pre_include.php';

define('NPAST', 100);
define('NCURRENT', 100);
define('NFUTURE', 100);

$db->delete_item('contests');

function make($s, $t)
{
	global $db;
	$db->insert_into('contests', array(
		'type' => rand(0, 1),
		'name' => 'contest-' . rand(),
		'desc' => 'thie is contest #' . rand(),
		'time_start' => $s,
		'time_end' => $t,
		'perm' => serialize(array(0, 1, array(GID_ALL), array()))
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

