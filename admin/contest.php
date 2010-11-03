<?php
/*
 * $File: contest.php
 * $Date: Tue Nov 02 20:33:52 2010 +0800
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

require_once $includes_path . 'contest/ctal.php';

/**
 * page argments:
 *	GET:
 *		[edit]:int, id of contest to be edited, or 0 if adding a new contest
 */
session_add_prefix('ct');

if (isset($_GET['edit']))
{
	$cur_page_link = "$cur_page_link&amp;edit=$_GET[edit]";
	require_once $admin_path . 'contest_edit.php';
	return;
}

$fields = array(
	// <database column name> => <display name>
	// <database column name> => array(<display name>, <transition function>)
	'id' => __('ID'),
	'type' => array(__('TYPE'), 'ctal_get_typename_by_type'),
	'name' => __('NAME'),
	'time_start' => array(__('START'), 'time2str'),
	'time_end' => array(__('END'), 'time2str')
);

$rows = $db->select_from('contests', array_keys($fields), array($DBOP['>'], 'time_end', time()),
	array('time_start' => 'ASC'));

echo "<a href='$cur_page_link&amp;edit=0'><button>" . __('Add a new contest') . '</button></a>';

echo '<table class="page-table">';
echo '<caption>' . __('List of unterminated contests') . '</caption>';
echo '<tr>';
foreach ($fields as $val)
{
	if (is_array($val))
		$val = $val[0];
	echo '<th>' . $val . '</th>';
}
echo '</tr>';
foreach ($rows as $row)
{
	echo '<tr>';
	foreach ($fields as $key => $val)
	{
		echo '<td>';
		if ($key == 'name')
			echo "<a href='$cur_page_link&amp;edit=$row[id]'>";
		if (is_array($val))
			echo $val[1]($row[$key]);
		else
			echo $row[$key];
		if ($key == 'name')
			echo '</a>';
		echo '</td>';
	}
	echo '</tr>';
}
echo '</table>';

