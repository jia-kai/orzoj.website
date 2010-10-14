<?php
/*
 * $File: record_detail.php
 * $Date: Thu Oct 14 08:32:45 2010 +0800
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

if (!is_string($page_arg))
	die('no argument');

require_once $includes_path . 'exe_status.php';
require_once $includes_path . 'problem.php';

// whether to display realname and submission IP
$disp_info = user_check_login() && $user->is_grp_member(GID_UINFO_VIEWER);

// fd: field
function _fd_user()
{
	global $row, $disp_info;
	$uid = $row['uid'];
	$str = user_get_nickname_by_id($uid) .
		'(' . user_get_username_by_id($uid) . ')';
	if ($disp_info)
		$str .= ' (' . __('real name: %s', user_get_realname_by_id($uid)) . ') ';
	echo __('User: %s', $str);
}

function _fd_prob()
{
	global $row;
	$pid = $row['pid'];
	echo __('Problem: <a href="%s">%s</a>', 
		t_get_link('problem', prob_get_code_by_id($pid), TRUE, TRUE),
		prob_get_title_by_id($pid));
}

function _fd_lang()
{
	global $row;
	echo __('Programming language: %s', plang_get_name_by_id($row['lid']));
}

function _fd_status()
{
	global $row, $RECORD_STATUS_TEXT;
	echo __('Status: %s', $RECORD_STATUS_TEXT[intval($s)]);
}

function _fd_score()
{
	global $row;
	if (!record_status_executed($row['status']))
		$str = '---';
	else
	{
		$s = intval($row['score']);
		$f = intval($row['full_score']);
		$str = __("%d (full score: %d)", $s, $f);
	}
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
	echo __('Submission time: %s', strftime('%a %b %d %H:%M:%S %Y %Z', $row['stime']));
}

function _fd_jtime()
{
	global $row;
	$t = intval($row['jtime']);
	if ($t)
		$t = strftime('%a %b %d %H:%M:%S %Y %Z', $t);
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
	global $EXECUTION_STATUS_TEXT;
	return $EXECUTION_STATUS_TEXT[intval($res->exe_status)];
}

function _fd_detail_score($res)
{
	return $res->score;
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
		return $res->extra_info;
	return '---';
}

function _fd_detail()
{
	global $row;
	echo __('Details:') . '<br />';
	$detail = $row['detail'];
	if (!record_status_executed($row['status']))
		echo $detail;
	else
	{
		$details = unserialize($detail);
		$cols = array(
			__('CASE') => '',
			__('STATUS') => '_fd_detail_exe_status',
			__('SCORE') => '_fd_detail_score',
			__('TIME[sec]') => '_fd_detail_time',
			__('MEMORY[kb]') => '_fd_detail_mem',
			__('Extra infomation') => '_fd_detail_extra_info'
		);

		echo '<table id="record-detail">
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
	global $row, $db, $DBOP;
	$src = $db->select_from('sources', 'src', array(
		$DBOP['='], 'rid', $row['id']));
	if ($count($src) == 1)
		$src = $src[0]['src'];
	else $src = __('Unavailable');
	//TODO: retrieve source from orzoj-server
	echo __('Source: <br /><div id="record-source">%s</div>',
		htmlencode($src));
}

$cols = array(
	'uid' => '_fd_user',
	'pid' => '_fd_prob',
	'jid' => '_fd_judge',
	'lid' => '_fd_lang',
	'src_len' => '_fd_srclen',
	'status' => '_fd_status',
	'stime' => '_fd_stime',
	'jtime' => '_fd_jtime',
	'ip' => '_fd_ip',
	'detail' => '_fd_detail'
);

if (!$disp_info)
	unset($cols['ip']);


$row = $db->select_from('records', array_keys($cols),
	array($DBOP['='], 'id', $page_arg));

if (count($row) != 1)
	die('no such record');

$row = $row[0];

if (user_check_login() && ($user->id == $row['uid'] || $user->is_grp_member(GID_SUPER_RECORD_VIEWER)))
	$cols['src'] = '_fd_src';

foreach ($cols as $col => $func)
{
	$func();
	echo '<br />';
}

