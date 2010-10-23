<?php
/* 
 * $File: ctal.php
 * $Date: Sat Oct 23 21:21:43 2010 +0800
 */
/**
 * @package orzoj-website
 * @subpackage contest
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
if (!defined('IN_ORZOJ'))
	exit;

require_once $includes_path . 'problem.php';

/**
 * contest abstract layer
 */
abstract class Ctal
{
	/**
	 * @ignore
	 */
	public $data;

	/**
	 * construction function
	 * @param array|NULL $data row in the database describing the contest or NULL if only get_form_fields will be called
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * echo contest-type specific form fields when adding a new contest
	 * @return void
	 */
	abstract protected function get_form_fields();

	/**
	 * this function is called when a new contest of a this type is added
	 * and data in the 'contests' table are inserted
	 * @return void
	 */
	abstract protected function add_contest();

	/**
	 * this function is called when the contest is updated
	 * @return void
	 */
	abstract protected function update_contest();

	/**
	 * called when user tries to view a problem in this contest before the contest ends
	 * @param array $pinfo problem information, containing $PROB_VIEW_PINFO (defined in problem.php)
	 *		may be modified
	 * @return void
	 * @exception Exc_runtime if permission denied
	 */
	abstract protected function prob_view(&$pinfo);

	/**
	 * whether a problem in this contest is allowed to appear in the problem list
	 * @return bool
	 */
	abstract protected function prob_view_allowed();

	/**
	 * get a list of problems in the contest
	 * @return array a 2-dimension array, in the following format:
	 *		[0][i]: (0 <= i < m)
	 *			head text for the ith column
	 *		[i][j]: (0 <= i < n, 0 <= j < m):
	 *			text in the ith row, jth column
	 *		[i][m]:
	 *			id of the ith problem
	 */
	abstract protected function get_prob_list();

	/**
	 * deal with user submissions for problems in this contest
	 * @param array $pinfo problem information, containing $PROB_SUBMIT_PINFO (defined in problem.php)
	 * @param int $lid programming language id
	 * @param string $src source
	 * @return void
	 */
	abstract protected function user_submit($pinfo, $lid, $src);

	/**
	 * this function is called when the judge process of a submission in this contest is finished
	 * @param int $rid record id
	 * @return void
	 */
	abstract protected function judge_done($rid);

	/**
	 * get the number of users participating in this contest
	 * @param array|NULL $where where cluase for column 'uid'
	 * @return int|NULL the number of users, or NULL if the result is unavailable currently
	 */
	abstract protected function get_user_amount($where = NULL);

	/**
	 * get final rank list of the problem
	 * @param array|NULL $where additional where caluse for column 'uid' (see /includes/db/dbal.php for where clause syntax)
	 * @return array a 2-dimension array representing the result
	 *		[0][i]: table header for column i
	 *		[i][j]: (i > 0)
	 *			array(&lt;text to be displayed&gt;, &lt;related record id, or 0 if unavailable&gt;)
	 * @exception Exc_runtime if the result is unavailable
	 */
	abstract protected function get_rank_list($where = NULL, $offset = NULL, $cnt = NULL);
}

$CONTEST_TYPE2CLASS = array('oi', 'acm');


/**
 * get the ctal class related to the problem
 * @param int $pid problem id
 * @return Ctal|NULL a Ctal instance or NULL if the problem does not belong to a problem
 */
function ctal_get_class_by_pid($pid)
{
	global $db, $DBOP;
	$now = time();
	$row = $db->select_from('map_prob_ct', 'cid',
		array($DBOP['&&'], 
		$DBOP['='], 'pid', $pid,
		$DBOP['>'], 'time_end', $now),
		array('time_start' => 'ASC'));
	if (count($row))
	{
		$row = $db->select_from('contests', NULL,
			array($DBOP['='], 'id', $row[0]['cid']));
		if (count($row) != 1)
			throw new Exc_inner(__('contest for problem #%d not found', $pid));
		$row = $row[0];
		$type = $CONTEST_TYPE2CLASS[$row['type']];
		require_once $includes_path . "contest/$type.php";
		$type = "Ctal_$type";
		return new $type($row);
	} 
	return NULL;
}

/**
 * get the ctal class of a contest
 * @param int $cid contest id
 * @return Ctal instance
 * @exception Exc_inner if no such contest
 */
function ctal_get_class_by_cid($cid)
{
	global $db, $DBOP, $CONTEST_TYPE2CLASS, $includes_path;
	$row = $db->select_from('contests', NULL, array(
		$DBOP['='], 'id', $cid));
	if (count($row) != 1)
		throw new Exc_inner(__('No such contest #%d', $cid));
	$row = $row[0];
	$type = $CONTEST_TYPE2CLASS[$row['type']];
	require_once $includes_path . "contest/$type.php";
	$type = "Ctal_$type";
	return new $type($row);
}

/**
 * get all contest types
 * @return array(<type id> => <type name>)
 */
function &ctal_get_typename_all()
{
	static $TEXT = NULL;
	if (is_null($TEXT))
	{
		$TEXT = array(
			__('Olympiad in informatics'),
			__('ACM/ICPC')
		);
	}
	return $TEXT;
}

/**
 * get contest type name by type id
 * @param int $tid type id
 * @return string
 */
function ctal_get_typename_by_type($tid)
{
	$t = &ctal_get_typename_all();
	return $t[$tid];
}

/**
 * @ignore
 */
function _ctal_get_list_make_where($time)
{
	global $DBOP;
	if (is_null($time))
		return NULL;
	$now = time();
	if ($time < 0)
		return array($DBOP['<='], 'time_end', $now);
	if ($time == 0)
		return array($DBOP['&&'],
			$DBOP['<='], 'time_start', $now,
			$DBOP['>'], 'time_end', $now);

	return array($DBOP['>'], 'time_start', $now);
}


/**
 * get the number of contests in a list
 * @param int|NULL $time specify requested contest time:
 *		<0: past contests
 *		=0: current contests
 *		>0: future contests
 *
 *		if $time is NULL, return all contests
 * @return int
 */
function ctal_get_list_size($time = NULL)
{
	global $db;
	return $db->get_number_of_rows('contests', _ctal_get_list_make_where($time));
}

/**
 * get a list of contests
 * @param array $fields requested fields, which must be a subset of
 *		id, type, name, desc, time_start, time_end, perm
 * @param int $time @see function ctal_get_list_size
 * @order_by array|NULL @see /includes/db/dbal.php
 * @param int|NULL $offset
 * @param int|NULL $cnt
 * @return array array of contest information containing requested fields.
 *		Note: some elements in the returned array may be NULL because permission denied for the contest
 */
function ctal_get_list($fields, $time = NULL, $order_by = NULL, $offset = NULL, $cnt = NULL)
{
	global $db, $user;
	$fileds_added = array();
	if (!in_array('perm', $fields))
	{
		$fileds_added[] = 'perm';
		$fields[] = 'perm';
	}

	$rows = $db->select_from('contests', $fields, _ctal_get_list_make_where($time),
		$order_by, $offset, $cnt);

	if (user_check_login())
	{
		$user_grps = $user->get_groups();
		$super_perm = $user->is_grp_member(GID_ADMIN_CONTEST);
	}
	else
	{
		$super_perm = FALSE;
		$user_grps = array(GID_GUEST);
	}

	foreach ($rows as &$row)
	{
		if (!$super_perm)
			if (!prob_check_perm($user_grps, $row['perm']))
				$row = NULL;
		if ($row != NULL)
			foreach ($fileds_added as $f)
				unset($row[$f]);
	}
	return $rows;
}

