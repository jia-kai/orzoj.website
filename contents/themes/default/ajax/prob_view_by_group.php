<?php
/*
 * $File: prob_view_by_group.php
 * $Date: Thu Oct 14 09:45:52 2010 +0800
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

$id = $page_arg;
$fields = array('id', 'title', 'code', 'cnt_submit', 'cnt_ac');
$translate = array(
	__('id'),
	__('title'),
	__('code'),
	__('cnt_submut'),
	);
$probs = prob_get_list($fields, $id, TRUE, NULL, NULL);
$content = '<table class="orzoj-table"><tr>';
foreach ($fields as $field)
{
	$content .= '<th>' . $field . '</th>';
}
$content .= '</tr>';
foreach ($probs as $prob)
{
	$content .= '<tr>';
	foreach ($prob as $col)
		$content .= '<td>' . $col . '</td>';
	$content .= '</tr>';
}

$content .= '</table>';
echo $content;
