<?php
/* 
 * $File: src_download.php
 * $Date: Mon Oct 18 15:45:49 2010 +0800
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

require_once 'pre_include.php';
require_once $includes_path . 'user.php';

if (!isset($_GET['rid']))
	die('no such record');

$where = array($DBOP['='], 'id', $_GET['rid']);
$row = $db->select_from('records', array('uid', 'lid'), $where);
	
if (count($row) != 1)
	die('no such record');
$row = $row[0];

if (!user_check_view_src_perm($row['uid']))
	die('permission denied');

$where[1] = 'rid';
$src = $db->select_from('sources', 'src', $where);
// TODO: retrieve source from orzoj-server
if (count($src) != 1)
	die('source unavailable');

$src = $src[0]['src'];

header('Content-type: application/orzoj-user-src');
header(sprintf('Content-Disposition: attachment; filename="usersrc-%d.%s"',
	$_GET['rid'], plang_get_type_by_id($row['lid'])));

echo $src;

