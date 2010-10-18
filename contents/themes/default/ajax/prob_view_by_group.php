<?php
/*
 * $File: prob_view_by_group.php
 * $Date: Mon Oct 18 15:07:41 2010 +0800
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
	__('Accepted'),
	__('Submited'),
	__('Difficulty')
);
$prob_amount = prob_get_amount($gid);

$probs = prob_get_list($fields, $gid, TRUE, ($start_page - 1) * $PROB_VIEW_ROWS_PER_PAGE, $PROB_VIEW_ROWS_PER_PAGE);

/* problem list title*/
echo '<div id="prob-view-by-group-title">';
$gname = '';
if ($gid == 0)
	$gname = 'All';
else
{
	$gname = $db->select_from('prob_grps', array('name'), array($DBOP['='], 'id', $gid));
	$gname = $gname[0]['name'];
}

// XXX: how to translate items in problem group?
echo __('Problems') . ' - ' . '<span>' . $gname . '</span>';
echo '</div>';

echo '
<div id="prob-list">
<table class="page-table">
<tr>';

foreach ($show_fields as $field)
	echo '<th>' . $field . '</th>';
echo '</tr>';

/**
 * @ignore
 */
function _make_prob_link($id, $name)
{
	global $gid, $start_page;
	echo '<td><a href="' . prob_view_single_get_a_href($id, $gid, $start_page)
		. '" onclick="' . prob_view_single_get_a_onclick($id, $gid, $start_page) 
		.'">' . $name . '</a></td>'; // Title
}

foreach ($probs as $prob)
{
	echo '<tr>';
	if (is_null($prob))
		for ($i = count($show_fields); $i; $i --)
			echo '<td>---</td>';
	else
	{
		echo '<td>' . $prob['id'] . '</td>'; // ID
		_make_prob_link($prob['id'], $prob['title']);
		_make_prob_link($prob['id'], $prob['code']);
		echo '<td>' . $prob['cnt_ac'] . '</td>'; // Accepted
		echo '<td>' . $prob['cnt_submit'] . '</td>'; // Submited
		$d = ($prob['cnt_submit'] - $prob['cnt_ac']) / $prob['cnt_submit'];
		$d = floor($d * 1000) / 1000 * 100;
		echo '<td>' . $d . '%</td>';
	}
	echo '</tr>';
}
echo '
</table>
</div>';

$total_page = ceil($prob_amount / $PROB_VIEW_ROWS_PER_PAGE);


/* bottom navigator */
echo '<div id="prob-view-by-group-navigator-bottom">';

function make_page_link($text, $page)
{
	global $gid;
	return sprintf('<a href="%s" onclick="%s; return false;">%s</a>',
		prob_view_by_group_get_a_href($gid, $page),
		prob_view_by_group_get_a_onclick($gid, $page),
		$text
	);
}

if ($start_page > 1)
	echo '&lt;' . make_page_link(__('Prev'), $start_page - 1);

if ($start_page < $total_page)
	echo ($start_page > 1 ? ' | ' : '') . make_page_link(__('Next'), $start_page + 1) . '&gt;';

echo '<span>' . $start_page . '/' . $total_page . '</span>';

echo '</div>';
?>

<script type="text/javascript">
$("button").button();
table_set_double_bgcolor();
</script>

