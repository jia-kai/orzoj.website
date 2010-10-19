<?php
/*
 * $File: prob_view_by_group.php
 * $Date: Tue Oct 19 01:07:52 2010 +0800
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

$sort_col = 'id';
$sort_way = 'ASC';
$start_page = 1;
if (isset($_POST['sort_col']) && isset($_POST['sort_way']))
{
	$sort_col = $_POST['sort_col'];
	$sort_way = $_POST['sort_way'];
	$post = true;
	if (isset($_POST['gid']))
		$gid = intval($_POST['gid']);
	if ($gid === 0)
		$gid = NULL;
	if (isset($_POST['start_page']))
		$start_page = intval($_POST['start_page']);
}
else
	prob_view_by_group_parse_arg();

$start_prob = ($start_page - 1) * $PROB_VIEW_ROWS_PER_PAGE + 1;
// parsed $gid, $start_page

$fields = array('id', 'title', 'code', 'cnt_submit', 'cnt_ac', 'ac_ratio');
$show_fields= array(
	array(__('ID'), 'id', 'ASC'),
	array(__('Title'), 'title', 'ASC'),
	array(__('Code'), 'code', 'ASC'),
	array(__('Accepted'), 'cnt_ac', 'DESC'),
	array(__('Submited'), 'cnt_submit', 'DESC'),
	array(__('Ratio'), 'ac_ratio', 'ASC')
);

$prob_amount = prob_get_amount($gid);


if (!isset($post))
{
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
}
echo '</div>';
?>

<script type="text/javascript">
var sort_col = "<?php echo $sort_col; ?>";
var sort_way = "<?php echo $sort_way; ?>";
</script>

<?
$url = t_get_link('ajax-prob-view-by-group', NULL, FALSE, TRUE);
echo <<<EOF
<script type="text/javascript">
function table_sort_by(col, default_order)
{
	if (sort_col == col)
	{
		if (sort_way == "ASC")
			sort_way = "DESC";
		else sort_way = "ASC";
	}
	else sort_way = default_order;
	sort_col = col;
	var t = $("#prob-list");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"type" : "post",
		"cache" : false,
		"url" : "$url",
		"data" : ({"start_page" : "1", "gid" : "$gid", "sort_col" : col, "sort_way" : sort_way}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			t.html(data);
		}
	});
	return false;
}
</script>
EOF;



echo <<<EOF
<div id="prob-list">
<table class="page-table">
<tr>
EOF;
/**
 * @ignore
 */
function _make_table_header($name, $col_name, $default_order)
{
	echo "<th><a style=\"cursor: pointer\" onclick=\"table_sort_by('$col_name', '$default_order'); return false;\">$name</a></th>";
}
foreach ($show_fields as $field)
	_make_table_header($field[0], $field[1], $field[2]);
echo '</tr>';


/**
 * @ignore
 */
function _make_prob_link($id, $name)
{
	global $gid, $start_page, $sort_col, $sort_way;
	echo '<td><a href="' . prob_view_single_get_a_href($id, $gid, $start_page)
		. '" onclick="' . prob_view_single_get_a_onclick($id, $gid, $start_page, $sort_col, $sort_way) 
		.'">' . $name . '</a></td>'; // Title
}

//$GID = ($gid === NULL ? "NULL" : $gid);
//echo "gid:$GID, sort_col:$sort_col, sort_way:$sort_way";

$probs = prob_get_list($fields, 
	$gid, 
	array($sort_col => $sort_way), 
	($start_page - 1) * $PROB_VIEW_ROWS_PER_PAGE, 
	$PROB_VIEW_ROWS_PER_PAGE);

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
		echo '<td>' . $prob['ac_ratio'] / 100 . '%</td>';
	}
	echo '</tr>';
}
echo '
</table>';

$total_page = ceil($prob_amount / $PROB_VIEW_ROWS_PER_PAGE);


/* bottom navigator */
echo '<div id="prob-view-by-group-navigator-bottom">';

function make_page_link($text, $page)
{
	global $gid, $sort_col, $sort_way;
	return sprintf('<a href="%s" onclick="%s; return false;">%s</a>',
		prob_view_by_group_get_a_href($gid, $page),
		prob_view_by_group_get_a_onclick($gid, $page, $sort_col, $sort_way),
		$text
	);
}

if ($start_page > 1)
	echo '&lt;' . make_page_link(__('Prev'), $start_page - 1);

if ($start_page < $total_page)
	echo ($start_page > 1 ? ' | ' : '') . make_page_link(__('Next'), $start_page + 1) . '&gt;';

echo '<span>' . $start_page . '/' . $total_page . '</span>';

echo '</div><!-- id: prob-list -->';
echo '</div>';
?>

<script type="text/javascript">
$("button").button();
table_set_double_bgcolor();
</script>

