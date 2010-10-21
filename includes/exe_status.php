<?php
/* 
 * $File: exe_status.php
 * $Date: Wed Oct 20 10:50:27 2010 +0800
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

/* these are status for a single test case */
define('EXESTS_RIGHT', 0);
define('EXESTS_PARTIALLY_RIGHT', 1);
define('EXESTS_WRONG_ANSWER', 2);
define('EXESTS_TLE', 3);
define('EXESTS_SIGKILL', 4);
define('EXESTS_SIGSEGV', 5);
define('EXESTS_SIGNAL', 6);
define('EXESTS_ILLEGAL_CALL', 7);
define('EXESTS_EXIT_NONZERO', 8);
define('EXESTS_SYSTEM_ERROR', 9);

/**
 * get all execution status in an array(<status number> => <description>)
 * @return string
 */
function &exests_get_all()
{
	static $TEXT = NULL;
	if (is_null($TEXT))
	{
		$TEXT = array(
			EXESTS_RIGHT => __('Right'),
			EXESTS_PARTIALLY_RIGHT => __('Partially right'),
			EXESTS_WRONG_ANSWER => __('Wrong answer'),
			EXESTS_TLE => __('Time limit exceeded'),
			EXESTS_SIGKILL => __('Terminated by SIGKILL'),
			EXESTS_SIGSEGV => __('Illegal access to memeory'),
			EXESTS_SIGNAL => __('Terminated by signal'),
			EXESTS_ILLEGAL_CALL => __('Illegal system call'),
			EXESTS_EXIT_NONZERO => __('Non-zero exit code'),
			EXESTS_SYSTEM_ERROR => __('System error')
		);
	}
	return $TEXT;
}

/**
 * convert execution status to human readable text
 * @param int $status execution status
 * @return string
 */
function exests_get_str($status)
{
	$tmp = &exests_get_all();
	return $tmp[intval($status)];
}

class Case_result
{
	var $exe_status, $score, $full_score, $time, $memory, $extra_info;
}

$case_result_vars = array_keys(get_class_vars('Case_result'));
sort($case_result_vars);

/**
 * encode an array of Case_result into a string
 * @param array $data
 * @return string
 */
function case_result_array_encode($data)
{
	global $case_result_vars;
	$ret = array();
	foreach ($data as $d)
	{
		$tmp = array();
		foreach ($case_result_vars as $v)
			$tmp[] = $d->$v;
		$ret[] = $tmp;
	}
	return gzcompress(json_encode($ret));
}

/**
 * decode a string into an array of Case_result
 * @param string $str
 * @return array
 */
function case_result_array_decode($str)
{
	global $case_result_vars;
	$ret = array();
	$cnt = count($case_result_vars);
	$str = gzuncompress($str);
	foreach (json_decode($str) as $d)
	{
		$tmp = new Case_result();
		for ($idx = 0; $idx < $cnt; $idx ++)
		{
			$f = $case_result_vars[$idx];
			$tmp->$f = $d[$idx];
		}
		$ret[] = $tmp;
	}
	return $ret;
}

