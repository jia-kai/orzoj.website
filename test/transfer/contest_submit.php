<?php
require_once 'pre_include.php';

require_once $includes_path . 'submit.php';
require_once $includes_path . 'problem.php';

if (!isset($_SERVER['argv'][1]))
	die('invalid commandline argument');
$cid = $_SERVER['argv'][1];

define('CID_OLD', 8);

$ct_info = $odb->select_from('contest', array('start', 'end'), array(
	$DBOP['='], 'id', CID_OLD));
$ct_info = $ct_info[0];

$rows = $odb->select_from('record',
	array('uid', 'pid', 'language', 'source'),
	array(
		$DBOP['&&'], $DBOP['&&'], 
		$DBOP['>='], 'time', $ct_info['start'],
		$DBOP['<'], 'time', $ct_info['end'],
		$DBOP['in'], 'pid', $odb->select_from(
			'problem', 'id', array($DBOP['='], 'contestid', CID_OLD),
			NULL, NULL, NULL,
			array('id' => 'pid'), TRUE
		)
	));

foreach ($rows as $row)
{
	odb_simulate_user_login(odb_user_get_name_by_id($row['uid']));
	$src = $row['source'];
	$_POST['code'] = odb_get_prob_code($row['pid']);
	$_POST['plang'] = $trans_plang[$row['language']];
	submit_src();
}

function tf_form_get_source_editor_data($xxxx)
{
	global $src;
	return $src;
}

