<?php
/* 
 * $File: judge.php
 * $Date: Fri Oct 29 21:29:18 2010 +0800
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
		$status, // see /includes/const.php, consts with JUDGE_STATUS_ prefix
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
		db_where_add_and($where,array($DBOP['='],'id',$id));
	$judge_list = $db->select_from('judges', NULL, $where);

	$ret = array();
	foreach ($judge_list as $row)
	{
		$row['lang_sup'] = unserialize($row['lang_sup']);
		$row['detail'] = unserialize($row['detail']);
		$judge = new Judge();
		foreach(get_class_vars(get_class($judge)) as $key => $val)
		{
			$judge->$key = $row[$key];
		}
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

/**
 * get judge name by id
 * @param int $jid judge id
 * @return string|NULL judge name or NULL if no such judge
 */
function judge_get_name_by_id($jid)
{
	if (!$jid)
		return NULL;
	static $cache = array();
	if (array_key_exists($jid, $cache))
		return $cache[$jid];
	global $db, $DBOP;
	$row = $db->select_from('judges', 'name', array(
		$DBOP['='], 'id', $jid));
	if (count($row) != 1)
		return $cache[$jid] = NULL;
	return $cache[$jid] = $row[0]['name'];
}

/**
 * get judge id by name
 * @param string $name judge name
 * @return int|NULL judge id, or NULL if no such judge
 */
function judge_get_id_by_name($name)
{
	global $db, $DBOP;
	$row = $db->select_from('judges', 'id', array($DBOP['=s'], 'name', $name));
	if (count($row) != 1)
		return NULL;
	return $row[0]['id'];
}

