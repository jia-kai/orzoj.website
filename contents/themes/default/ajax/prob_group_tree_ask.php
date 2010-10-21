<?php
/*
 * $File: prob_group_tree_ask.php
 * $Date: Thu Oct 21 22:57:09 2010 +0800
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

/*
 * POST: 
 *	'prob_grp_id': int
 *		 returns all child group of a specific group in a organized way
 */

require_once $theme_path . 'prob_func.php';

$ret = array();
if (!isset($_POST['prob_grp_id']))
	die('`prob_grp_id in $_POST does not set.`');
$pgid = $_POST['prob_grp_id'];
$first_request = FALSE;
if ($pgid == -1) // the first request
{
	$first_request = TRUE;
	$pgid = 0;
}
$grps = $db->select_from('prob_grps', array('id', 'name'),
	array($DBOP['='], 'pgid', $pgid));
foreach ($grps as $grp)
{
	$id = $grp['id'];
	$name = $grp['name'];
	$grp = array(
		'data' => array(
			'title' => $name,
			'attr' => array(
				'href' => prob_view_by_group_get_a_href($id, 1, FALSE),
				'onclick' => prob_view_by_group_get_a_onclick($id, 1, 'id', 'ASC', NULL, FALSE)
			)
		),
		'attr' => array('id' => $id)
	);
	$nchild = $db->get_number_of_rows('prob_grps',
		array($DBOP['='], 'pgid', $id));
	if ($nchild)
		$grp['state'] = 'closed';
	$ret[] = $grp;
}

if ($first_request)
	$ret = array(
		'data' => array(
			'title' => __('All'),
			'attr' => array(
				'href' => prob_view_by_group_get_a_href(0, 1),
				'onclick' => prob_view_by_group_get_a_onclick(0, 1, 'id', 'ASC', NULL, FALSE)
			)
		),
		'attr' => array('id' => 0),
		'state' => 'open',
		'children' => $ret
	);
echo json_encode($ret);

/*
[
	{ "data" : "A node", "attr" : {"id" : 1} ,"children" : [ { "data" : "Only child", "state" : "closed", "attr" : {"id" : 2}} ], "state" : "open" },
	{ "data" : "Node ?", "attr" : {"id" : 3}}
]
 */
