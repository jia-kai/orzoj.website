<?php
require_once 'pre_include.php';
require_once $includes_path . 'user.php';

$rows = $odb->select_from('problem'); //,  NULL, array($DBOP['!='], 'id', 1));

/*
$db->insert_into('problems',
	array('title' => 'A+B Problem',
	'code' => 'a+b',
	'perm' => serialize(array(0, 1, array(GID_ALL), array())),
	'io' => serialize(array('a+b.in', 'a+b.out')),
	'time' => time(),
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
 */

foreach ($rows as $row)
{
	$code = odb_get_prob_code($row['id']);
	$desc = array(
		'time' => $row['timelimit'],
		'memory' => $row['memorylimit'],
		'desc' => $row['description'],
		'input_fmt' => $row['inputformat'],
		'output_fmt' => $row['outputformat'],
		'input_samp' => $row['sampleinput'],
		'output_samp' => $row['sampleoutput'],
		'range' => 'Sorry, I do not know...',
		'source' => $row['source'],
		'hint' => $row['hint']
	);
	$db->insert_into('problems', array(
		'title' => $row['title'],
		'code' => $code,
		'desc' => serialize($desc),
		'perm' => serialize(array(0, 1, array(), array())),
		'io' => serialize(array($row['inputfile'], $row['outputfile'])),
		'time' => time()
	));
}

