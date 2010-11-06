<?php
/* 
 * $File: const.php
 * $Date: Sat Nov 06 18:11:35 2010 +0800
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

define('DB_REAL_PRECISION', 10000);
// real numbers are multiplied by DB_REAL_PRECISION and 
// converted to integer to be inserted in the database

define('OPTION_KEY_LEN_MAX', 30);
define('PAGES_SLUG_LEN_MAX',200);
define('JUDGE_NAME_LEN_MAX', 20);
define('PROB_CODE_LEN_MAX', 25);
define('USERNAME_LEN_MAX', 20);
define('USERNAME_LEN_MIN', 3);
define('NICKNAME_LEN_MAX', 20);
define('REALNAME_LEN_MAX', 20);
define('USER_GRP_NAME_LEN_MAX', 20);
define('PROB_GRP_NAME_LEN_MAX', 20);
define('WLANG_NAME_LEN_MAX', 20);
define('POST_SUBJECT_LEN_MAX', 127);
define('POST_CONTENT_LEN_MAX', 10240); // 10kb
define('POST_CONTENT_FLOOR_LEN_MAX', 5120); // 10kb
define('MESSAGE_SUBJECT_LEN_MAX', 127);
define('TEAM_NAME_LEN_MAX', 50);

define('DEFAULT_THEME', 'default');
define('DEFAULT_WLANG_ID', 2);

define('USER_TID_NONE', 1);

define('JUDGE_STATUS_OFFLINE',0);
define('JUDGE_STATUS_ONLINE',1);

define('DYNAMIC_PASSWORD_LIFETIME', 10); // in seconds

define('STS_PROB_USER_UNTRIED', 0);
define('STS_PROB_USER_UNAC', 1);
define('STS_PROB_USER_AC', 2);
define('STS_PROB_USER_AC_BLINK', 3);

$cnt = 0;
define('WEBSERVER_OTHERS', $cnt ++);
define('WEBSERVER_APACHE', $cnt ++);
define('WEBSERVER_IIS7', $cnt ++);
define('WEBSERVER_IIS', $cnt ++); // Only for IIS < 7

$cnt = 0;
define('USER_BROWSER_OTHERS', $cnt ++);
define('USER_BROWSER_LYNX', $cnt ++);
define('USER_BROWSER_CHROME', $cnt ++);
define('USER_BROWSER_SAFARI', $cnt ++);
define('USER_BROWSER_GECKO', $cnt ++);
define('USER_BROWSER_MSIE', $cnt ++);
define('USER_BROWSER_OPERA', $cnt ++);
define('USER_BROWSER_NETSCAPE', $cnt ++);
define('USER_BROWSER_IPHONE', $cnt ++);
unset($cnt);

define('DEFAULT_THEME_ID', 1);
