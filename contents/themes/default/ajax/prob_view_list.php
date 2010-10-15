<?php
/*
 * $File: prob_view_list.php
 * $Date: Fri Oct 15 14:38:17 2010 +0800
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
?>

<table class="orzoj-table"><tr>
<?php
foreach ($show_fields as $field)
	echo '<th>' . $field . '</th>';
echo '</tr>';

foreach ($probs as $prob)
{
	$content = '';
	$content .= '<tr>';
	$content .= '<td>' . $prob['id'] . '</td>'; // ID
	$content .= '<td><a href="' . prob_view_single_get_a_href($prob['id'], $gid, $start_page) 
		. '" onclick="' . prob_view_single_get_a_onclick($prob['id'], $gid, $start_page) 
		.'">' . $prob['title'] . '</a></td>'; // Title
	$content .= '<td>' . $prob['code'] . '</td>'; // Code
	$content .= '<td>' . $prob['cnt_ac'] . '/' . $prob['cnt_submit'] . '</td>'; // Difficulty
	$content .= '</tr>';
	echo $content; $content = '';
}
?>
</table>
