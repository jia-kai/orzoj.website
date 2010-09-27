<?php
/* 
 * $File: sched.php
 * $Date: Mon Sep 27 01:18:46 2010 +0800
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
if (!defined('IN_ORZOJ')) exit;

$table_jobs = $table_prefix . 'jobs';

/**
 * add a scheduled task
 * @param int $time task executing time (seconds since the Epoch)
 * @param string|mixed $func 
 * @param array $args
 * @return int|bool id or FALSE if failed
 * @see instert_into
 */
function sched_add($time, $func, $args)
{
	global $db, $table_jobs;
	$value_array = array(
		'time' => $time,
		'func' => $func,
		'args' => $args
		);
	return $db->insert_into($table_jobs, $value_array);
}

/**
 * remove a scheduled task
 * @param int $id 
 * @return bool TRUE if succeed, otherwise FALSE
 */
function sched_remove($id)
{
	global $db, $table_jobs;
	$where_clause = array(
		$DBOP['='], 'id', $id
		);
	return $db->delete_item($table_jobs, $where_clause);
}

/**
 * update a scheduled task
 * @param int $id
 * @param int $time
 * @return bool TRUE if succeed, otherwise FALSE
 */
function sched_update($id, $time)
{
	global $db, $table_jobs;
	$value = array(
		'time' => $time
	);
	$where_clause = array(
		$DBOP['='], 'id', $id
	);
	return $db->update_data($table_jobs, $value, $where_clause);
}

/**
 * 
 * find and execute jobs that should be executed now 
 * @return void
 */

/*
 * XXX
 * test functions
function a_plus_b($a, $b)
{
	echo $a + $b . '<br />';
}
$db->insert_into($table_jobs, array('id' => 1, 'time' => 123, 'func' => 'a_plus_b', 'args' => serialize(array(rand(), rand()))));
 */

function sched_work()
{
	global $db, $table_jobs, $DBOP;
	$where_clause = array(
		$DBOP['<='], 'time', time()
		);
	$ret = $db->select_from($table_jobs, NULL, $where_clause);
	if ($ret === FALSE)
		die(__(__FILE__ . ' : sched_work() : select from database failed.'));
	foreach ($ret as $key => $value)
	{
		$func = $value['func'];
		$args = unserialize($value['args']);
		$ret = call_user_func_array($func, $args);
		// TODO
		// write log file or write log in database? or do not write log?
		if ($ret === NULL) // function calling failed
		{
			// XXX
		}
	}
}

