<?php
/* 
 * $File: pre_include.php
 * $Date: Thu Nov 04 19:40:06 2010 +0800
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
$PAGE_START_TIME = microtime(TRUE);
ob_start();
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL);
define('IN_ORZOJ', TRUE);

define('ORZOJ_VERSION', '0.0.1-alpha');
define('ORZOJ_OFFICIAL_WEBSITE', 'http://code.google.com/p/orzoj');
define('ORZOJ_BUG_REPORT_ADDR', 'http://code.google.com/p/orzoj/issues');

// XXX: debug mode on
define('ORZOJ_DEBUG_MODE', TRUE);

$root_path = rtrim(realpath(dirname(__FILE__)), '/') . '/';
$includes_path = $root_path . 'includes/';

if (defined('CONFIG_FILE_PATH'))
	require_once CONFIG_FILE_PATH;
else
	require_once $root_path . 'config.php';
require_once $includes_path . 'const.php';
require_once $includes_path . 'exception.php';
require_once $includes_path . 'functions.php';
require_once $includes_path . 'l10n.php';

// FIXME: this should be determined by where the config file exsists
if (!defined('IS_INSTALLED'))
	die(__('You must install first.<br />Please run %sinstall.', $root_path));

try
{
	db_init();
}
catch (Exc_db $e)
{
	die(__('failed to connect to database'));
}

if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
{
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);

	while (list($key, $val) = each($process))
	{
		foreach ($val as $k => $v) {
			unset($process[$key][$k]);
			if (is_array($v)) {
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}


