<?php
/* 
 * $File: judge.php
 * $Date: Sun Oct 03 20:15:03 2010 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj.

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

/**
 * judge info structure
 */
class Judge
{
	var $id, // int
		$name, // string
		$status, // see /includes/const.inc.php, consts with JUDGE_STATUS_ prefix
		$lang_sup, // array of name of supported languages
		$detail; // array of info like 'cpu', 'mem', defined in table options:'judge_info_list'
}

/**
 * get judges satisfying given conditions
 * @param NULL|int $id if not NULL, specifies the id of the wanted judge
 * @param NULL|bool $online only return online/offline judges if it is bool
 * @return array array of Judge
 */
function judge_get_list($id = NULL, $online = NULL)
{
	global $db, $DBOP;
	$where = NULL;
	if (is_bool($online))
		$where = array($DBOP['='], 'status', $online ? JUDGE_STATUS_ONLINE : JUDGE_STATUS_OFFLINE);
	if (is_int($id))
	{
		$tmp = array($DBOP['&&'], $DBOP['='], 'id', $id);
		if (is_array($where))
			$where = array_merge($tmp, $where);
		else $where = $tmp;
	}
	$judge_list = $db->select_from('judges', NULL, $where);

	$ret = array();
	foreach ($judge_list as $row)
	{
		$row['lang_sup'] = unserialize($row['lang_sup']);
		$row['detail'] = unserialize($row['detail']);
		$judge = new Judge();
		foreach((get_class_vars(get_class($judge)) as $key => $val)
			$judge->$key = $val;
		$ret[] = $judge;
	}
	return $ret;
}

/**
 * remove a judge
 * @param int $id judge id
 * @return void
 */
function judge_remove($id)
{
	global $db, $DBOP;
	$db->delete_item('judges', array($DBOP['='], 'id', $id));
}

