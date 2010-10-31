<?php
/*
 * $File: problem.php
 * $Date: Sun Oct 31 11:24:06 2010 +0800
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

require_once $includes_path . 'problem.php';

/*
 * GET arguments:
 *		[edit]: int
 *			edit a problem with id $_GET['id']. If it equals to 0, add a new problem
 *			If this argument is set, other arguments are ignored
 *		[pgnum]: int, page number
 *		[sort_col, sort_way]: string, int
 *			sort_way: 0: ASC; otherwise DESC
 *		[filter]: indicate a submission of the filter form
 * SESSION variables:
 *		[code]:string, problem code
 *		[title]:string, problem title
 *		[gid]:int, problem group id
 *		[sort_col, sort_way]:string, int
 * POST arguments:
 *		[code, title, gid] used to set SESSION variables
 *		[pgnum]: int, page number (starting at 1)
 */

if (isset($_GET['edit']))
{
	$cur_page_link = "$cur_page_link&amp;edit=$_GET[edit]";
	require_once $admin_path . 'problem_edit.php';
	die;
}

define('PAGE_SIZE', 50);

if (isset($_GET['filter']))
{
	foreach (array('code', 'title', 'gid') as $v)
		session_set($v, $_POST[$v]);
}

if (isset($_GET['sort_col']))
{
	session_set('sort_col', $_GET['sort_col']);
	session_set('sort_way', $_GET['sort_way']);
}

if (isset($_GET['pgnum']))
	$pgnum = intval($_GET['pgnum']);
else $pgnum = 0;

if (isset($_POST['pgnum']))
	$pgnum = intval($_POST['pgnum']) - 1;

if ($pgnum < 0)
	$pgnum = 0;

$fields = array(
	// <database column name> => array(<display name>, <sort way>, [<transition function>])
	'id' => array(__('ID'), 0),
	'title' => array(__('TITLE'), 0),
	'code' => array(__('CODE'), 0),
	'time' => array(__('LAST MODIFIED'), 1, 'time2str'),
);

if (!session_get('sort_col'))
{
	$sort_col = 'id';
	$sort_way = $fields[$sort_col][1];
}
else
{
	$sort_col = session_get('sort_col');
	$sort_way = session_get('sort_way');
}

if (!isset($fields[$sort_col]))
	die('no such column to sort');

if ($sort_way == 0)
{
	$sort_way = 0;
	$sort_way_str = 'ASC';
}
else
{
	$sort_way = 1;
	$sort_way_str = 'DESC';
}

function make_form_input($prompt, $post_name)
{
	$id = get_random_id();
	$value = session_get($post_name);
	if (!is_null($value))
		$value = " value='$value' ";
	echo "<span><label for='$id'>$prompt</label><input id='$id' name='$post_name' $value type='text' /></span>";
}

function make_form_select($prompt, $post_name, &$options)
{
	$id = get_random_id();
	echo "<span><label for='$id'>$prompt</label><select id='$id' name='$post_name'>";
	$default = session_get($post_name);
	foreach ($options as $value)
	{
		$name = $value[0];
		$value = $value[1];
		echo "<option value='$value'";
		if ($value == $default)
			echo ' selected="selected" ';
		echo ">$name</option>";
	}
	echo '</select></span>';
}

echo "<div><a href='$cur_page_link&amp;edit=0'>" . __('Add New Problem') . '</a></div>';

echo "<form action='$cur_page_link&amp;filter=1' method='post' class='filter-form'>";
echo '<span><a title="' . __('you can enter patterns with * and ?') . '">' . __('Search problem:') . '</a></span>';
make_form_input(__('Code:'), 'code');
make_form_input(__('Title:'), 'title');
make_form_select(__('Problem group:'), 'gid', make_pgid_select_opt());
echo '<span><input type="submit" value="' . __('Search') . '" /></span>';
echo '</form>';

if (session_get('gid') == 0)
	session_set('gid', NULL);
$where = _prob_get_list_make_where(session_get('gid'), transform_pattern(session_get('title')));
db_where_add_and($where, array($DBOP['like'], 'code', transform_pattern(session_get('code'))));

$rows = $db->select_from('problems', array_keys($fields), $where,
	array($sort_col => $sort_way_str), $pgnum * PAGE_SIZE, PAGE_SIZE);

echo '<table class="page-table">';
echo '<caption>' . __('Problem list') . '</caption>';
echo '<tr>';
foreach ($fields as $key => $f)
{
	if ($key == $sort_col)
		$way = 1 - $sort_way;
	else
		$way  = $f[1];
	$f = $f[0];
	echo "<th><a href='$cur_page_link&amp;sort_col=$key&amp;sort_way=$way'>$f";
	if ($key == $sort_col)
		echo $sort_way ? '&darr;' : '&uarr;';
	echo '</a></th>';
}
echo '</tr>';

foreach ($rows as $row)
{
	echo '<tr>';
	foreach ($fields as $key => $f)
	{
		echo '<td>';
		if (isset($f[2]))
			echo $f[2]($row[$key]);
		else echo "<a href='$cur_page_link&amp;edit=$row[id]'>$row[$key]</a>";
		echo '</td>';
	}
	echo '</tr>';
}

echo '</table>';

make_pgnum_nav($pgnum, ceil($db->get_number_of_rows('problems', $where) / PAGE_SIZE));

