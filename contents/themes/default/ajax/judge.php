<?php
/*
 * $File: judge.php
 * $Date: Tue Nov 02 11:02:48 2010 +0800
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

$page_arg = intval($page_arg);
if ($page_arg < 0) 
	exit;

require_once $includes_path . 'judge.php';

function _judge_supported_languages($judge)
{
	$st = '';
	foreach ($judge->lang_sup as $lang)
		$st .= htmlencode($lang).'<br/>';
	return $st;
}

function _judge_detail($judge)
{
	$st = '<table class="colorbox-table">';
	foreach ($judge->detail as $key => $v)
		$st .= "<tr><td>$key</td><td style='text-align: left'>".htmlencode($v).'</td></tr>';
	$st .= '</table>';
	return $st;
}

$now = time2str(time());
echo '<br/>';
$aJudgeList = judge_get_list($page_arg);
$judge_status_array = array(JUDGE_STATUS_OFFLINE => __('<font color="#FF0000">OFFLINE</font>'), JUDGE_STATUS_ONLINE => __('<font color="#00FF00">ONLINE</font>'));
?>
<?php
foreach ($aJudgeList as $judge)
{
?>
<div style="width:800px;">
<table class="colorbox-table">
<?php
echo '<tr><td>', __('Judge ID'), '</td><td>', $judge->id, '</td></tr>';
echo '<tr><td>', __('Judge Name'), '</td><td>', htmlencode($judge->name), '</td></tr>';
echo '<tr><Td>', __('Status'), '</td><td>' , $judge_status_array[$judge->status] , '</td></tr>';
echo '<tr><td>', __('Supported Language'), '</td><td>', _judge_supported_languages($judge), '</td></tr>';
echo '<tr><td>', __('Detail'), '</td><td>', _judge_detail($judge) , '</td></tr>';
?>
</table>
</div>
<br />
<?php
}
echo '<br /><br />';
echo __('This judge status table was generated at %s and we don\'t guarantee it a realtime status.',$now);
?>
