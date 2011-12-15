<?php
require_once 'pre_include.php';

require_once $includes_path . 'submit.php';
require_once $includes_path . 'problem.php';

$rows = $odb->select_from('record',
	array('uid', 'pid', 'language', 'source'));

$cnt = 0;
foreach ($rows as $row)
{
	odb_simulate_user_login("jiakaiadmin");
	$src = $row['source'];
	$code = odb_get_prob_code($row['pid']);
	$plang = $trans_plang[$row['language']];
	if (is_null($code))
	{
		echo "NULL code for problem $row[pid]\n";
		continue;
	}
	$cnt ++;
	if ($plang == 1)
		$src = "
		#include <cstdio>
		#include <cstdlib>
		#include <cstring>
		#include <cmath>
		" . $src;
	submit_add_record(prob_get_id_by_code($code), $plang, $src, array('', ''));
}

echo "$cnt records added\n";

