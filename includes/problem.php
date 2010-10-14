<?php
/* 
 * $File: problem.php
 * $Date: Thu Oct 14 19:27:55 2010 +0800
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
	'cnt_submit', 'cnt_ac', 'cnt_unac', 'cnt_ce', 'grp');
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
		$grp = $user->groups;
		$has_perm = $user->is_grp_member(GID_SUPER_SUBMITTER);
	}
	else
	{
		$grp = array(GID_GUEST);
		$has_perm = FALSE;
	}

	if (!$has_perm && !prob_check_perm($grp, $row['perm']))
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

	if (!$has_perm)
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
 * FIXME: This function has somthing wrong
 */
function _prob_get_list_make_where($gid)
{
	return NULL;
	global $db, $DBOP;
	$ret0 = NULL;
	if (!is_null($gid))
		$ret0 = array($DBOP['in'], 'id', $db->select_from(
			'map_prob_grp', 'pid', 
			array($DBOP['in'], 'gid', $db->select_from(
				'cache_pgrp_child', 'chid', array(
					$DBOP['='], 'gid', $gid), array('chid' => 'ASC'), NULL, NULL,
					array('chid' => 'gid'), TRUE),
			), array('pid' => 'ASC'), NULL, NULL, array('pid' => 'id'), TRUE));
	$ret1 = NULL;
	$now = time();
	if (!user_check_login() || !$user->is_grp_member(GID_SUPER_SUBMITTER))
		$ret1 = array($DBOP['!'], $DBOP['in'], 'id', $db->select_from(
			'map_prob_ct', 'pid', array($DBOP['&&'],
				$DBOP['<='], 'time_start', $now,
				$DBOP['>'], 'time_end', $now), array('pid' => 'ASC'), NULL, NULL,
			array('pid' => 'id'), TRUE));

	if (is_null($ret0))
		return $ret1;
	if (is_null($ret1))
		return $ret0;
	return array_merge(array($DBOP['&&']), $ret0, $ret1);
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
 * @param bool $time_asc order by time ASC(TRUE) or DESC(FALSE)
 * @param int|NULL $offset
 * @param int|NULL $cnt
 * @return array
 */
function prob_get_list($fields, $gid = NULL, $time_asc = TRUE, $offset = NULL, $cnt = NULL)
{
	global $db, $DBOP, $user;
	$perm_added = FALSE;
	if (!in_array('perm', $fields))
	{
		$fields[] = 'perm';
		$perm_added = TRUE;
	}
	$rows = $db->select_from('problems',
		$fields, _prob_get_list_make_where($gid),
		array('time' => $time_asc ? 'ASC' : 'DESC'));
	$ret = array();
	if (user_check_login())
		$grp = $user->groups;
	else $grp = array(GID_GUEST);

	$io_set = isset($fields['io']);

	foreach ($rows as $row)
		if (prob_check_perm($grp, $row['perm']))
		{
			if ($io_set)
			{
				if (strlen($row['io']))
					$row['io'] = unserialize($row['io']);
				else $row['io'] = NULL;
			}
			if ($perm_added)
				unset($row['perm']);
			$ret[] = $row;
		}
	return $ret;
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

