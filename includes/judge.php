<?php
/* 
 * $File: judge.php
 * $Date: Wed Sep 29 11:51:59 2010 +0800
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
 * Judge info class
 */
class Judge_info
{
	var $id, // int
		$name, // string
		$status, // see /includes/const.inc.php, consts with JUDGE_STATUS_ prefix
		$lang_sup, // array of id of supperted languages
		$detail; // array of info like 'cpu', 'mem', defined in table options:'judge_info_list'
}

/**
 * transform query answer of judges from database to a array of Judge_info
 * @param array $judge_list query answer from table : judges
 * @return array array of Judge_info
 */
function array2judge_info($judge_list)
{
	$ret = array();
	foreach ($judge_list as $key => $value)
	{
		$judge = new Judge_info();
		$value = $value['value'];
		$value = unserialize($value);
		foreach (get_class_vars(get_class($judge)) as $var => $val)
			$judge->$var = $value[$var];
		$ret[] = $judge;
	}
	return $ret;
}
/**
 * get all judges running on orzoj
 * @global $db
 * @return array of Judge_info
 */
function get_judge_list()
{
	global $db;
	$judge_list = $db->select_from('judges');
	return array2judge_info($judge_list);
}

/**
 * get online judges running on orzoj
 * @global $db
 * @return array of online judges, structure see install/tables.php
 */
function get_online_judge()
{
	global $db;
	$where_clause = array('status' => JUDGE_STATUS_ONLINE);
	return array2judge_info($db->select_from('judges', NULL, $where_clause));
}

/**
 * get offline judges running on orzoj
 * @global $db
 * @return array of online judges, structure see install/tables.php
 */
function get_offline_judge()
{
	global $db;
	$where_clause = array('status' => JUDGE_STATUS_OFFLINE);
	return array2judge_info($db->select_from('judges', NULL, $where_clause));
}
