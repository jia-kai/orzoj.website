<?php

require_once '../pre_include.php';
require_once $includes_path . 'problem.php';

define('NPROB', 50);
define('NPGRP', 50);
define('NMAP', 80);

$db->delete_item('problems');
$db->delete_item('prob_grps');
$db->delete_item('map_prob_grp');
$db->delete_item('cache_pgrp_child');

function make_prob()
{
	global $db, $DBOP;
	$db->insert_into('problems',
		array('title' => 'A+B Problem',
		'code' => 'a+b',
		'perm' => serialize(array(0, 1, array(GID_ALL), array())),
		'io' => serialize(array('a+b.in', 'a+b.out')),
		'time' => time(),
		'cnt_submit' => 10,
		'cnt_ac' => 2,
		'cnt_unac' => 4,
		'cnt_ce' => 1,
		'ac_ratio' => 103,
		'desc' => serialize(array(
			'time' => '1s',
			'memory' => '256MB',
			'desc' => 'Calculate a + b',
			'input_fmt' => 'Two numbers in a single row.',
			'output_fmt' => 'A number, the sum of a and b.',
			'input_samp' => '1 2',
			'output_samp' => '3',
			'source' => 'Every OJ'
	//		'hint' => '1 <= a, b <= 100000'
		))
	));

	for ($i = 0; $i < NPROB - 1; $i ++)
		$db->insert_into('problems',
			array(
				'title' => rand(),
				'code' => rand(),
				'perm' => serialize(array(0, 1, array(GID_ALL), array())),
				'io' => '',
				'time' => time(),
				'cnt_submit' => rand(),
				'cnt_ac' => rand(),
				'cnt_unac' => rand(),
				'cnt_ce' => rand(),
				'ac_ratio' => rand(0, 10000),
				'desc' => serialize(array(
					'time' => rand(),
					'memory' => rand(),
					'desc' => rand(),
					'input_fmt' => rand(),
					'output_fmt' => rand(),
					'input_samp' => rand(),
					'output_samp' => rand(),
					'source' => rand(),
					'hint' => rand()
				))
			)
		);
}

function make_pgrp()
{
	global $db;
	for ($i = 0; $i < NPGRP; $i ++)
	{
		$id = $db->insert_into('prob_grps', array('pgid' => rand(0, $i - 1), 'name' => "prob_grp $i"));
		prob_update_grp_cache_add($id);
	}
}

function make_map()
{
	global $db;
	for ($i = 0; $i < NMAP; $i ++)
		$db->insert_into('map_prob_grp', 
			array('pid' => rand(1, NPROB),
			'gid' => rand(1, NPGRP))
		);
}

make_prob();
make_pgrp();
make_map();

