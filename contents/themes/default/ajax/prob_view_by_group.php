<?php
/*
 * $File: prob_view_by_group.php
 * $Date: Thu Oct 14 17:24:02 2010 +0800
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
require_once $theme_path . 'prob_func.php';

define('PAGE_PROB_LIST_ROWS', 20);

prob_view_by_group_parse_arg();
$fields = array('id', 'title', 'code', 'cnt_submit', 'cnt_ac');
$show_fields= array(
	__('ID'),
	__('Title'),
	__('Code'),
	__('Difficulty')
);
$probs = prob_get_list($fields, $gid, TRUE, ($start_page - 1) * PAGE_PROB_LIST_ROWS, PAGE_PROB_LIST_ROWS);
$content = '<table class="orzoj-table"><tr>';
foreach ($show_fields as $field)
	$content .= '<th>' . $field . '</th>';
$content .= '</tr>';

foreach ($probs as $prob)
{
	$content .= '<tr>';
	$content .= '<td>' . $prob['id'] . '</td>'; // ID
	$content .= '<td><a href="' . prob_view_single_get_a_href($prob['id'], $gid, $start_page) 
		. '" onclick="' . prob_view_single_get_a_onclick($prob['id'], $gid, $start_page) 
		.'">' . $prob['title'] . '</a></td>'; // Title
	$content .= '<td>' . $prob['code'] . '</td>'; // Code
	$content .= '<td>' . $prob['cnt_ac'] . '/' . $prob['cnt_submit'] . '</td>'; // Difficulty
	$content .= '</tr>';
}

$content .= '</table>';
echo $content;
