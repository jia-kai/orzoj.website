<?php
/* 
 * $File: exe_status.php
 * $Date: Thu Oct 14 08:19:17 2010 +0800
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

$EXECUTION_STATUS_TEXT = array(
	EXESTS_RIGHT => __('Right'),
	EXESTS_PARTIALLY_RIGHT => __('Partially right'),
	EXESTS_WRONG_ANSWER => __('Wrong answer'),
	EXESTS_TLE => __('Time limit exceeded'),
	EXESTS_SIGKILL => __('Terminated by SIGKILL'),
	EXESTS_SIGSEGV => __('Illegal access to memeory'),
	EXESTS_SIGNAL => __('Terminated by signal'),
	EXESTS_ILLEGAL_CALL => __('Illegal system call'),
	EXESTS_EXIT_NONZERO => __('Non-zero exit code'),
	EXESTS_SYSTEM_ERROR => __('System error'),
);

class Case_result
{
	var $exe_status, $score, $time, $memory, $extra_info;
}
