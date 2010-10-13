<?php
/*
 * $File: prob_group_tree.php
 * $Date: Wed Oct 13 21:32:07 2010 +0800
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

//require_once("../../../../pre_include.php");
if (!defined('IN_ORZOJ'))
	exit;
$ret = array();
if (!isset($_GET['prob_grp_id']))
	throw new Exc_inner('`prob_grp_id in $_GET does not set.`');
$pgid = $_GET['prob_grp_id'];
$grps = $db->select_from('prob_grps', array('id', 'name'),
	array($DBOP['='], 'pgid', $pgid));
foreach ($grps as $grp)
{
	$id = $grp['id'];
	$name = $grp['name'];
	$grp = array("data" => $name, "attr" => array("id" => $id));
	$nchild = $db->get_number_of_rows('prob_grps',
		array($DBOP['='], 'pgid', $id));
	if ($nchild)
		$grp["state"] = "closed";
	$ret[] = $grp;
}

echo json_encode($ret);

/*
[
	{ "data" : "A node", "attr" : {"id" : 1} ,"children" : [ { "data" : "Only child", "state" : "closed", "attr" : {"id" : 2}} ], "state" : "open" },
	{ "data" : "Node ?", "attr" : {"id" : 3}}
]
 */
