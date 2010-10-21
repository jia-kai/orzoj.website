<?php
/* 
 * $File: sched.php
 * $Date: Thu Oct 21 18:16:32 2010 +0800
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


/**
 * add a scheduled job
 * @param int $time job executing time (seconds since the Epoch)
 * @param string $file which file $func is (it's usually __FILE__) must be in orzoj-website direcotry
 * @param callback $func the function to be called
 * @param array $args
 * @return int job id
 */
function sched_add($time, $file, $func, $args)
{
	global $db, $root_path;
	$file = substr(realpath($file), strlen($root_path));
	$value_array = array(
		'time' => $time,
		'file' => $file,
		'func' => $func,
		'args' => serialize($args)
		);
	return $db->insert_into('scheds', $value_array);
}

/**
 * remove a scheduled job
 * @param int $id 
 * @return void
 */
function sched_remove($id)
{
	global $db, $DBOP;
	$where_clause = array(
		$DBOP['='], 'id', $id
		);
	$db->delete_item('scheds', $where_clause);
}

/**
 * modify the time of a scheduled job
 * @param int $id
 * @param int $time
 */
function sched_update($id, $time)
{
	global $db, $DBOP;
	$value = array(
		'time' => $time
	);
	$where_clause = array(
		$DBOP['='], 'id', $id
	);
	$db->update_data('scheds', $value, $where_clause);
}

// for sched_work, see orz.php

