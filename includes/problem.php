<?php
/* 
 * $File: problem.php
 * $Date: Mon Oct 18 23:08:02 2010 +0800
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
	'cnt_submit', 'cnt_ac', 'cnt_unac', 'cnt_ce', 'difficulty', 'grp');
// desc: exlained in simple-doc.txt or install/tables.php
// grp: array of problem group ids that this problem belongs to
// io: array of input/output file name, or NULL if using stdio

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

	if (!$is_super_submitter && !prob_check_perm($grp, $row['perm']))
		throw new Exc_runtime(__('Your are not permitted to view this problem'));

	$row_grp = array();
	$grps = $db->select_from('map_prob_grp', 'gid',
		array($DBOP['='], 'pid', $pid));
	foreach ($grps as $grp)
		$row_grp[] = $grp['gid'];
	$row['grp'] = $row_grp;

	if (strlen($row['io']))
		$row['io'] = unserialize($row['io']);
	else $row['io'] = NULL;

	if (!$is_super_submitter)
	{
		$ct = ctal_get_class($pid);
		if ($ct)
			$ct->prob_view($grp, $row);
	}

	$row = filter_apply('before_prob_html', $row);

	$str = tf_get_prob_html($row);

	return filter_apply('after_prob_html', $str, $pid);
}

/**
 * @ignore
 */
function _prob_get_list_make_where($gid)
{
	global $db, $DBOP;
	if (is_null($gid))
		return NULL;
	return array($DBOP['in'], 'id', $db->select_from(
		'map_prob_grp', 'pid', 
		array($DBOP['in'], 'gid', $db->select_from(
			'cache_pgrp_child', 'chid', array(
				$DBOP['='], 'gid', $gid), array('chid' => 'ASC'), NULL, NULL,
				array('chid' => 'gid'), TRUE),
			), array('pid' => 'ASC'), NULL, NULL, array('pid' => 'id'), TRUE));
}

/**
 * get the number of problems
 * @param int|NULL $gid problem group id
 * @return int
 */
function prob_get_amount($gid = NULL)
{
	global $db, $DBOP;
	return $db->get_number_of_rows('problems',
		_prob_get_list_make_where($gid));
}

/**
 * get problem list
 * @param array $fields the fields needed, which should be a subset of $PROB_VIEW_PINFO, and CAN NOT contain 'grp'
 * @param int|NULL $gid problem group id
 * @param bool $id_asc order by id ASC(TRUE) or DESC(FALSE)
 * @param int|NULL $offset
 * @param int|NULL $cnt
 * @return array  Note: if some problems is not allowed to be viewd, the corresponding rows will be NULL 
 */
function prob_get_list($fields, $gid = NULL, $id_asc = TRUE, $offset = NULL, $cnt = NULL)
{
	global $db, $DBOP, $user;
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
	$rows = $db->select_from('problems',
		$fields, _prob_get_list_make_where($gid),
		array('id' => $id_asc ? 'ASC' : 'DESC'),
		$offset, $cnt
	);

	$is_super_submiter = FALSE;
	if (user_check_login())
	{
		$grp = $user->get_groups();
		$is_super_submiter = $user->is_grp_member(GID_SUPER_SUBMITTER);
	}
	else $grp = array(GID_GUEST);

	$io_set = isset($fields['io']);

	foreach ($rows as $key => $row)
	{
		if (!$is_super_submiter)
		{
			if (prob_future_contest($row['id']))
			{
				$ct = ctal_get_class($row['id']);
				if (!$ct->view_in_list($grp))
					$rows[$key] = NULL;
			}
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
	static $cache = array();
	if (array_key_exists($pid, $cache))
		return $cache[$pid];
	global $db, $DBOP;
	$row = $db->select_from('problems',
		array('title', 'code'),
		array($DBOP['='], 'id', $pid));
	if (count($row) != 1)
		return $cache[$pid] = NULL;
	return $cache[$pid] = $row[0];
}

/**
 * get problem title by id
 * @param int $pid problem id
 * @return string|NULL problem title or NULL if no such problem
 */
function prob_get_title_by_id($pid)
{
	$t = _prob_get_title_code_by_id($pid);
	if ($t == NULL)
		return NULL;
	return $t['title'];
}

/**
 * get problem code by id
 * @param int $pid problem id
 * @return string|NULL problem code or NULL if no such problem
 */
function prob_get_code_by_id($pid)
{
	$t = _prob_get_title_code_by_id($pid);
	if ($t == NULL)
		return NULL;
	return $t['code'];
}

/**
 * test whether a problem belongs to an upcoming contest
 * @param int $pid problem id
 * @return bool
 */
function prob_future_contest($pid)
{
	static $cache = NULL;
	if (is_null($cache))
	{
		global $db, $DBOP;
		$rows = $db->select_from('map_prob_ct', 'pid',
			array($DBOP['in'], 'cid', $db->select_from('contests',
			'id', array($DBOP['>='], 'time_start', time()),
			NULL, NULL, NULL, array('id' => 'cid'), TRUE)));

		$cache = array();

		foreach ($rows as $row)
			$cache[$row['pid']] = TRUE;
	}
	return isset($cache[$pid]);
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

