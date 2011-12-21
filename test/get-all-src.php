<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';
require_once $includes_path . 'record.php';
require_once $includes_path . 'problem.php';
$cid = 2;


$results = $db->select_from('contests_oi', array('uid', 'prob_result'), 
	array($DBOP['='], 'cid', $cid));

foreach($results as $rst_row)
{
	$usrname = user_get_username_by_id($rst_row['uid']) . '-' .
		user_get_realname_by_id($rst_row['uid']);
	foreach (json_decode($rst_row['prob_result']) as $prob_id => $prob_rst)
	{
		$rid = $prob_rst[3];
		$lid = $db->select_from('records', array('lid'), array($DBOP['='], 'id', $rid));
		$lid = $lid[0]['lid'];
		$ltype = plang_get_type_by_id($lid);
		$pcode = prob_get_code_by_id($prob_id);
		$score = $prob_rst[1];
		$fname = "sources/$usrname-$pcode-$score.$ltype";
		$file = fopen($fname, 'w');
		fwrite($file, record_get_src_by_rid($rid));
		fclose($file);
	}
}
