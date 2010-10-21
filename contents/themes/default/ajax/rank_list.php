<?php
/*
 * $File: rank_list.php
 * $Date: Thu Oct 21 13:21:46 2010 +0800
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

$USERS_PER_PAGE = 50;
$RANK_LIST_COLOR_SELF = '#7f7f7f';

$sort_list = array(
	array('cnt_ac_prob', 'DESC'),
	array('ac_ratio', 'DESC'),
	array('cnt_submit_prob', 'DESC')
);


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
	$sort_col = $sort_list[0][0];
	$sort_way = $sort_list[0][1];
}

if (isset($page_arg))
{
	if (sscanf($page_arg, '%d', $start_page) != 1)
		die(__('Unknown argument.'));
}

$user_amount = $db->get_number_of_rows('users');
$total_page = ceil($user_amount / $USERS_PER_PAGE);

if ($start_page < 1) $start_page = 1;
if ($start_page > $total_page) $start_page = $total_page;

$start_rank = ($start_page - 1) * $USERS_PER_PAGE + 1;
$rank_delta = 1;
$default_sort_col = $sort_list[0][0];
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
	array(__('Accepted Prob.'), 'cnt_ac_prob', 'DESC'),
	array(__('Submited Prob.'), 'cnt_submit_prob', 'DESC'),
	array(__('AC ratio'), 'ac_ratio', 'DESC')
);

?>
</script>

<?php if (!isset($post)) { ?>
<div id="rank-title"><?php echo __('Rank List');?></div>
<?php }?>

<div id="rank-list-content">

	<table class="page-table" id="rank-list-table">
<?php
echo '<tr>';
foreach ($heads as $head)
	_make_table_header($head[0], $head[1], $head[2]);
echo '</tr>';

/**
 * @ignore
 */
function op_order($order) 
{
	return $order == 'ASC' ? 'DESC' : 'ASC';
}


$orderby = array();
if ($sort_col == 'rank')
{
	$sort_col = $sort_list[0][0];
	if ($sort_way == 'ASC')
		$sort_way = $sort_list[0][1];
	else
		$sort_way = op_order($sort_list[0][1]);
}
$orderby[$sort_col] = $sort_way;
$is_default_order = FALSE;
foreach ($sort_list as $val)
	if ($val[0] == $sort_col)
	{
		if ($val[1] == $sort_way)
			$is_default_order = TRUE;
		else
			$is_default_order = FALSE;
		break;
	}
foreach ($sort_list as $val)
	if ($val[0] != $sort_col)
		$orderby[$val[0]] = ($is_default_order ? $val[1] : op_order($val[1]));

$users = $db->select_from('users', 
	array('id', 'nickname', 'realname', 'cnt_submit_prob', 'cnt_ac_prob', 'ac_ratio'),
	NULL,
	$orderby,
	($start_page - 1) * $USERS_PER_PAGE,
	$USERS_PER_PAGE
);


$get_rank_vesion = 2;
if ($get_rank_vesion == 1)
{
	/* get rank version 1 */
	/**
	 * @ignore
	 */
	function _get_rank($uid)
	{
		global $db, $DBOP, $sort_list;
		$tmp = NULL;
		$_get_rank_where = NULL;
		foreach ($sort_list as $sort)
		{
			$t = $tmp;
			$val = $db->select_from('users', array($sort[0]), array($DBOP['='], 'id', $uid));
			$val = $val[0][$sort[0]];
			db_where_add_and($t, array($DBOP[$sort[1] == 'ASC' ? '<' : '>'], $sort[0], $val));
			db_where_add_or($_get_rank_where, $t);
			db_where_add_and($tmp, array($DBOP['='], $sort[0], $val));
		}
		return $db->get_number_of_rows('users', $_get_rank_where) + 1;
	}
}
else if ($get_rank_vesion == 2)
{
	/* get rank version 2 */
	$order_by = array();
	$cols = array('id');
	foreach ($sort_list as $sort)
	{
		$order_by[$sort[0]] = $sort[1];
		$cols[] = $sort[0];
	}
	$tmp = $db->select_from('users', $cols, NULL, $order_by);
	$id2rank = array();
	$cnt = 0;
	$now = 1;
	function cmp($user1, $user2)
	{
		global $sort_list;
		foreach($sort_list as $sort)
			if ($user1[$sort[0]] != $user2[$sort[0]])
			{
				return $sort[1] == 'ASC' ? ($user1[$sort[0]] < $user2[$sort[0]]) : ($user1[$sort[0]] > $user2[$sort[0]]);
			}
		return false;
	}
	$prev_user = NULL;
	foreach ($tmp as $_user)
	{
		if ($cnt > 0 && !cmp($prev_user, $_user))
			$id2rank[$_user['id']] = $now;
		else
			$id2rank[$_user['id']] = $now = $cnt + 1;
		$cnt ++;
		$prev_user = $_user;
	}
	function _get_rank($uid)
	{
		global $id2rank;
		return $id2rank[$uid];
	}
}

foreach ($users as $_user)
{
	echo '<tr>'
		. '<td>' . _get_rank($_user['id']) . '</td>';
	foreach ($heads as $head)
	{
		switch ($head[1])
		{
		case 'rank':
			break;
		case 'ac_ratio':
			echo '<td>' . ($_user['ac_ratio'] / 100) . '%</td>';
			break;
		case 'nickname':
			$uid = $_user['id'];
			$url_href = t_get_link('ajax-user-info', "$uid", TRUE, TRUE);
			$nickname = $_user['nickname'];
			$realname = $_user['realname'];
			$style = '';
			if (user_check_login() && $uid == $user->id)
			{
				$style = "color: $RANK_LIST_COLOR_SELF;";
				$realname = __('This is you!') . ' ' . $realname . '.';
			}
			//$url_href = t_get_link('problem', "$uid", TRUE, TRUE);
			echo "<td><a class=\"rank-list-nickname-a\" href=\"$url_href\" style=\"$style\" title=\"$realname\">$nickname</a></td>";
			break;
		default:
			echo '<td>' . $_user[$head[1]] . '</td>';
		}
	}
	echo '</tr>';
}
?>
	</table>

<div id="rank-list-navigator-bottom">

<?php

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

$id = _tf_get_random_id();
$GoToPage = __('Go to page');
echo <<<EOF
<form action="#" id="rank-list-goto-form" method="post" onsubmit="rank_list_goto(); return false;">
<label for="$id" style="float: left;">$GoToPage</label>
<input value="$start_page" name="goto_page" id="$id" style="float: left; width: 30px;" type="text" />/$total_page
</form>
EOF;
?>



</div> <!-- id: rank-list-navigator-bottom -->

</div> <!-- id: rank-list-content -->

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
function rank_list_goto()
{
	var page = $("#rank-list-goto-form input").val();
	rank_list_naviage(page);
}

$(".rank-list-nickname-a").colorbox();
table_set_double_bgcolor();
</script>
