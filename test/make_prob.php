<?php
require_once '../pre_include.php';

$db->delete_item('problems');
$db->insert_into('problems',
	array(
		'title' => 'A+B Problem',
		'code' => 'a+b',
		'perm' => serialize(array(0, 1, array(GID_ALL), array())),
		'io' => '',
		'time' => time(),
		'desc' => serialize(array()),
		'cnt_submit' => 100,
		'cnt_ac' => 80,
		'cnt_unac' => 10,
		'cnt_ce' => 2,
	));

$db->insert_into('problems',
	array('title' => 'A+B Problem V2',
	'code' => 'apb',
	'perm' => serialize(array(0, 1, array(GID_ALL), array())),
	'io' => '',
	'time' => time(),
	'cnt_submit' => 10,
	'cnt_ac' => 2,
	'cnt_unac' => 4,
	'cnt_ce' => 1,
	'desc' => serialize(array(
		'time' => '1s',
		'memory' => '256MB',
		'desc' => 'Calculate a + b',
		'input_fmt' => 'Two numbers in a single row.',
		'output_fmt' => 'A number, the sum of a and b.',
		'input_samp' => '1 2',
		'output_samp' => '3',
		'source' => 'Every OJ',
		'hint' => '1 <= a, b <= 100000'
	))
));

for ($i = 0; $i < 100; $i ++)
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
