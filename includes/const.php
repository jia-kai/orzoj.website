<?php
/* 
 * $File: const.php
 * $Date: Sun Oct 17 15:54:51 2010 +0800
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

define('OPTION_KEY_LEN_MAX', 30);
define('JUDGE_NAME_LEN_MAX', 20);
define('PROB_CODE_LEN_MAX', 25);
define('USERNAME_LEN_MAX', 20);
define('USERNAME_LEN_MIN', 3);
define('WLANG_NAME_LEN_MAX', 20);
define('POST_SUBJECT_LEN_MAX', 127);
define('MESSAGE_SUBJECT_LEN_MAX', 127);
define('TEAM_NAME_LEN_MAX', 50);

define('DEFAULT_THEME_ID', 1);
define('DEFAULT_WLANG_ID',2);

$cnt = 1;
define('GID_ADMIN_USER', $cnt ++); // manage users (lock a user, change password, etc)
define('GID_ADMIN_GROUP', $cnt ++);  // manage user groups (add, remove groups and assign group administrators)
define('GID_ADMIN_TEAM', $cnt ++); // manage user teams
define('GID_ADMIN_PROB', $cnt ++); // manage problems
define('GID_ADMIN_CONTEST', $cnt ++);  // manage contests
define('GID_ADMIN_POST', $cnt ++); // manage posts
define('GID_SUPER_SUBMITTER', $cnt ++);
// submit regardless of which contest the problem belongs to
// or other limits on problem submission
define('GID_SUPER_RECORD_VIEWER', $cnt ++);
// view all records and sources
define('GID_UINFO_VIEWER', $cnt ++); // view view register IP, submission IP, user real name etc.
define('GID_LOCK', $cnt ++); // locked group id
define('GID_ALL', $cnt ++); // every registered user should be in this group
define('GID_NONE', $cnt ++); // nobody should be in this group
define('GID_GUEST', $cnt ++);

define('JUDGE_STATUS_OFFLINE',0);
define('JUDGE_STATUS_ONLINE',1);

define('DYNAMIC_PASSWORD_LIFETIME', 10); // in seconds

define('ST_PROB_USER_UNAC', 0);
define('ST_PROB_USER_AC', 1);

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
