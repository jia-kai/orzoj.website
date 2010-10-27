<?php
require_once 'pre_include.php';

require_once $includes_path . 'contest/oi.php';
require_once $includes_path . 'problem.php';

define('CID_OLD', 8);

$rows = $odb->select_from('contest', NULL, array($DBOP['='], 'id', CID_OLD));

foreach ($rows as $row)
{
	$val = array(
		'type' => 0,
		'name' => $row['name'],
		'desc' => $row['description'],
		'time_start' => time() + 10,
		'time_end' => time() + 40,
		'perm' => serialize(array(0, 1, array(), array()))
	);
	$cid = $db->insert_into('contests', $val);
	$val['id'] = $cid;
	$ct = new Ctal_oi($val);
	$ct->add_contest();

	$probs = $odb->select_from('problem', 'id',
		array($DBOP['='], 'contestid', $row['id']),
		array('title' => 'ASC'));

	$idx = 0;
	foreach ($probs as $p)
		$db->insert_into('map_prob_ct', array(
			'pid' => prob_get_id_by_code(odb_get_prob_code($p['id'])),
			'cid' => $cid,
			'order' => $idx ++
		));

	echo 'cid: ' . $cid . "\n";
}

