<?php
/*
 * $File: prob_view_by_group.php
 * $Date: Fri Oct 15 10:22:43 2010 +0800
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

// XXX: this should be a setting of theme
$PROB_VIEW_ROWS_PER_PAGE = 20;

prob_view_by_group_parse_arg();
$fields = array('id', 'title', 'code', 'cnt_submit', 'cnt_ac');
$show_fields= array(
	__('ID'),
	__('Title'),
	__('Code'),
	__('Difficulty')
);
// XXX: how does this work?
$prob_amount = prob_get_amount($gid);

// XXX: how does this work?
$probs = prob_get_list($fields, $gid, TRUE, ($start_page - 1) * $PROB_VIEW_ROWS_PER_PAGE, $PROB_VIEW_ROWS_PER_PAGE);

$content = '';

/* problem list title*/
$content .= '<div id="prob-view-by-group-title">';
$gname = '';
if ($gid == 0)
	$gname = 'All';
else
{
	$gname = $db->select_from('prob_grps', array('name'), array($DBOP['='], 'id', $gid));
	$gname = $gname[0]['name'];
}
// XXX: how to translate items in problem group?
$content .= __('Problems') . ' - ' . '<span>' . $gname . '</span>';
$content .= '</div>';

/* problem list table */
$content .= '<table class="orzoj-table"><tr>';
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

$total_page = ceil($prob_amount / $PROB_VIEW_ROWS_PER_PAGE);


/* bottom navigator */
$content .= '<div id=prob-view-by-group-navigator-bottom>';
// TODO: button effects
if ($start_page > 1)
{
	$content .= '<a href="' . prob_view_by_group_get_a_href($gid, $start_page - 1) . '"'
		. ' onclick="' . prob_view_by_group_get_a_onclick($gid, $start_page - 1) . '">'
		. __('Prev') . '</a>';
}
$content .= $start_page . '/' . $total_page;
if ($start_page < $total_page)
{
	$content .= '<a href="' . prob_view_by_group_get_a_href($gid, $start_page + 1) . '"'
		. ' onclick="' . prob_view_by_group_get_a_onclick($gid, $start_page + 1) . '">'
		. __('Next') . '</a>';
}
$content .= '</div>';
$content .= '<script type="text/javascript">';
$content .= '$("button").button();';
$content .= 'table_set_double_bgcolor();';
$content .= '</script>';
echo $content;

