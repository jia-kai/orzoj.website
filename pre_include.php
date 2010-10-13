<?php
/* 
 * $File: pre_include.php
 * $Date: Wed Oct 13 17:26:57 2010 +0800
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
date_default_timezone_set('GMT');
error_reporting(E_ALL);
define('IN_ORZOJ', true);

$root_path = rtrim(realpath(dirname(__FILE__)), '/') . '/';
$includes_path = $root_path . 'includes/';

$ORZOJ_VERSION = '0.0.1-alpha';

require_once $root_path . 'config.php';
require_once $includes_path . 'const.php';
require_once $includes_path . 'l10n.php';
if (!defined('IS_INSTALLED'))
	die(__('You must install first.<br />Please run %sinstall.', $root_path));
require_once $includes_path . 'functions.php';
require_once $includes_path . 'exception.php';
require_once $includes_path . 'user.php';

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


/*
 * Detect web server.
 * Copied from wordpress.
 */
if (isset($_SERVER['SERVER_SOFTWARE']))
{
	$is_Apache = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);
	$is_IIS = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false);
	$is_IIS7 = $is_iis7 = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/7.') !== false);

	if ($is_Apache) $webserver = WEBSERVER_APACHE;
	else if ($is_IIS7) $webserver = WEBSERVER_IIS7;
	else if ($is_IIS) $webserver = WEBSERVER_IIS;
	else $webserver = WEBSERVER_OTHERS;

	unset($is_Apache,$is_IIS,$is_IIS7);
}

/*
 * Detect UA Browser.
 * Copied from wordpress.
 */
$userbrowser = USER_BROWSER_OTHERS;

if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx') !== false ) {
		$userbrowser = USER_BROWSER_LYNX;
	} elseif ( stripos($_SERVER['HTTP_USER_AGENT'], 'chrome') !== false ) {
		$userbrowser = USER_BROWSER_CHROME;
	} elseif ( stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false ) {
		$userbrowser = USER_BROWSER_SAFARI;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false ) {
		$userbrowser = USER_BROWSER_GECKO;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false ) {
		$userbrowser = USER_BROWSER_MSIE;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false ) {
		$userbrowser = USER_BROWSER_OPERA;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Nav') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4.') !== false ) {
		$userbrowser = USER_BROWSER_NETSCAPE;
	}
}

if ( $userbrowser == USER_BROWSER_SAFARI && stripos($_SERVER['HTTP_USER_AGENT'], 'mobile') !== false )
	$is_iphone = USER_BROWSER_IPHONE;

/*
 * Detect UA Browser finished.
 */
