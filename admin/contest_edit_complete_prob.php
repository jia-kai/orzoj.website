<?php
/*
 * $File: contest_edit_complete_prob.php
 * $Date: Sat Jan 07 12:43:13 2012 +0800
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

require_once '../pre_include.php';
require_once $includes_path . 'user.php';
if (!user_check_login() || !$user->is_grp_member(GID_ADMIN_CONTEST))
	die('what are you doing?');

define('LIMIT_MAX', 20);

$pattern = '%' . $_GET['q'] . '%';
$rows = $db->select_from('problems', array('code', 'title'), array($DBOP['||'],
	$DBOP['like'], 'title', $pattern,
	$DBOP['like'], 'code', $pattern), NULL, NULL, min(LIMIT_MAX, intval($_GET['limit'])));

foreach ($rows as &$row)
	echo "$row[code]|$row[title]\n";

