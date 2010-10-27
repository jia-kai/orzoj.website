<?php
/*
 * $File: record_detail.php
 * $Date: Wed Oct 27 19:11:31 2010 +0800
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

/**
 * page argument: <record id:int>
 */

if (!is_string($page_arg))
	die('no argument');

require_once $includes_path . 'exe_status.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'judge.php';
require_once $includes_path . 'record.php';
require_once $includes_path . 'contest/ctal.php';
require_once $root_path . 'contents/highlighters/geshi/geshi.php';

// whether to display realname and submission IP
$disp_uinfo = user_check_login() && ($user->is_grp_member(GID_UINFO_VIEWER) ||
	$user->is_grp_member(GID_ADMIN_USER));

// fd: field
function _fd_user()
{
	global $row, $disp_uinfo;
	$uid = $row['uid'];
	$str = user_get_nickname_by_id($uid) .
		'(' . user_get_username_by_id($uid) . ')';
	if ($disp_uinfo)
		$str .= ' (' . __('real name: %s', user_get_realname_by_id($uid)) . ') ';
	echo __('User: %s', '<a class="record-detail-colorbox" href="' .
		t_get_link('ajax-user-info', $uid, TRUE, TRUE) . '">' . $str . '</a>');
}

function _fd_prob()
{
	global $row;
	$pid = $row['pid'];
	$code = prob_get_code_by_id($pid);
	echo __('Problem: %s (CODE: %s)', '<a target="_blank" href="' .
		t_get_link('problem', $code, TRUE, TRUE) .
		'">' . prob_get_title_by_id($pid) .
		'</a>', $code);
}

function _fd_lang()
{
	global $row;
	echo __('Programming language: %s', plang_get_name_by_id($row['lid']));
}

function _fd_contest()
{
	global $row;
	if ($row['cid'] == 0)
		$str = __('None');
	else
		$str = '<a target="_blank" href="' . t_get_link('contest', $row['cid'], TRUE, TRUE) . '">' .
			ct_get_name_by_id($row['cid']) . '</a>';

	echo __('Contest: %s', $str);
}

function _fd_status()
{
	global $row;
	echo __('Status: %s', record_status_get_str($row['status']));
}

function _fd_score()
{
	global $row;
	if (!record_status_executed($row['status']))
		$str = '---';
	else
		$str = $row['score'];
	echo __('Score: %s', $str);
}

function _fd_time()
{
	global $row;
	if (!record_status_executed($row['status']))
		$t = '---';
	else
	{
		$t = intval($row['time']);
		$t = __('%.6lf [sec]', $t);
	}
	echo __('Total CPU time: %s', $t);
}

function _fd_mem()
{
	global $row;
	if (!record_status_executed($row['status']))
		$str = '---';
	else $str = __('%d [kb]', $row['mem']);
	echo __('Maximal memory: %s', $str);
}

function _fd_judge()
{
	global $row;
	$name = judge_get_name_by_id($row['jid']);
	if ($name === NULL)
		$name = '---';
	echo __('Judge: %s', $name);
}

function _fd_srclen()
{
	global $row;
	$len = intval($row['src_len']);
	if ($len == 0)
		$str = '---';
	else if ($len < 1024)
		$str = __("%d [b]", $len);
	else $str = __('%.3f [kb]',  $len / 1024.0);

	echo __('Source code length: %s', $str);
}


function _fd_stime()
{
	global $row;
	echo __('When submitted: %s', time2str($row['stime']));
}

function _fd_jtime()
{
	global $row;
	$t = intval($row['jtime']);
	if ($t)
		$t = time2str($t);
	else $t = '---';
	echo __('When judged: %s', $t);
}

function _fd_ip()
{
	global $row;
	echo __('Submission ip: %s', $row['ip']);
}

function _fd_detail_exe_status($res)
{
	return exests_get_str($res->exe_status);
}

function _fd_detail_score($res)
{
	return $res->score . '/' . $res->full_score;
}

function _fd_detail_time($res)
{
	return sprintf('%.6f', $res->time * 1e-6);
}

function _fd_detail_mem($res)
{
	return $res->memory;
}

function _fd_detail_extra_info($res)
{
	if (strlen($res->extra_info))
		return htmlencode($res->extra_info);
	return '---';
}

function _fd_detail()
{
	global $row;
	echo __('Details:') . '<br />';
	$detail = $row['detail'];
	if (!record_status_executed($row['status']))
		echo '<div id="record-detail">' . htmlencode($detail) . '</div>';
	else
	{
		$details = case_result_array_decode($detail);
		$cols = array(
			__('CASE') => '',
			__('STATUS') => '_fd_detail_exe_status',
			__('SCORE') => '_fd_detail_score',
			__('TIME[sec]') => '_fd_detail_time',
			__('MEMORY[kb]') => '_fd_detail_mem',
			__('Extra infomation') => '_fd_detail_extra_info'
		);

		echo '<table class="colorbox-table" style="min-width:600px; max-width:650px;">
			<tr>';
		foreach ($cols as $col => $func)
			echo '<th>' . $col . '</th>';
		echo '</tr>';
		$idx = 0;
		foreach ($details as $detail)
		{
			$idx ++;
			echo '<tr>';
			foreach ($cols as $col => $func)
			{
				echo '<td>';
				if ($func == '')
					echo $idx;
				else echo $func($detail);
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}
}

function _fd_src()
{
	global $row, $db, $DBOP, $page_arg, $plang_type;
	$src = $db->select_from('sources', 'src', array(
		$DBOP['='], 'rid', $page_arg));
	if (count($src) == 1)
		$src = $src[0]['src'];
	else $src = __('not found');
	//TODO: retrieve source from orzoj-server
	printf('<a href="%s">%s</a><br />', t_get_src_download_url($page_arg), __('Source:'));
	$geshi = new GeSHi($src, $plang_type);
	$geshi->set_header_type(GESHI_HEADER_PRE_TABLE);
	$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
	$geshi->enable_classes();
	$geshi->set_tab_width(4);
	echo '<div id=record-source>' . $geshi->parse_code() . '</div>';
}

$cols = array(
	'uid' => '_fd_user',
	'pid' => '_fd_prob',
	'jid' => '_fd_judge',
	'lid' => '_fd_lang',
	'cid' => '_fd_contest',
	'src_len' => '_fd_srclen',
	'status' => '_fd_status',
	'stime' => '_fd_stime',
	'jtime' => '_fd_jtime',
	'ip' => '_fd_ip',
	'detail' => '_fd_detail'
);

$where = array($DBOP['='], 'id', $page_arg);
db_where_add_and($where, record_make_where());

$row = $db->select_from('records', array_keys($cols),
	$where);

if (count($row) != 1)
	die('no such record');

record_filter_rows($row);


if (count($row) != 1 || is_null($row[0]))
	die('no such record');

$row = $row[0];

if (!$disp_uinfo)
	$disp_uinfo = user_check_login() && $user->id == $row['uid'];

if (!$disp_uinfo)
	unset($cols['ip']);

if ($row['status'] == RECORD_STATUS_WAITING_FOR_CONTEST)
	foreach (array('jid', 'jtime', 'detail') as $f)
		unset($cols[$f]);

$plang_type = plang_get_type_by_id($row['lid']);

if (record_allow_view_src($row['uid'], $row['cid']))
	$cols['src'] = '_fd_src';

foreach ($cols as $col => $func)
{
	$func();
	echo '<br />';
}

?>

<script type="text/javascript">
load_js_css_file("<?php echo get_page_url($root_path . "contents/highlighters/geshi/geshi-styles/$plang_type.css");?>", 'css');
$(".record-detail-colorbox").colorbox();
</script>

