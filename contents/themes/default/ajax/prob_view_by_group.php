<?php
/*
 * $File: prob_view_by_group.php
 * $Date: Fri Oct 29 13:22:48 2010 +0800
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
/*
 *	POST:
 *		[<goto_page_default: int>]: 
 *			default value of goto-page-form in navigator bottom. just used as a symbol of where is in goto page mode.
 *		[<sort_col: string><sort_way: string><gid: int><start_page: int><title_pattern_show: string><prob-filter: string>]
 *			these are set the content of this page.
 *			'sort_col' includes: 'id', 'title', 'code', 'cnt_submit_user', cnt_ac_user', 'difficulty', see $show_fields below.
 *			'sort_way': either 'ASC' or 'DESC'
 *			difference between title_pattern_show and prob-filter is where does the filter come from
 *			gid: group id
 *	page argument: parsed in ../prob_func.php : prob_view_by_group_parse_arg(), when $_POST['sort_col'] and $_POST['sort_way'] are not set.
 */


require_once $includes_path . 'problem.php';
require_once $theme_path . 'prob_func.php';



// XXX: this should be a setting of theme
$PROB_VIEW_ROWS_PER_PAGE = 20;

$title_pattern = NULL;

$sort_col = 'id';
$sort_way = 'ASC';
$start_page = 1;

/**
 * @ignore
 */
function _tranform_pattern($tp)
{
	if ($tp == NULL)
		return NULL;
	$tp = trim($tp);
	$len = strlen($tp);
	$s = '';
	for ($i = 0; $i < $len; $i ++)
		if ($tp[$i] == '\\')
		{
			$i ++;
			if ($i < $len)
			{
				$ch = $tp[$i];
				if ($ch == '*' || $ch == '?' || $ch == '\\')
					$s .= ($ch == '\\' ? '\\\\' : $ch);
				else if ($ch == '_' || $ch == '%')
					$s .= '\\\\\\' . $ch;
				else
					$s .= '\\\\' . $ch;
			}
			else
				$s .= '\\\\';
		}
		else
		{
			$ch = $tp[$i];
			if ($ch == '%' || $ch == '_')
				$s .= '\\' . $ch;
			else if ($ch == '*')
				$s .= '%';
			else if ($ch == '?')
				$s .= '_';
			else
				$s .= $ch;
				
		}
	$s = '%' . $s . '%';
	return $s;
}

$fields = array('id', 'title', 'code', 'cnt_submit_user', 'cnt_ac_user', 'difficulty');

if (isset($_POST['goto_page_default']))
	$goto_page_default = $_POST['goto_page_default'];
if (isset($_POST['sort_col']) && isset($_POST['sort_way']))
{
	$sort_col = $_POST['sort_col'];	
	if (!in_array($sort_col, $fields))
		die('Man should be polite.');
	$sort_way = $_POST['sort_way'];
	$on_sort = TRUE;
	if (isset($_POST['gid']))
		$gid = intval($_POST['gid']);
	if ($gid === 0)
		$gid = NULL;
	if (isset($_POST['start_page']))
		$start_page = intval($_POST['start_page']);
	if (isset($_POST['title_pattern_show']))
	
	{
		$title_pattern_show = $_POST['title_pattern_show'];
		if ($title_pattern_show == '*')
			$title_pattern_show = NULL;
	}
}
else
{
	prob_view_by_group_parse_arg();
	if (isset($_POST['prob-filter']))
	{
		if ($_POST['prob-filter'] == 'prob-filter-title')
			$title_pattern_show = $_POST['value'];
		else
			throw new Exc_inner(__('Unknown problem filter.'));
	}
}

$title_pattern = _tranform_pattern($title_pattern_show);
if ($title_pattern_show == '*')
	$title_pattern = NULL;
if ($title_pattern_show == '')
	$title_pattern_show = $title_pattern = NULL;

$start_prob = ($start_page - 1) * $PROB_VIEW_ROWS_PER_PAGE + 1;
// parsed $gid, $start_page


$prob_amount = prob_get_amount($gid, $title_pattern);

if ((!(isset($on_sort) && $on_sort == TRUE)) || (isset($goto_page_default)))
{
	echo '<div id="prob-view-by-group-title">';
	if (!($title_pattern_show === NULL || $title_pattern_show == '*'))
	{
		echo __('Problems filter') . ' - ' . '<span>' . $title_pattern_show . '</span>';
	}
	else 
	{
		/* problem list title*/
		$gname = '';
		if ($gid == 0)
			$gname = __('All');
		else
			$gname = prob_grp_get_name_by_id($gid);

		// XXX: how to translate items in problem group?
		echo __('Problems') . ' - ' . '<span>' . $gname . '</span>';
	}

	echo '</div>';
}
?>

<script type="text/javascript">
var sort_col = "<?php echo $sort_col; ?>";
var sort_way = "<?php echo $sort_way; ?>";
</script>

<?
$url = t_get_link('ajax-prob-view-by-group', NULL, FALSE, TRUE);
echo <<<EOF
<script type="text/javascript">
function table_sort_by(col, default_order, title_pattern_show)
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
		"data" : ({"start_page" : "1",
			 "gid" : "$gid", 
			"sort_col" : col,
			 "sort_way" : sort_way,
			"title_pattern_show" : title_pattern_show,
			"on_sort" : true
			}),
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

$show_fields= array(
	array(__('ID'), 'id', 'ASC'),
	array(__('Title'), 'title', 'ASC'),
	array(__('Code'), 'code', 'ASC'),
	array(__('Accepted Users'), 'cnt_ac_user', 'DESC'),
	array(__('Submited Users'), 'cnt_submit_user', 'DESC'),
	array(__('Difficulty'), 'difficulty', 'ASC')
);

$sort_list = array(
	array('id', 'ASC')
//	array('cnt_ac_user', 'DESC'),
//	array('difficulty',  'ASC'),
//	array('cnt_submit_user', 'DESC')
);

/**
 * @ignore
 */
function _make_table_header($name, $col_name, $default_order)
{
	global $title_pattern_show, $sort_col, $sort_way;
	$t = ($title_pattern_show  == NULL ? '*' : $title_pattern_show);
	echo "<th><a style=\"cursor: pointer\" onclick=\"table_sort_by('$col_name', '$default_order', '$t'); return false;\">$name";
	if ($col_name == $sort_col)
		printf('<img src="%s" alt="sort way" style="float:right" />',
			_url('images/arrow_' . ($sort_way == 'ASC' ? 'up' : 'down') . '.gif', TRUE));
	echo '</a></th>';
}

$cnt_show_fields = count($show_fields);

// user problem status
if (user_check_login())
{
	echo '<th></th>';
	$cnt_show_fields ++;
}

foreach ($show_fields as $field)
	_make_table_header($field[0], $field[1], $field[2]);
echo '</tr>';


/**
 * @ignore
 */
function _make_prob_link($id, $name)
{
	global $gid, $start_page, $sort_col, $sort_way, $title_pattern_show;
	if ($title_pattern_show === NULL)
		$title_pattern_show = '*';
	echo '<td><a href="' . prob_view_single_get_a_href($id, $gid, $start_page, $sort_col, $sort_way, $title_pattern_show)
		. '" onclick="' . prob_view_single_get_a_onclick($id, $gid, $start_page, $sort_col, $sort_way, $title_pattern_show) 
		.'">' . $name . '</a></td>'; // Title
}

/**
 * @ignore
 */
function _adjust_val(&$val, $l, $r)
{
	if ($l > $r) return;
	if ($val < $l)
		$val = $l;
	if ($val > $r)
		$val = $r;
}

$total_page = ceil($prob_amount / $PROB_VIEW_ROWS_PER_PAGE);

_adjust_val($start_page, 1, $total_page);
$goto_page_default = $start_page;


$is_default_order = TRUE;
foreach ($sort_list as $val)
	if ($val[0] == $sort_col)
	{
		$is_default_order = ($sort_way == $val[1] ? TRUE : FALSE);
		break;
	}
/**
 * @ignore
 */
function op_order($order) {return $order == 'ASC' ? 'DESC' : 'ASC';}

$order_by[$sort_col] = $sort_way;
foreach ($sort_list as $val)
	if ($val[0] != $sort_col)
		$order_by[$val[0]] = ($is_default_order ? $val[1] : op_order($val[1]));

$probs = prob_get_list($fields, 
	$gid, 
	$title_pattern,
	$order_by,
	($start_page - 1) * $PROB_VIEW_ROWS_PER_PAGE, 
	$PROB_VIEW_ROWS_PER_PAGE);

$prob_user_sts_icon_info = array(
	STS_PROB_USER_UNTRIED => array(_url('images/prob_user_sts_untried.gif', TRUE), __('Untried')),
	STS_PROB_USER_UNAC => array(_url('images/prob_user_sts_unac.gif', TRUE), __('Unaccepted')),
	STS_PROB_USER_AC => array(_url('images/prob_user_sts_ac.gif', TRUE), __('Accepted')),
	STS_PROB_USER_AC_BLINK => array(_url('images/prob_user_sts_ac_blink.gif', TRUE), __('Accepted Blink'))
);

foreach ($probs as $prob)
{
	echo '<tr>';
	if (is_null($prob))
		for ($i = $cnt_show_fields; $i; $i --)
			echo '<td>---</td>';
	else
	{
		if (user_check_login())
		{
			$sts = prob_get_prob_user_status($prob['id']);
			$url = $prob_user_sts_icon_info[$sts][0];
			$info = $prob_user_sts_icon_info[$sts][1];
			echo "<td><img src=\"$url\" alt=\"$info\" title=\"$info\" /></td>";
		}
		echo '<td>' . $prob['id'] . '</td>'; // ID
		_make_prob_link($prob['id'], $prob['title']);
		_make_prob_link($prob['id'], $prob['code']);
		echo '<td>' . $prob['cnt_ac_user'] . '</td>'; 
		echo '<td>' . $prob['cnt_submit_user'] . '</td>';
		echo '<td>' . $prob['difficulty'] / DB_REAL_PRECISION * 100 . '%</td>';
	}
	echo '</tr>';
}
echo '
</table>';


/* bottom navigator */
echo '<div id="prob-view-by-group-navigator-bottom">';

/**
 * @ignore
 */
function _make_page_link($text, $page)
{
	global $gid, $sort_col, $sort_way, $title_pattern_show;
	return sprintf('<a href="%s" onclick="%s">%s</a>',
		prob_view_by_group_get_a_href($gid, $page, $sort_col, $sort_way, $title_pattern_show),
		prob_view_by_group_get_a_onclick($gid, $page, $sort_col, $sort_way, $title_pattern_show),
		$text
	);
}


/**
 * @ignore
 */
function _make_page_nav()
{
	global $start_page, $total_page;
	$ret = '';

	if ($start_page > 1)
		$ret .= '&lt;' . _make_page_link(__('Prev'), $start_page - 1);

	if ($start_page < $total_page)
		$ret .= ($start_page > 1 ? ' | ' : '') . _make_page_link(__('Next'), $start_page + 1) . '&gt;';

	return $ret;
}

$page_nav = _make_page_nav();

$id = _tf_get_random_id();
$GoToPage = __('Go to page');
if (!isset($goto_page_default))
	$goto_page_default = '';
$title_pattern_show_t = $title_pattern_show;
if ($title_pattern_show == NULL)
	$title_pattern_show_t = '*';
$url = t_get_link('ajax-prob-view-by-group', NULL, FALSE, TRUE);

echo <<<EOF
$page_nav
<form action="#" id="prob-view-by-group-goto-form" method="post" onsubmit="prob_view_by_group_goto(); return false;">
<label for="$id" style="float: left">$GoToPage</label>
<input value="$goto_page_default" name="goto_page" id="$id" style="float: left; width: 30px;" type="text" />
/$total_page
</form>
</div><!-- id: prob-view-by-group-navigator-bottom -->
</div><!-- id: prob-list -->

<script type="text/javascript">
function prob_view_by_group_goto()
{
	var t = $("#prob-view");
	t.animate({"opacity" : 0.5}, 1);
	page = $("#prob-view-by-group-goto-form input").val();
	$.ajax({
		"url" : "$url",
		"type" : "post",
		"cache" : false,
		"data" : ({"sort_col" : "$sort_col", 
					"sort_way" : "$sort_way",
					"gid" : "$gid",
					"start_page" : page,
					"title_pattern_show" : "$title_pattern_show",
					"goto_page_default" : page
				}),
		"success" : function(data) {
					t.animate({"opacity" : 1}, 1);
					t.html(data);
				}
	});
}
</script>
EOF;

?>

<script type="text/javascript">
$("button").button();
table_set_double_bgcolor();
</script>


