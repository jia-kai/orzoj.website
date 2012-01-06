<?php
/*
 * $File: user.php
 * $Date: Fri Jan 06 14:15:11 2012 +0800
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
 * page argument:
 *	GET:
 *		[sort_col]:string
 *		[sort_way]:int 0: ASC; otherwise DESC
 *		[edit]:int the id of user to be edited
 *		[pgnum]:int page number, starting at 0
 *
 *	POST:
 *		[filters]:array array of applied filters
 *		[pgnum]:int page number, starting at 1
 *
 *	SESSION:
 *		[sort_col, sort_way, filters]
 */
session_add_prefix('user');

define('PAGE_SIZE', 50);

if (!empty($_GET['edit']))
{
	$cur_page_link = "$cur_page_link&amp;edit=$_GET[edit]";
	require_once $admin_path . 'user_edit.php';
	return;
}

$fields = array(
	// <database column name> => array(<table head>, <default sort way>, [<display function>])
	'id' => array(__('ID'), 0),
	'username' => array(__('USERNAME'), 0),
	'nickname' => array(__('NICKNAME'), 0),
	'realname' => array(__('REAL NAME'), 0),
	'reg_time' => array(__('REGISTER'), 1, 'time2str'),
	'last_login_time' => array(__('LAST LOGIN'), 1, 'time2str')
);
 
if (isset($_GET['sort_col']) && isset($_GET['sort_way']))
{
	$s = $_GET['sort_col'];
	if (!isset($fields[$s]))
		die('no such sorting column');
	session_set('sort_col', $_GET['sort_col']);
	session_set('sort_way', $_GET['sort_way'] == 1);
}

$sort_col = session_get('sort_col');
$sort_way = session_get('sort_way');
if (is_null($sort_col))
	$sort_col = 'id';
if (is_null($sort_way))
	$sort_way = $fields[$sort_col][1];

if (isset($_GET['pgnum']))
	$pgnum = intval($_GET['pgnum']);
else
	$pgnum = 0;

if (isset($_POST['pgnum']))
	$pgnum = intval($_POST['pgnum']) - 1;

if ($pgnum < 0)
	$pgnum = 0;

$filters = array(
	'uid' => array($DBOP['='], 'id'),
	'tid' => array($DBOP['='], 'tid'),
	'username' => array($DBOP['like'], 'username'),
	'realname' => array($DBOP['like'], 'realname'),
	'nickname' => array($DBOP['like'], 'nickname')
);

$filters_allow_empty = array('tid');

if (isset($_POST['filters']) && is_array($_POST['filters']))
	session_set('filters', array_intersect_key($_POST['filters'], $filters));

$where = NULL;
if (is_array($filters_req = session_get('filters')))
	foreach ($filters_req as $f => $v)
		if (!empty($v) || in_array($f, $filters_allow_empty))
			db_where_add_and($where, array_merge($filters[$f], array($v))); 


$rows = $db->select_from('users', array_keys($fields), $where,
	array($sort_col => ($sort_way ? 'DESC' : 'ASC')),
	PAGE_SIZE * $pgnum, PAGE_SIZE);

echo "<form action='$cur_page_link' method='post' class='filter-form'>";
echo '<span><a title="' . __('you can enter patterns with * and ?') . '">' . __('Search user:') . '</a></span>';
filter_form_get_input(__('ID:'), 'uid');
filter_form_get_input(__('Username:'), 'username');
filter_form_get_input(__('Nickname:'), 'nickname');
filter_form_get_input(__('Real name:'), 'realname');
echo '<div style="clear:both;height:5px;">&nbsp;</div>';
make_tid_select();
echo '<span><input type="submit" value="' . __('search') . '" />' . '</span>';
echo '</form>';

echo '<table class="page-table">';
echo '<caption>' . __('User List') . '</caption>';
echo '<tr>';
foreach ($fields as $f => $v)
{
	if ($f == $sort_col)
	{
		$way = 1 - $sort_way;
		$arr = $sort_way ? '&darr' : '&uarr;';
	}
	else
	{
		$way = $v[1];
		$arr = '';
	}
	echo "<th><a href='$cur_page_link&amp;sort_col=$f&amp;sort_way=$way&amp'>$v[0]$arr</a></th>";
}
echo '</tr>';
foreach ($rows as $row)
{
	echo '<tr>';
	foreach ($fields as $f => $v)
	{
		echo '<td>';
		$out = $row[$f];
		if (isset($v[2]))
			$out = $v[2]($row[$f]);
		else
			$out = "<a href='$cur_page_link&amp;edit=$row[id]'>$out</a>";
		echo $out;
		echo '</td>';
	}
	echo '</tr>';
}
echo '</table>';

make_pgnum_nav($pgnum, ceil($db->get_number_of_rows('users', $where) / PAGE_SIZE));

function filter_form_get_input($prompt, $pname)
{
	global $filters_req;
	echo '<span>';
	form_get_input($prompt, "filters[$pname]", get_array_val($filters_req, $pname), FALSE);
	echo '</span>';
}

