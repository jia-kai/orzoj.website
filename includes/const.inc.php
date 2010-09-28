<?php
/* 
 * $File: const.inc.php
 * $Date: Tue Sep 28 16:35:44 2010 +0800
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
define('PROB_SLUG_LEN_MAX', 100);

define('GID_ADMIN', 1); // admin group id
define('GID_LOCK', 2); // locked group id
define('GID_ALL', 3); // every should be in this group
define('GID_NONE', 4); // nobody should be in this group

define('JUDGE_STATUS_OFFLINE',0);
define('JUDGE_STATUS_ONLINE',1);
define('JUDGE_STATUS_BUSY',2);

