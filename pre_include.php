<?php
/* 
 * $File: pre_include.php
 * $Date: Fri Jan 06 15:44:59 2012 +0800
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
if (!defined('IN_ORZOJ'))
	define('IN_ORZOJ', TRUE);

define('ORZOJ_VERSION', '0.0.1-alpha');
define('ORZOJ_OFFICIAL_WEBSITE', 'http://code.google.com/p/orzoj');
define('ORZOJ_BUG_REPORT_ADDR', 'http://code.google.com/p/orzoj/issues');

// XXX: debug mode on
define('ORZOJ_DEBUG_MODE', TRUE);

$root_path = rtrim(realpath(dirname(__FILE__)), '/') . '/';
$includes_path = $root_path . 'includes/';

require_once $includes_path . 'const.php';
require_once $includes_path . 'exception.php';

$config_file_path = $root_path . 'config.php';

require_once $includes_path . 'l10n.php';

if (!defined('IN_INSTALLATION'))
{
	if (!file_exists($config_file_path))
	{
		echo '<div style="text-align: center; font-size: 40px">';
		echo __('File `%s` does not exists.', $config_file_path) . '<br />';
		echo __('Please install first.');
		$dir = dirname(__FILE__);
		$doc_root = $_SERVER['DOCUMENT_ROOT'];
		$root_dir = str_replace($doc_root,'',$dir);
		echo sprintf('<a href="%s">',$root_dir . '/install/'),__('Click here to install') . '</a>';
		echo '</div>';
		die;
	}
	require_once $config_file_path;
	require_once $includes_path . 'functions.php';
	try
	{
		db_init();
	}
	catch (Exc_db $e)
	{
		die(__('failed to connect to database'));
	}
}
else
	require_once $includes_path . 'functions.php';

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

