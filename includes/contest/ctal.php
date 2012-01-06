<?php
/* 
 * $File: ctal.php
 * $Date: Fri Jan 06 22:29:16 2012 +0800
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

$CONTEST_TYPE2CLASS = array('freesub', 'oi', 'acm');

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
	 * @param array|NULL $data row in the database describing the contest
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * echo contest-type specific form fields when adding a new contest/edit a contest
	 * if $this->data is only contains 'type' field, generate form fields for adding a new contest
	 * otherwise generate fields for editing the contest
	 *
	 * this function is called from admin context, so functions in /admin/functions.php can be used
	 *
	 * @return void
	 */
	abstract protected function get_form_fields();

	/**
	 * this function is called when a new contest of a this type is added
	 * data in the 'contests' table are NOT inserted, and should be inserted by this function
	 * @return int contest id
	 */
	abstract protected function add_contest();

	/**
	 * this function is called when the contest is updated
	 * data in the 'contests' table should be updated by this function
	 * @return void
	 */
	abstract protected function update_contest();

	/**
	 * this function is called when the contest is deleted (before contest starts)
	 * @return void
	 */
	abstract protected function delete_contest();

	/**
	 * called when user tries to view a problem in this contest before the contest ends
	 * @param array $pinfo problem information, containing $PROB_VIEW_PINFO (defined in problem.php)
	 *		may be modified
	 * @return void
	 * @exception Exc_runtime if permission denied
	 */
	abstract protected function view_prob(&$pinfo);

	/**
	 * whether problems the contest is allowed to be viewed
	 * @return bool
	 */
	abstract protected function allow_view_prob();

	/**
	 * whether result the contest is allowed to be viewed
	 * @return bool
	 */
	abstract protected function allow_view_result();

	/**
	 * get a list of problems in the contest
	 * @return array a 2-dimension array, in the following format:
	 *		[0][i]: (0 <= i < m)
	 *			head text for the ith column
	 *		[0][m]:
	 *			index of column containing the link to the problem
	 *		[i]: (1 <= i < n):
	 *			NULL if the problem is not allowed to be viewd
	 *		[i][j]: (1 <= i < n, 0 <= j < m):
	 *			text in the ith row, jth column
	 *		[i][m]: (1<= i < n)
	 *			id of the ith problem
	 */
	abstract protected function get_prob_list();

	/**
	 * deal with user submissions for problems in this contest
	 * @param array $pinfo problem information, containing $PROB_SUBMIT_PINFO (defined in problem.php)
	 * @param int $lid programming language id
	 * @param string $src source
	 * @return bool whether the submission will be judged immediately
	 */
	abstract protected function user_submit($pinfo, $lid, $src);

	/**
	 * this function is called when the judge process of a submission in this contest is finished
	 * @param int $rid record id
	 * @return void
	 */
	abstract protected function judge_done($rid);

	/**
	 * whether contest result is ready
	 * @return bool
	 */
	abstract protected function result_is_ready();

	/**
	 * get the number of users participating in this contest
	 * @param array|NULL $where where cluase for column 'uid'
	 * @return int|NULL the number of users, or NULL if the result is unavailable currently
	 */
	abstract protected function get_user_amount($where = NULL);

	/**
	 * get final rank list of the contest
	 * @param array|NULL $where additional where caluse for column 'uid' (see /includes/db/dbal.php for where clause syntax)
	 * @return array a 2-dimension array representing the result
	 *		[0][i]: (0 <= i < m)
	 *			table header for column i
	 *		[i][j]: (1 <= i < n, 0 <= j < m):
	 *			data in the ith row, jth column:
	 *			<text:string>|array(<text:string>, <link type:string>, <link value:int>)
	 *			where link type is one of "uid", "rid" (user id, record id)
	 *			link value is the corresponding id
	 * @exception Exc_runtime if the result is unavailable
	 */
	abstract protected function get_rank_list($where = NULL, $offset = NULL, $cnt = NULL);

	/**
	 * this function is called when an unprivileged user wants to view a record of this contest
	 * @param &array $row a $row from the records table, set it to NULL if viewing not allowed
	 * @return void
	 */
	abstract protected function filter_record(&$row);

	/**
	 * whether the source of a record in this contest is allowed to be viewed by an unprivileged user
	 * who is not the submitter of this record
	 * @param int $uid the uid of the record
	 * @return bool
	 */
	abstract protected function allow_view_src($uid);

	/**
	 * check whether $this->data is valid
	 * @exception Exc_runtime
	 */
	protected function check_data()
	{
		if ($this->data['time_start'] >= $this->data['time_end'])
			throw new Exc_runtime(__('the contest seems to end before it starts'));
		if ($this->data['time_end'] <= time())
			throw new Exc_runtime(__('the contest seems to have ended in the past'));
	}
}


/**
 * get the ctal class related to the problem
 * @param int $pid problem id
 * @return Ctal|NULL a Ctal instance or NULL if the problem does not belong to a contest
 */
function ctal_get_class_by_pid($pid)
{
	global $db, $DBOP;
	$now = time();
	$cid = prob_future_contest($pid);
	if (is_int($cid))
		return ctal_get_class_by_cid($cid);
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
	static $cache = array();
	if (!isset($cahce[$cid]))
	{
		$row = $db->select_from('contests', NULL, array(
			$DBOP['='], 'id', $cid));
		if (count($row) != 1)
			throw new Exc_inner(__('No such contest #%d', $cid));
		$row = $row[0];
		$type = $CONTEST_TYPE2CLASS[$row['type']];
		require_once $includes_path . "contest/$type.php";
		$cache[$cid] = array($type, $row);
	}
	list($type, $row) = $cache[$cid];
	$type = "Ctal_$type";
	return new $type($row);
}

/**
 * filter a row in the record table according to its contest type
 * @param int $cid contest id
 * @param &array $row
 * @return void
 */
function ctal_filter_record($cid, &$row)
{
	$ct = ctal_get_class_by_cid($cid);
	$ct->filter_record($row);
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
 * @exception Exc_inner if no such type
 */
function ctal_get_typename_by_type($tid)
{
	$t = &ctal_get_typename_all();
	if (!isset($t[$tid]))
		throw new Exc_inner(__('unknown contest type: %s', var_export($tid, TRUE)));
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
 * @param int|NULL $time @see function ctal_get_list_size
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

	foreach ($rows as $key => $row)
	{
		if (!$super_perm)
			if (!prob_check_perm($user_grps, $row['perm']))
				$rows[$key] = NULL;
		if ($row != NULL)
			foreach ($fileds_added as $f)
				unset($rows[$key][$f]);
	}
	return $rows;
}

/**
 * get contest name by contest id
 * @param int $cid contest id
 * @return string|NULL contest name or NULL if no such contest
 */
function ct_get_name_by_id($cid)
{
	global $db, $DBOP;
	$row = $db->select_from('contests', 'name', array(
		$DBOP['='], 'id', $cid));
	if (count($row) != 1)
		return NULL;
	return $row[0]['name'];
}

