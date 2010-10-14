<?php
/* 
 * $File: record.php
 * $Date: Thu Oct 14 08:06:24 2010 +0800
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


$cnt = 0;

define('RECORD_STATUS_WAITING_TO_BE_FETCHED', $cnt ++);
define('RECORD_STATUS_WAITING_FOR_CONTEST', $cnt ++);
define('RECORD_STATUS_WAITING_ON_SERVER', $cnt ++);

define('RECORD_STATUS_JUDGE_BUSY', $cnt ++);

define('RECORD_STATUS_COMPILING', $cnt ++);
define('RECORD_STATUS_COMPILE_SUCCESS', $cnt ++);
define('RECORD_STATUS_RUNNING', $cnt ++);

define('RECORD_STATUS_COMPILE_FAILURE', $cnt ++);

define('RECORD_STATUS_ACCEPTED',$cnt ++ );
define('RECORD_STATUS_WRONG_ANSWER', $cnt ++);
define('RECORD_STATUS_TIME_LIMIT_EXCEED', $cnt ++);
define('RECORD_STATUS_MEMORY_LIMIT_EXCEED', $cnt ++);
define('RECORD_STATUS_RUNTIME_ERROR', $cnt ++);
define('RECORD_STATUS_DATA_NOT_FOUND', $cnt ++);
define('RECORD_STATUS_ERROR', $cnt ++);
unset($cnt);

/**
 * test whether the source is executed
 * @param int $status
 * @return bool
 */
function record_status_executed($status)
{
	return $status >= RECORD_STATUS_ACCEPTED && $status <= RECORD_STATUS_RUNTIME_ERROR;
}

/**
 * test whether the judge process is finished
 * @param int $status
 * return bool
 */
function record_status_finished($status)
{
	return intval($status) > RECORD_STATUS_COMPILE_SUCCESS;
}


/**
 * translate record status to human readable text
 */
$RECORD_STATUS_TEXT = array(
	RECORD_STATUS_WAITING_TO_BE_FETCHED => __('Waiting to be fetched'),
	RECORD_STATUS_WAITING_FOR_CONTEST => __('Waiting for contest'),
	RECORD_STATUS_WAITING_ON_SERVER => __('Waiting on orzoj-server'),

	RECORD_STATUS_COMPILING => __('Compiling'),
	RECORD_STATUS_COMPILE_SUCCESS => __('Succesfully compiled'),
	RECORD_STATUS_COMPILE_FAILURE => __('Compilation error'),
	RECORD_STATUS_RUNNING => __('Running'),
	RECORD_STATUS_ACCEPTED => __('Accepted'),
	RECORD_STATUS_WRONG_ANSWER => __('Wrong answer'),
	RECORD_STATUS_TIME_LIMIT_EXCEED => __('Time limit exceeded'),
	RECORD_STATUS_MEMORY_LIMIT_EXCEED => __('Memory limit exceeded'),
	RECORD_STATUS_RUNTIME_ERROR => __('Runtime error'),
	RECORD_STATUS_DATA_NOT_FOUND => __('Data not found'),
	RECORD_STATUS_JUDGE_BUSY => __('Judge is busy'),
	RECORD_STATUS_ERROR => __('Error'),
);

