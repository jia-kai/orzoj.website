<?php
/* 
 * $File: problem.php
 * $Date: Wed Oct 27 09:57:23 2010 +0800
 */
/**
 * @package orzoj-website
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

require_once $includes_path . 'contest/ctal.php';

$PROB_SUBMIT_PINFO = array('id', 'code', 'perm', 'io');
$PROB_VIEW_PINFO = array('id', 'title', 'code', 'desc', 'perm', 'io', 'time',
	'cnt_ac', 'cnt_unac', 'cnt_ce', 'cnt_submit', 'cnt_submit_user', 'cnt_ac_user', 'cnt_ac_submission_sum',
	'difficulty', 'grp');
$PROB_VIEW_PINFO_STATISTICS = array('cnt_ac', 'cnt_unac', 'cnt_ce', 'cnt_submit', 'cnt_submit_user', 'cnt_ac_user', 
	'cnt_ac_submission_sum', 'difficulty');
// desc: exlained in simple-doc.txt and install/tables.php
// grp: array of problem group ids that this problem belongs to
// io: array of input/output file name, or NULL if using stdio
// cnt_submit: cnt_ac + cnt_unac + cnt_ce

/**
 * check whether a user has permission for a problem
 * @param array $user_grp the ids of groups that the user belongs to
 * @param array|string $perm problem permission setting, see /install/tables.php
 * @return bool whether the user is permitted
 */
function prob_check_perm($user_grp, $perm)
{
	if (!is_array($perm))
		$perm = unserialize($perm);
	if ($perm[0])
		$order = array(2, 3);
	else $order = array(3, 2);
	$match = NULL;
	foreach ($order as $o)
		if (count(array_intersect($user_grp, $perm[$o])))
			$match = $o;
	if (is_null($match))
		return $perm[1] != 0;
	return $match == 2;
}

/**
 * view a problem
 * @param int $pid problem id
 * @return string the HTML code
 * @exception Exc_runtime if permission denied
 */
function prob_view($pid)
{
	global $db, $DBOP, $PROB_VIEW_PINFO, $user;
	$row = $PROB_VIEW_PINFO;
	unset($row[array_search('cnt_submit', $row)]);
	unset($row[array_search('grp', $row)]);
	$row = $db->select_from('problems', $row,
		array($DBOP['='], 'id', $pid));
	if (count($row) != 1)
		throw new Exc_runtime(__('No such problem #%d', $pid));
	$row = $row[0];
	if (user_check_login())
	{
		$grp = $user->get_groups();
		$is_super_submitter = $user->is_grp_member(GID_SUPER_SUBMITTER);
	}
	else
	{
		$grp = array(GID_GUEST);
		$is_super_submitter = FALSE;
	}

	if (!$is_super_submitter)
	{
		$ct = ctal_get_class_by_pid($pid);
		if (!$ct)
		{
			if (!prob_check_perm($grp, $row['perm']))
				throw new Exc_runtime(__('Your are not permitted to view this problem'));
		}
	}
	$row_grp = array();
	$grps = $db->select_from('map_prob_grp', 'gid',
		array($DBOP['='], 'pid', $pid));
	foreach ($grps as $grp)
		$row_grp[] = $grp['gid'];
	$row['grp'] = $row_grp;

	if (strlen($row['io']))
		$row['io'] = unserialize($row['io']);
	else $row['io'] = NULL;

	$row['cnt_submit'] = $row['cnt_ac'] + $row['cnt_unac'] + $row['cnt_ce'];

	if (isset($ct))
		$ct->view_prob($row);

	$row = filter_apply('before_prob_html', $row);

	$str = tf_get_prob_html($row);

	return filter_apply('after_prob_html', $str, $pid);
}

/**
 * @ignore
 */
function _prob_get_list_make_where($gid, $title_pattern)
{
	global $db, $DBOP;
	$where = NULL;
	if (!is_null($gid))
		$where = array($DBOP['in'], 'id', $db->select_from(
			'map_prob_grp', 'pid', 
			array($DBOP['in'], 'gid', $db->select_from(
				'cache_pgrp_child', 'chid', array(
					$DBOP['='], 'gid', $gid), array('chid' => 'ASC'), NULL, NULL,
					array('chid' => 'gid'), TRUE),
		), array('pid' => 'ASC'), NULL, NULL, array('pid' => 'id'), TRUE));

	if (!is_null($title_pattern) && strlen($title_pattern))
		db_where_add_and($where, array($DBOP['like'], 'title', $title_pattern));

	return $where;
}

/**
 * get the number of problems
 * @param int|NULL $gid problem group id
 * @return int
 */
function prob_get_amount($gid = NULL, $title_pattern = NULL)
{
	global $db, $DBOP;
	return $db->get_number_of_rows('problems',
		_prob_get_list_make_where($gid, $title_pattern));
}

$_cache_prob_title = array();
$_cache_prob_code = array();

/**
 * get problem list
 * @param array $fields the fields needed, which should be a subset of $PROB_VIEW_PINFO, and CAN NOT contain 'grp'
 * @param int|NULL $gid problem group id
 * @param NULL|array $order_by @see includes/db/dbal.php : function select_from
 * @param int|NULL $offset
 * @param int|NULL $cnt
 * @return array  Note: if some problems is not allowed to be viewd, the corresponding rows will be NULL 
 */
function prob_get_list($fields, $gid = NULL, $title_pattern = NULL, $order_by = NULL, $offset = NULL, $cnt = NULL)
{
	if (is_string($fields))
		$fields = array($fields);
	global $db, $DBOP, $user, $_cache_prob_title, $_cache_prob_code;
	$fields_added = array();
	if (!in_array('perm', $fields))
	{
		$fields[] = 'perm';
		$fields_added[] = 'perm';
	}
	if (!in_array('id', $fields))
	{
		$fields[] = 'id';
		$fields_added[] = 'id';
	}
	if (isset($fields['cnt_submit']))
	{
		$cnt_submit = TRUE;
		unset($fields['cnt_submit']);
		foreach (array('cnt_ac', 'cnt_unac', 'cnt_ce') as $f)
			if (!in_array($f, $fields))
			{
				$fields[] = $f;
				$fields_added[] = $f;
			}
	}
	$rows = $db->select_from('problems',
		$fields, 
		_prob_get_list_make_where($gid, $title_pattern),
		$order_by,
		$offset, $cnt
	);

	$is_super_submiter = FALSE;
	if (user_check_login())
	{
		$grp = $user->get_groups();
		$is_super_submiter = ($user->is_grp_member(GID_SUPER_SUBMITTER) ||
			$user->is_grp_member(GID_ADMIN_PROB));
	}
	else $grp = array(GID_GUEST);

	$io_set = in_array('io', $fields);
	$title_set = in_array('title', $fields);
	$code_set = in_array('code', $fields);

	foreach ($rows as $key => $row)
	{
		if (!$is_super_submiter)
		{
			if (prob_future_contest($row['id']))
				$rows[$key] = NULL;
			else
				if (!prob_check_perm($grp, $row['perm']))
					$rows[$key] = NULL;
		}

		if ($rows[$key] != NULL)
		{
			if ($io_set)
			{
				if (strlen($row['io']))
					$row['io'] = unserialize($row['io']);
				else $row['io'] = NULL;
			}
			if (isset($cnt_submit))
				$fields['cnt_submit'] = $fields['cnt_ac'] + $fields['cnt_unac'] + $fields['cnt_ce'];

			if ($title_set)
				$_cache_prob_title[$row['id']] = $row['title'];
			if ($code_set)
				$_cache_prob_code[$row['id']] = $row['code'];

			foreach ($fields_added as $f)
				unset($rows[$key][$f]);
		}
	}
	return $rows;
}


/**
 * get problem id by code
 * @param string $pcode problem code
 * @return int|NULL problem id or NULL if no such problem
 */
function prob_get_id_by_code($pcode)
{
	global $db, $DBOP;
	$row = $db->select_from('problems',
		'id', array($DBOP['=s'], 'code', $pcode));
	if (count($row) != 1)
		return NULL;
	return $row[0]['id'];
}

/**
 * @ignore
 */
function _prob_get_title_code_by_id($pid)
{
	global $db, $DBOP, $_cache_prob_title, $_cache_prob_code;
	if (array_key_exists($pid, $_cache_prob_title) && array_key_exists($pid, $_cache_prob_code))
		return;
	$row = $db->select_from('problems',
		array('title', 'code'),
		array($DBOP['='], 'id', $pid));
	if (count($row) != 1)
	{
		$_cache_prob_title[$pid] = NULL;
		$_cache_prob_code[$pid] = NULL;
	}
	else
	{
		$row = $row[0];
		$_cache_prob_title[$pid] = $row['title'];
		$_cache_prob_code[$pid] = $row['code'];
	}
}

/**
 * get problem title by id
 * @param int $pid problem id
 * @return string|NULL problem title or NULL if no such problem
 */
function prob_get_title_by_id($pid)
{
	global $_cache_prob_title;
	_prob_get_title_code_by_id($pid);
	return $_cache_prob_title[$pid];
}

/**
 * get problem code by id
 * @param int $pid problem id
 * @return string|NULL problem code or NULL if no such problem
 */
function prob_get_code_by_id($pid)
{
	global $_cache_prob_code;
	_prob_get_title_code_by_id($pid);
	return $_cache_prob_code[$pid];
}

/**
 * test whether a problem belongs to an upcoming contest
 * @param int $pid problem id
 * @return int|NULL the contest id the problem belongs to, or NULL
 */
function prob_future_contest($pid)
{
	static $cache = NULL;
	if (is_null($cache))
	{
		global $db, $DBOP;
		$rows = $db->select_from('map_prob_ct', array('pid', 'cid'),
			array($DBOP['in'], 'cid', $db->select_from('contests',
			'id', array($DBOP['>'], 'time_end', time()),
			NULL, NULL, NULL, array('id' => 'cid'), TRUE)));

		$cache = array();

		foreach ($rows as $row)
			$cache[intval($row['pid'])] = intval($row['cid']);
	}
	$pid = intval($pid);
	if (!isset($cache[$pid]))
		return NULL;
	return $cache[$pid];
}

/**
 * update cache, must be called exactly once after adding a problem group
 * @param int $gid id of newly added problem group
 * @return void
 */
function prob_update_grp_cache_add($gid)
{
	global $db, $DBOP;
	$pgid = $gid;
	while (TRUE)
	{
		$db->insert_into('cache_pgrp_child',
			array('gid' => $pgid, 'chid' => $gid));
		$pgid = $db->select_from('prob_grps', 'pgid',
			array($DBOP['='], 'id', $pgid));
		if (!count($pgid))
			return;
		$pgid = intval($pgid[0]['pgid']);
		if ($pgid == 0)
			return;
	}
}

/**
 * update cache, must be called after deleting a problem group
 * @param int $gid id of the deleted problem group
 * @return void
 */
function prob_update_grp_cache_delete($gid)
{
	global $db, $DBOP;
	$db->delete_item('cache_pgrp_child',
		array($DBOP['||'],
		$DBOP['='], 'gid', $gid,
		$DBOP['='], 'chid', $gid));
}


/**
 * get the status of a problem of a specific user
 * assume user is logined
 * @param int $pid problem id
 * @exception Exc_inner if user is not logined
 * @return int @see includes/const.php
 */
function prob_get_prob_user_status($pid)
{
	if (!user_check_login())
		throw new Exc_inner('User not logined: at prob_get_user_prob_status');
	global $db, $DBOP, $user;
	$status = $db->select_from('sts_prob_user', array('status'),
		array($DBOP['&&'], $DBOP['='], 'pid', $pid, $DBOP['='], 'uid', $user->id)
		);
	if (count($status) == 0)
		return STS_PROB_USER_UNTRIED;
	return $status[0]['status'];
}

/**
 * get problem group name by group id
 * @param int $gid group id
 * @return string|NULL the problem group name, or NULL if no such group
 */
function prob_grp_get_name_by_id($gid)
{
	global $db, $DBOP;
	$row = $db->select_from('prob_grps', 'name', array(
		$DBOP['='], 'id', $gid));
	if (count($row) != 1)
		return NULL;
	return $row[0]['name'];
}

