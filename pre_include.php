<?php
/* 
 * $File: pre_include.php
 * $Date: Wed Sep 29 11:25:51 2010 +0800
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

ob_start();
date_default_timezone_set('GMT');
error_reporting(E_ALL);
define('IN_ORZOJ', true);

$root_path = dirname(__FILE__) . '/';
$includes_path = $root_path . 'includes/';

require_once $root_path . 'config.php';
require_once $includes_path . 'l10n.php';
require_once $includes_path . 'functions.php';
require_once $includes_path . 'exception.php';

db_init();

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


