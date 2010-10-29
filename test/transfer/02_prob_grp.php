<?php
require_once 'pre_include.php';
require_once $includes_path . 'problem.php';

$pid = $db->insert_into('prob_grps', array(
	'pgid' => 0,
	'name' => 'NOIP',
	'desc' => 'NOIP 难度的相关题目'
));

prob_update_grp_cache_add($pid);

$pid = $db->insert_into('prob_grps', array(
	'pgid' => $pid,
	'name' => 'NOIP2010',
	'desc' => ''
));

prob_update_grp_cache_add($pid);

$root_id = $db->insert_into('prob_grps', array(
	'pgid' => $pid,
	'name' => '七中模拟赛',
	'desc' => '成都七中内部举行的模拟赛'
));

prob_update_grp_cache_add($root_id);

$rows = $odb->select_from('contest', array('id', 'start'), array($DBOP['!='], 'id', 1));

foreach ($rows as $row)
{
	$pid = $db->insert_into('prob_grps', array(
		'pgid' => $root_id,
		'name' => strftime('%m-%d', $row['start']),
		'desc' => '在 ' . time2str($row['start']) . ' 举行的比赛 (迁移自旧版OJ)'
	));

	prob_update_grp_cache_add($pid);

	$probs = $odb->select_from('problem', 'id',
		array($DBOP['='], 'contestid', $row['id']));

	foreach ($probs as $prob)
		$db->insert_into('map_prob_grp', array(
			'pid' => prob_get_id_by_code(odb_get_prob_code($prob['id'])),
			'gid' => $pid
		));
}

