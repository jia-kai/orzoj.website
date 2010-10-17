<?php
/*
 * $File: status_list.php
 * $Date: Sun Oct 17 10:45:18 2010 +0800
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
 * page argument: [<starting page num:int>]
 * POST: 
 * ['filter'|'request'|'prob_submit'|'prob_best_solutions']
 *		'filter':
 *			value: array of used filters
 *			return a complete ajax page
 *		'request':
 *			value: array of requests record ids (size <= PAGE_SIZE)
 *			return json encoded array (<record id> => <done><table columns>)
 *				if done == 0, judge for this record is not finished and the
 *				record should be requested again
 *				if done == 1, this record needs no more updating
 *		'prob_submit':
 *			used for dynamic status display in problem submission
 *			value: problem id
 *			return: <done><status as string|detail page address>
 *				if done == 0, current status as string follows
 *				if done == 1, URL to the detail page follows
 *		'prob_best_solutions':
 *			best solutions for a problem
 *			value: problem id
 */

require_once $includes_path . 'record.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'judge.php';

if (isset($_POST['prob_submit']))
{
	if (!user_check_login())
		die('0not logged in');
	$where = array($DBOP['&&'],
		$DBOP['='], 'uid', $user->id,
		$DBOP['='], 'pid', $_POST['prob_submit']);
	db_where_add_and($where, record_make_where());
	$row = $db->select_from('records', array('id', 'status', 'mem'), $where,
		array('id' => 'DESC'), NULL, 1);
	if (count($row) != 1)
		die('0no such record');
	$s = $row[0]['status'];
	if (record_status_finished($s))
		die('1' . t_get_link('ajax-record-detail', $row[0]['id'], FALSE, TRUE));
	$cur_row = $row[0];
	die('0' . _cv_status());
}

if (isset($_POST['prob_best_solutions']))
{
	$_POST['filter']['pid'] = $_POST['prob_best_solutions'];
	$_POST['filter']['ranklist'] = 1;
	define('PAGE_SIZE', 5);
} else define('PAGE_SIZE', 10);


$pgnum = 0;

if (isset($_POST['goto_page']))
	$pgnum = intval($_POST['goto_page']) - 1;

if (!is_null($page_arg))
	$pgnum = intval($page_arg);
if ($pgnum < 0)
	$pgnum = 0;

$FILETER_ALLOWED = array('uid', 'pid', 'lid', 'status');

$where = NULL;
$select_cols = array(
	'id', 'uid', 'pid', 'jid', 'lid', 'src_len', 'status',
	'stime', 'score', 'full_score', 'time', 'mem'
);
$order_by = array('id' => 'DESC');

if (isset($_POST['filter']))
{
	$req = &$_POST['filter'];
	if (isset($req['username']) && strlen($req['username']))
		$req['uid'] = user_get_id_by_name($req['username']);
	if (isset($req['pcode']) && strlen($req['pcode']))
		$req['pid'] = prob_get_id_by_code($req['pcode']);
	if (isset($req['lid']) && !strlen($req['lid']))
		unset($req['lid']);
	if (isset($req['status']) && !strlen($req['status']))
		unset($req['status']);
	foreach ($FILETER_ALLOWED as $f)
		if (array_key_exists($f, $req))
			db_where_add_and($where, array($DBOP['='], $f, $req[$f]));
	if (isset($req['ranklist']))
	{
		$order_by = array('score' => 'DESC', 'time' => 'ASC',
			'mem' => 'ASC', 'src_len' => 'ASC', 'stime' => 'ASC');
		db_where_add_and($where, array($DBOP['!='], 'score', 0));
	}
}
else if (isset($_POST['request']))
{
	$req = $_POST['request'];
	if (is_array($req) && count($req) <= PAGE_SIZE)
	{
		foreach ($req as $id)
			db_where_add_or($where, array($DBOP['='], 'id', $id));
	}
}

db_where_add_and($where, record_make_where());

$rows = $db->select_from('records', $select_cols, $where,
	$order_by, $pgnum * PAGE_SIZE, PAGE_SIZE);

record_filter_rows($rows);

// cv: column value
function _cv_user()
{
	global $cur_row;
	$uid = $cur_row['uid'];
	return user_get_nickname_by_id($uid) .
		'<br />(' . user_get_username_by_id($uid) . ')';
}

function _cv_prob()
{
	global $cur_row;
	$pid = $cur_row['pid'];
	$code = prob_get_code_by_id($pid);
	return sprintf('<a href="%s" title="%s">%s</a>',
		t_get_link('problem', $code, TRUE, TRUE),
		__('Problem code: %s', $code),
		prob_get_title_by_id($pid));
}

function _cv_lang()
{
	global $cur_row;
	return plang_get_name_by_id($cur_row['lid']);
}

function _cv_status()
{
	global $cur_row, $RECORD_STATUS_TEXT;
	$s = intval($cur_row['status']);
	$str = $RECORD_STATUS_TEXT[$s];
	if (isset($_POST['prob_best_solutions']))
		return $str;
	if ($s == RECORD_STATUS_RUNNING)
		$str = "$str (" . $cur_row['mem'] . ')'; // see /install/tables.php
	if (!record_status_finished($s))
		return '<img src="' . _url('images/loading.gif', TRUE) . '" alt="loading" />' . $str;
	if ($s == RECORD_STATUS_ACCEPTED)
		$class = 'class="status-ac"';
	else if ($s == RECORD_STATUS_WRONG_ANSWER)
		$class = 'class="status-wa"';
	else if ($s == RECORD_STATUS_COMPILE_FAILURE)
		$class = 'class="status-ce"';
	else $class = '';
	return "<a name=\"status-detail\" $class href=\"" . t_get_link('ajax-record-detail', $cur_row['id'], TRUE, TRUE) .
		"\">$str</a>";
}

function _cv_score()
{
	global $cur_row;
	if (!record_status_executed($cur_row['status']))
		return '---';
	return $cur_row['score'];
}

function _cv_time()
{
	global $cur_row;
	if (!record_status_executed($cur_row['status']))
		return '---';
	$t = intval($cur_row['time']);
	return sprintf('%.6f', $t * 1e-6);
}

function _cv_mem()
{
	global $cur_row;
	if (!record_status_executed($cur_row['status']))
		return '---';
	return $cur_row['mem'];
}

function _cv_judge()
{
	global $cur_row;
	$name = judge_get_name_by_id($cur_row['jid']);
	if ($name === NULL)
		return '---';
	return $name;
}

function _cv_srclen()
{
	global $cur_row;
	$len = intval($cur_row['src_len']);
	if ($len == 0)
		return '---';
	if ($len < 1024)
		return "$len [b]";
	return sprintf('%.3f [kb]',  $len / 1024.0);
}

$_cv_rank_val = $pgnum * PAGE_SIZE;
function _cv_rank()
{
	global $_cv_rank_val;
	return ++ $_cv_rank_val;
}

function _cv_date()
{
	global $cur_row;
	return strftime('%a %b %d %H:%M:%S <br /> %Y %Z', $cur_row['stime']);
}

$cols = array(
	// <key> => array(<column name>, <function to generate value>)
	array(__('USER'), '_cv_user'),
	array(__('PROBLEM'), '_cv_prob'),
	array(__('STATUS'), '_cv_status'),
	array(__('LANG'), '_cv_lang'),
	array(__('SCORE'), '_cv_score'),
	array(__('TIME[sec]'), '_cv_time'),
	array(__('MEM[kb]'), '_cv_mem'),
	array(__('JUDGE'), '_cv_judge'),
	array(__('SRC LEN'), '_cv_srclen'),
	array(__('DATE'), '_cv_date')
);

if (isset($_POST['filter']['ranklist']))
{
	$cols[-1] = array(__('RANK'), '_cv_rank');
	unset($cols[7]);
}

if (isset($_POST['prob_best_solutions']))
	unset($cols[1]);

ksort($cols);

if (isset($_POST['request']))
{
	$ret = array();
	foreach ($rows as $cur_row)
	{
		if (record_status_finished($cur_row['status']) || is_null($cur_row))
			$cur = '1';
		else $cur = '0';
		if (is_null($cur_row))
			for ($i = count($cols); $i; $i --)
				$cur .= '<td>---</td>';
		else
			foreach ($cols as $col)
			{
				$func = $col[1];
				$cur .= '<td>' . $func() . '</td>';
			}
		$ret[(string)$cur_row['id']] = $cur;
	}
	die(json_encode($ret));
}

echo '
<div id="status-list" style="clear:both">
<table class="' . (isset($_POST['prob_best_solutions']) ? 'colorbox-table' : 'page-table' )
	. '" id="status-list-table">
<tr> ';

foreach ($cols as $col)
	echo "<th>$col[0]</th>";

echo ' </tr> ';

$records_unfinished = array();
foreach ($rows as $cur_row)
{
	$id = $cur_row['id'];
	echo "<tr id=\"status-tb-tr-$id\">";
	if (is_null($cur_row))
		for ($i = count($cols); $i; $i --)
			echo '<td>---</td>';
	else
	{
		foreach ($cols as $col)
		{
			$func = $col[1];
			echo '<td>' . $func() . '</td>';
		}
		if (!record_status_finished($cur_row['status']))
			$records_unfinished[] = "'$id'";
	}
	echo '</tr>';
}

echo '
</table>';

function make_a($text, $pg)
{
	printf('<a href="%s" onclick="status_navigate(\'%s\'); return false;">%s</a>',
		t_get_link('show-ajax-status-list', $pg, TRUE, TRUE),
		t_get_link('ajax-status-list', $pg, TRUE, TRUE),
		$text);
}

echo '<div id="status-list-navigate">';

if ($pgnum)
{
	echo '&lt;';
	make_a(__('Prev'), $pgnum - 1);
}
if (count($rows) == PAGE_SIZE)
{
	if ($pgnum)
		echo ' | ';
	make_a(__('Next'), $pgnum + 1);
	echo '&gt;';
}

$id = _tf_get_random_id();
echo '<form action="#" id="goto-page-form" method="post"
	onsubmit="status_goto_page(); return false;" ><label for="' . $id . '" style="float:left">';
echo __('Go to page:');
echo '</label><input type="text" value="' . ($pgnum + 1) . '" name="goto_page" id="' . $id . '" 
   style="width: 30px; float: left;" /></form>';

echo '</div> <!-- id: status-list-navigate -->
</div> <!-- id: status-list -->';

?>

<script type="text/javascript">

function status_navigate_do(addr, data)
{
	var t = $("#status-list");
	t.animate({"opacity": 0.5}, 1);
	$.ajax({
		"type": "post",
		"cache": false,
		"url": addr,
		"data": data,
		"success": function(data) {
			var t = $("#status-list");
			t.animate({"opacity": 1}, 1);
			t.html(data);
		}
	});
}

<?php
if (!isset($_POST['prob_best_solutions']))
{
?>
	function status_navigate(addr)
	{
		status_navigate_do(addr,
			$("#filter-form").serializeArray());
	}

	function status_goto_page()
	{
		status_navigate_do("<?php t_get_link('ajax-status-list');?>",
			$("#filter-form").serializeArray().concat(
				$("#goto-page-form").serializeArray()));
	}

	$("a[name='status-detail']").colorbox();
	table_set_double_bgcolor();
	start_update_table(new Array(<?php echo implode(',', $records_unfinished); ?>));

<?php
}
else
{
	$pid = $_POST['prob_best_solutions'];
	echo '

	function status_navigate(addr)
	{
		status_navigate_do(addr, {"prob_best_solutions": ' . $pid . '});
	}

	function status_goto_page()
	{
		status_navigate_do("' . t_get_link('ajax-status-list', NULL, FALSE, TRUE) . '",
			{
				"prob_best_solutions": ' . $pid . ',
				"goto_page": $("#goto-page-form input").val()
			})
	}

	';

}
?>

$("#goto-page-form").bind("submit", function(){
	status_goto_page();
	return false;
});

</script>
