<?php
if (!defined('IN_ORZOJ')) exit;
if (!function_exists('__'))
{
	function __($str) {return $str;}
}
class OIContestRule extends ContestRule
{
	function getContestName() { return __('Olympiad in Informatics')};
	function getNewRecordID($problemid,$userid)
	{
		global $db,$tablepre;
		$problemid = (int)($problemid);
		$userid=  (int)($userid);
		$whereclause = array(
			'param1' => array('param1' => 'problemid','op1' => 'int_eq','param2' => $problemid),
			'op1' => 'logical_and',
			'param2' = array('param1' => 'userid','op1' => 'int_eq','param2' => $userid));
		$select_result = $db->select_from($tablepre,NULL,$whereclause);
		if ($select_result !== FALSE && isset($select_result[0]))
			return $select_result[0]['id'];
		else
			return 0;
	}
	function RankByRecordsInfo($records)
	{
		$rank = array();
	}
}
