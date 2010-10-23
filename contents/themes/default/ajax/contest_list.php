<?php
/*
 * $File: contest_list.php
 * $Date: Sat Oct 23 20:48:15 2010 +0800
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

/*
 * page argument: [(all|past|current|upcoming)[-<page num>]]
 *
 * POST:
 *		sort_col: string, one of keys of $cols
 *		sort_way: 0: 'ASC', 1: 'DESC'. otherwise: default
 *		page_num: int (starting from 1)
 *
 */

define('PAGE_SIZE', 20);

$echo_div_contest_list = TRUE;
$sort_col = 'time_start';
$sort_way = 'DESC';
if (is_null($page_arg))
{
	$ct_time = NULL;
	$pg_num = 0;
}
else
{
	$tmp = explode('-', $page_arg);
	$ct_time = $tmp[0];
	if (count($tmp) >= 2)
	{
		$pg_num = intval($tmp[1]);
		$echo_div_contest_list = FALSE;
	} else $pg_num = 0;
	$TIME = array(
		'all' => NULL,
		'past' => -1,
		'current' => 0,
		'upcoming' => 1
	);
	if (!array_key_exists($ct_time, $TIME))
		die('invalid argument');
	$ct_time_str = $ct_time;
	$ct_time = $TIME[$ct_time];
	unset($TIME);
}

if (isset($_POST['page_num']))
	$pg_num = intval($_POST['page_num']) - 1;

if ($pg_num < 0)
	$pg_num = 0;

function _cv_id()
{
	global $cur_row;
	echo $cur_row['id'];
}

function _cv_type()
{
	global $cur_row;
	echo ctal_get_typename_by_type($cur_row['type']);
}

function _cv_name()
{
	global $cur_row;
	echo '<a href="';
	t_get_link('contest', $cur_row['id']);
	echo '" onclick="contest_view(\'' . $cur_row['id'] . '\'); return false;">';
	echo $cur_row['name'];
	echo '</a>';
}

function _cv_time_start()
{
	global $cur_row;
	echo time2str($cur_row['time_start']);
}

function _cv_time_end()
{
	global $cur_row;
	echo time2str($cur_row['time_end']);
}

$cols = array(
	// <database field> => array(<display name>, <display function>, <default sort way>)
	'id' => array(__('ID'), '_cv_id', 'DESC'),
	'type' => array(__('TYPE'), '_cv_type', 'ASC'),
	'name' => array(__('NAME'), '_cv_name', 'ASC'),
	'time_start' => array(__('START'), '_cv_time_start', 'DESC'),
	'time_end' => array(__('END'), '_cv_time_end', 'DESC')
);

if (isset($_POST['sort_col']) && isset($_POST['sort_way']))
{
	$sort_col = $_POST['sort_col'];
	$sort_way = $_POST['sort_way'];
	if (!isset($cols[$sort_col]))
		die('invalid argument');
	$sort_way = intval($sort_way);
	if ($sort_way == 0)
		$sort_way = 'ASC';
	else if ($sort_way == 1)
		$sort_way = 'DESC';
	else $sort_way = $cols[$sort_col][2];

	$echo_div_contest_list = FALSE;
}

$sort_way_num = ($sort_way == 'ASC' ? 0 : 1);

if ($echo_div_contest_list)
	echo '<div class="contest-list" id="contest-list-' . $ct_time_str . '">';

echo '<table class="page-table">';
echo '<tr>';
foreach ($cols as $key => $col)
{
	echo "<th><a class='page-table-th' onclick='change_sort(\"$key\");'>$col[0]";
	if ($key == $sort_col)
		printf('<img src="%s" alt="sort way" style="float:right" />',
			_url('images/arrow_' . ($sort_way == 'ASC' ? 'up' : 'down') . '.gif', TRUE));
	echo '</a></th>';
}
echo '</tr>';

$rows = ctal_get_list(array_keys($cols), $ct_time, array($sort_col => $sort_way), $pg_num * PAGE_SIZE, PAGE_SIZE);

foreach ($rows as $cur_row)
{
	echo '<tr>';
	if (is_null($cur_row))
		for ($i = count($cols); $i; $i --)
			echo '<td>---</td>';
	else
	{
		foreach ($cols as $col)
		{
			echo '<td>';
			$func = $col[1];
			$func();
			echo '</td>';
		}
	}
	echo '</tr>';
}

echo '</table>';

echo '<div class="contest-list-nav">';
function _make_link($prompt, $pg)
{
	global $ct_time_str;
	printf('<a href="%s" onclick="contest_list_nav(%d); return false;">%s</a>',
		t_get_link('show-ajax-contest-list', "$ct_time_str-$pg", TRUE, TRUE),
		$pg + 1, $prompt);
}

if ($pg_num)
{
	echo '<div style="float: left;">&lt;';
	_make_link(__('Prev'), $pg_num - 1);
	echo ' | </div>';
}

echo '<form id="contest-list-nav-form-' . $ct_time_str .'" method="post" action="';
t_get_link('show-ajax-contest-list', $ct_time_str);
echo '">
	<input type="text" id="contest-list-nav-input-' . $ct_time_str .'" name="page_num" value="' . ($pg_num + 1) .'" />';
$tot_page = ceil(ctal_get_list_size($ct_time) / PAGE_SIZE);
echo "/$tot_page";
echo '</form>';

if ($pg_num + 1 < $tot_page)
{
	echo '<div style="float: left;"> | ';
	_make_link(__('Next'), $pg_num + 1);
	echo '&gt;</div>';
}

echo '</div> <!-- class: contest-list-nav -->';

if ($echo_div_contest_list)
	echo '</div>';

?>

<div style="clear:both">&nbsp;</div>
<!-- I need this div to make ui.tabs work properly?? -->

<script type="text/javascript">
function change_sort(col)
{
	var way = -1;
	if (col == "<?php echo $sort_col;?>")
		way = <?php echo 1 - $sort_way_num?>;
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-list', $ct_time_str, FALSE);?>",
		"data": ({"sort_col": col, "sort_way": way}),
		"success": function (data) {
			$("#contest-list-<?php echo $ct_time_str;?>").html(data);
		}
	});
}

function contest_view(id)
{
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-view', NULL, FALSE);?>",
		"data": ({"id": id, <?php echo "'time': '$ct_time_str', 'sort_col': '$sort_col', "; $pn = $pg_num + 1;
			echo "'sort_way': '$sort_way_num', 'page_num': '$pn'";?>}),
		"success": function (data) {
			$("#contest-list-<?php echo $ct_time_str;?>").html(data);
		}
	});
}

function contest_list_nav(pgnum)
{
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-list', $ct_time_str, FALSE);?>",
		"data": ({<?php echo "'sort_col': '$sort_col', 'sort_way': '$sort_way_num'";?>, 'page_num': pgnum}),
		"success": function (data) {
			$("#contest-list-<?php echo $ct_time_str;?>").html(data);
		}
	});
}

$("#contest-list-nav-form-<?php echo $ct_time_str;?>").bind("submit", function(){
	contest_list_nav($("#contest-list-nav-input-<?php echo $ct_time_str?>").val());
	return false;
})
table_set_double_bgcolor();
</script>

