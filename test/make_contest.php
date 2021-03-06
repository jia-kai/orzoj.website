<?php

require_once '../pre_include.php';
require_once $includes_path . 'user.php';

define('TEST_CONTEST_LIST', TRUE);

define('CT_NUSER', 10);

if(defined('TEST_CONTEST_LIST'))
{
	define('NPAST', 21);
	define('NCURRENT', 5);
	define('NFUTURE', 21);
}
else
{
	define('NPAST', 0);
	define('NCURRENT', 1);
	define('NFUTURE', 0);
}

$db->delete_item('contests');
$db->delete_item('map_prob_ct');

function make($s, $t, $test_result = FALSE, $type = 'past')
{
	global $db;
	static $cnt = 0;
	$cid = $db->insert_into('contests', array(
		'type' => defined('TEST_CONTEST_LIST') && !$test_result ? rand(0, 1) : 0,
		'name' => $test_result ? 'test-result-list' : 'contest-' . $type . ($cnt ++),
		'desc' => 'this is contest #' . ($cnt ++),
		'time_start' => $s,
		'time_end' => $t,
		'perm' => serialize(array(0, 1, array(GID_ALL), array()))
	));
	for ($i = 0; $i < 4; $i ++)
		$db->insert_into('map_prob_ct', array(
			'cid' => $cid,
			'pid' => rand(10, 20),
			'order' => $i
		));

	if ($test_result)
		for ($i = 0; $i < CT_NUSER; $i ++)
			$db->insert_into('contests_oi',
				array(
					'cid' => $cid,
					'uid' => $i + 1,
					'prob_result' => json_encode(array()),
					'total_score' => rand(),
					'total_time' => rand()
				));
}

for ($i = 0; $i < NPAST; $i ++)
{
	$end = time() - rand(1, 1000);
	make($end - rand(100, 100000), $end, FALSE, 'past');
}

for ($i = 0; $i < NCURRENT; $i ++)
	make(time() - rand(100, 10000), time() + rand(3600 * 24 * 3, 3600 * 24 * 30), FALSE, 'current');

for ($i = 0; $i < NFUTURE; $i ++)
{
	$start = time() + rand(3600 * 24 * 365, 3600 * 24 * 3650);
	make($start, $start + rand(1000, 10000), FALSE, 'upcomming');
}

make(time() - 10, time() - 5, TRUE);

