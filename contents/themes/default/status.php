<?php
/*
 * $File: status.php
 * $Date: Thu Oct 14 09:45:22 2010 +0800
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

require_once $includes_path . 'record.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'judge.php';

define('PAGE_SIZE', 15);

echo '<div class="status-filter" style="margin-right: 10px">';
echo __('Filter:');
echo '</div>';
echo '<form action="';
t_get_link($cur_page, $page_arg);
echo '" method="post">';

function _make_input($prompt, $post_name)
{
	if (isset($_POST['filter'][$post_name]))
		$default = $_POST['filter'][$post_name];
	else $default = '';
	$id = _tf_get_random_id();
	echo <<<EOF
<div class="status-filter">
<label for="$id">$prompt</label>
</div>
<div class="status-filter">
<input type="text" name="filter[$post_name]" id="$id" value="$default" />
</div>
EOF;
}

_make_input(__('username'), 'username');
_make_input(__('problem code'), 'pcode');

function _make_select($prompt, $post_name, $options)
{
	if (isset($_POST['filter'][$post_name]))
		$default = $_POST['filter'][$post_name];
	else $default = '';
	$id = _tf_get_random_id();
	echo <<<EOF
<div class="status-filter">
<label for="$id">$prompt</label>
</div>
<div class="status-filter">
<select id="$id" name="filter[$post_name]">
EOF;

	foreach ($options as $disp => $val)
	{
		echo "<option value=\"$val\"";
		if ($val === $default)
			echo ' selected="selected" ';
		echo ">$disp</option>";
	}
	echo '</select></div>';
}

$rows = $db->select_from('plang', array('id', 'name'));

$plang = array(__('ALL') => '');
foreach ($rows as $row)
	$plang[$row['name']] = $row['id'];

_make_select(__('lang.'), 'plang', $plang);
_make_select(__('status'), 'status', array_merge(array('ALL' => ''),
	array_flip($RECORD_STATUS_TEXT)));

$p = __('Apply');
echo "
	<div class=\"status-filter\">
	<input type=\"submit\" id=\"filter-apply-button\" value=\"$p\" />
	</div>";

echo '</form>';

$pgnum = 0;
if (!is_null($page_arg))
{
	$pgnum = intval($page_arg);
	if ($pgnum < 0)
		$pgnum = 0;
}

$FILETER_ALLOWED = array('uid', 'pid', 'lid', 'status');

$where = NULL;

function _where_and($new)
{
	global $where, $DBOP;
	if ($where == NULL)
		$where = $new;
	else $where = array_merge(array($DBOP['&&']), $where, $new);
}

if (isset($_POST['filter']))
{
	$req = &$_POST['filter'];
	if (isset($req['username']))
		$req['uid'] = user_get_id_by_name($req['username']);
	if (isset($req['pcode']))
		$req['pid'] = prob_get_id_by_code($req['pcode']);
	foreach ($FILETER_ALLOWED as $f)
		if (isset($req[$f]) && strlen($req[$f]))
			_where_and(array($DBOP['='], $f, $req[$f]));
}

if (!user_check_login())
	_where_and(array($DBOP['!='], 'status', RECORD_STATUS_WAITING_FOR_CONTEST));
else if (!$user->is_grp_member(GID_SUPER_RECORD_VIEWER))
	_where_and(array($DBOP['||'],
		$DBOP['!='], 'status', RECORD_STATUS_WAITING_FOR_CONTEST,
		$DBOP['='], 'uid', $user->id));

$rows = $db->select_from('records', array(
	'id', 'uid', 'pid', 'jid', 'lid', 'src_len', 'status',
	'stime', 'score', 'full_score', 'time', 'mem'
	), NULL, array('stime' => 'DESC'), $pgnum * PAGE_SIZE, PAGE_SIZE);


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
	return sprintf('<a href="%s">%s</a>',
		t_get_link('problem', prob_get_code_by_id($pid), TRUE, TRUE),
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
	if (!record_status_finished($s))
		return $str;
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


function _cv_date()
{
	global $cur_row;
	return strftime('%a %b %d %H:%M:%S <br /> %Y %Z', $cur_row['stime']);
}

$cols = array(
	// <column name> => <function to generate value>
	__('USER') => '_cv_user',
	__('PROBLEM') => '_cv_prob',
	__('STATUS') => '_cv_status',
	__('LANG') => '_cv_lang',
	__('SCORE') => '_cv_score',
	__('TIME[sec]') => '_cv_time',
	__('MEM[kb]') => '_cv_mem',
	__('JUDGE') => '_cv_judge',
	__('SRC LEN') => '_cv_srclen',
	__('DATE') => '_cv_date'
);

?>

<table class="orzoj-table">
<tr>
<?php
foreach ($cols as $name => $func)
	echo "<th>$name</th>";
?>
</tr>
<?php
foreach ($rows as $cur_row)
{
	echo '<tr>';
	foreach ($cols as $func)
		echo '<td>' . $func() . '</td>';
	echo '</tr>';
}
?>
</table>

<script type="text/javascript">
$("#filter-apply-button").button();
$("a[name='status-detail']").colorbox();
</script>

