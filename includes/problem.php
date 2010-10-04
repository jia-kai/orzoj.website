<?php
/* 
 * $File: problem.php
 * $Date: Mon Oct 04 21:43:11 2010 +0800
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
$PROB_VIEW_PINFO = array('id', 'title', 'code', 'desc', 'perm', 'io');

/**
 * check whether a user has permission for a problem
 * @param array $user_grp the ids of groups that the user belongs to
 * @param array|string $perm problem permission setting, see /install/tables.php
 * @return bool whether the user is permitted
 */
function prob_check_perm($user_grp, $perm)
{
	if (!isset($user->groups))
		$user->set_val($user->id);
	if (!is_array($perm))
		$perm = unserialize($perm);
	if ($perm[0])
		$order = array(2, 3);
	else $order = array(3, 2);
	$match = NULL;
	foreach ($order as $o)
		if (count(array_intersect($user->groups, $perm[$o])))
			$match = $o;
	if (is_null($match))
		return $perm[1] != 0;
	return $match == 2;
}

/**
 * view a problem
 * @param int $pid problem id
 * @return string|NULL the HTML code, or NULL if the user is not allowed to view this problem
 */
function prob_view($pid)
{
	global $db, $DBOP;
	$row = $db->select_from('problems', $PROB_VIEW_PINFO,
		array($DBOP['='], 'id', $pid));
	if (count($row) != 1)
		throw new Exc_runtime(__('No such problem #%d', $pid));
	$row = $row[0];
	if (user_check_login())
		$grp = $user->groups;
	else $grp = array(GID_GUEST);
	if (!prob_check_perm($grp, $row['perm']))
		throw new Exc_runtime(__('Permission denied for this problem'));

	$ct = ctal_get_class($pid);
	if ($ct)
		$ct->prob_view($grp, $row);

	$str = tf_get_prob_html($row);

	return filter_apply('after_prob_html', $str, $pid);
}

