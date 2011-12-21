<?php
/* 
 * $File: src_download.php
 * $Date: Wed Dec 21 22:35:42 2011 +0800
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
require_once $includes_path . 'record.php';

if (!isset($_GET['rid']))
	die('no such record');

$where = array($DBOP['='], 'id', $_GET['rid']);
$row = $db->select_from('records', array('uid', 'cid', 'lid', 'pid', 'score'), $where);
	
if (count($row) != 1)
	die('no such record');
$row = $row[0];

if (!record_allow_view_src($row['uid'], $row['cid']))
	die('permission denied');

$src = record_get_src_by_rid($_GET['rid']);

header('Content-type: application/orzoj-user-src');
header(sprintf('Content-Disposition: attachment; filename="%s-%s-%d-%d.%s"',
	prob_get_code_by_id($row['pid']),
	user_get_username_by_id($row['uid']),
	$row['score'],
	$_GET['rid'],
	plang_get_type_by_id($row['lid'])));

echo $src;

