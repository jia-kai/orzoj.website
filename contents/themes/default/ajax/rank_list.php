<?php
/*
 * $File: rank_list.php
 * $Date: Mon Oct 18 23:48:12 2010 +0800
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

$USERS_PER_PAGE = 10;

$start_page = 1;
if (isset($_POST['sort_col']) && isset($_POST['sort_way']))
{
	$sort_col = $_POST['sort_col'];
	$sort_way = $_POST['sort_way'];
	$post = true;
	if (isset($_POST['start_page']))
		$start_page = intval($_POST['start_page']);
}
else
{
	$sort_col = 'cnt_ac';
	$sort_way = 'DESC';
}

if (isset($page_arg))
{
	if (sscanf($page_arg, '%d', $start_page) != 1)
		die(__('Unknown argument.'));
}

$start_rank = ($start_page - 1) * $USERS_PER_PAGE + 1;

?>

<script type="text/javascript">
var sort_col = "<?php echo $sort_col; ?>";
var sort_way = "<?php echo $sort_way; ?>";
</script>
<?php
$url = t_get_link('ajax-rank-list', NULL, FALSE, TRUE);
echo <<<EOF
<script type="text/javascript">
function table_sort_by(col, default_order)
{
	if (col == "rank")
		col = sort_col;
	if (sort_col == col)
	{
		if (sort_way == "ASC")
			sort_way = "DESC";
		else sort_way = "ASC";
	}
	else sort_way = default_order;
	sort_col = col;
	var t = $("#rank-list-content");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"type" : "post",
		"cache" : false,
		"url" : "$url",
		"data" : ({"sort_col" : col, "sort_way" : sort_way}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			t.html(data);
		}
	});
	return false;
}
</script>
EOF;

/**
 * @ignore
 */
function _make_table_header($name, $col_name, $default_order)
{
	echo "<th><a style=\"cursor: pointer\" onclick=\"table_sort_by('$col_name', '$default_order'); return false;\">$name</a></th>";
}

$heads = array(
	array(__('Rank'), 'rank', 'ASC'),
	array(__('Nickname'), 'nickname', 'ASC'),
	array(__('Accepted'), 'cnt_ac', 'DESC'),
	array(__('Submited'), 'cnt_submit', 'DESC'),
	array(__('Ratio(AC/Solved)'), 'ac_ratio', 'DESC')
);
?>
</script>

<?php if (!isset($post)) { ?>
<div id="rank-title">Rank List</div>
<?php }?>

<div id="rank-list-content">

	<table class="page-table" id="rank-list-table">
<?php
echo '<tr>';
foreach ($heads as $head)
	_make_table_header($head[0], $head[1], $head[2]);
echo '</tr>';

$users = $db->select_from('users', 
	array('id', 'nickname', 'cnt_submit', 'cnt_ac', 'ac_ratio'),
	NULL,
	array($sort_col  => $sort_way),
	($start_page - 1) * $USERS_PER_PAGE,
	$USERS_PER_PAGE
);

foreach ($users as $user)
{
	echo '<tr>'
		. '<td>' . ($start_rank ++) . '</td>';
	foreach ($heads as $head)
	{
		switch ($head[1])
		{
		case 'rank':
			break;
		case 'ac_ratio':
			echo '<td>' . ($user['ac_ratio'] / 100) . '%</td>';
			break;
		case 'nickname':
			$uid = $user['id'];
			$url_href = t_get_link('show-ajax-user-info', "$uid", TRUE, TRUE);
			$url_onclick = t_get_link('ajax-user-info', "$uid", TRUE, TRUE);
			echo "<td><a class=\"rank-list-nickname-a\" href=\"$url_href\" onclick=\"$url_onclick\">" . $user['nickname'] . '</a></td>';
			break;
		default:
			echo '<td>' . $user[$head[1]] . '</td>';
		}
	}
	echo '</tr>';
}
?>
	</table>

<div id="rank-list-navigator-bottom">
<script type="text/javascript">
function rank_list_naviage(page)
{
	var t = $("#rank-list-content");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"type" : "post",
			"cache" : false,
			"url" : "<?php t_get_link('ajax-rank-list', NULL, FALSE); ?>", 
			"data" : ({"sort_col" : "<?php echo $sort_col; ?>", 
			"sort_way" : "<?php echo $sort_way; ?>",
			"start_page" : page
			}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			t.html(data);
		}
	});
	return false;
}
$(".rank-list-nickname-a").colorbox();
table_set_double_bgcolor();
</script>
<?php
$user_amount = $db->get_number_of_rows('users');
$total_page = ceil($user_amount / $USERS_PER_PAGE);
/**
 * @ignore
 */
function _make_page_link($promt, $page)
{
	$url = t_get_link('show-ajax-rank-list', "$page", TRUE, TRUE);
	echo "<a href=\"$url\" onclick=\"rank_list_naviage($page); return false;\">$promt</a>";
}

if ($start_page > 1)
	_make_page_link('&lt;' . __('Prev'), $start_page - 1);
if ($start_page < $total_page)
{
	if ($start_page > 1)
		echo ' | ';
	_make_page_link(__('Next') . '&gt;', $start_page + 1);
}
?>
</div> <!-- id: rank-list-navigator-bottom -->

</div> <!-- id: rank-list-content -->


