<?php
/*
 * $File: judge.php
 * $Date: Sun Oct 24 21:28:48 2010 +0800
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
	$st = '<table class="page-table">';
	foreach ($judge->detail as $key => $v)
		$st .= "<tr><td>$key</td><td>".htmlencode($v).'</td></tr>';
	$st .= '</table>';
	return $st;
}

$now = time2str(time());
echo '<br/>';
$aJudgeList = judge_get_list();
if (count($aJudgeList) == 1)
	echo __('There is only ONE judge');
else if (count($aJudgeList) < 1)
	echo __('Oops! There isn\'t any judge at all');
else
	echo __('There are %d judges',count($aJudgeList));
echo '<br/><br/>';

$judge_status_array = array(JUDGE_STATUS_OFFLINE => __('<font color="#FF0000">OFFLINE</font>'), JUDGE_STATUS_ONLINE => __('<font color="#00FF00">ONLINE</font>'));
?>
<?php
foreach ($aJudgeList as $judge)
{
?>
<div style="width:800px;">
<div id="preview_<?php echo $judge->id;?>"><a href="#"><?php echo __('Judge ID: %d | Status : %s',$judge->id,$judge_status_array[$judge->status]);?></a></div>
<table id="judge_<?php echo $judge->id;?>" class="page-table">
<?php
echo '<tr><td width="30">', __('Judge ID'), '</td><td>', $judge->id, '</td></tr>';
echo '<tr><td>', __('Judge Name'), '</td><td>', htmlencode($judge->name), '</td></tr>';
echo '<tr><Td>', __('Status'), '</td><td>' , $judge_status_array[$judge->status] , '</td></tr>';
echo '<tr><td>', __('Supported Language'), '</td><td>', _judge_supported_languages($judge), '</td></tr>';
echo '<tr><td>', __('Detail'), '</td><td>', _judge_detail($judge) , '</td></tr>';
EOF;
?>
</table>
</div>
<Br/>
<?php
}
echo '<br/><br/>';
echo __('This judge list was generated at %s and we don\'t guarantee it a realtime status.',$now);
?>
<script type="text/javascript">
table_set_double_bgcolor();
<?php
foreach ($aJudgeList as $judge)
{
?>
$("#judge_<?php echo $judge->id;?>").slideUp("slow");
$("#preview_<?php echo $judge->id;?>").click(function() {
	$("#judge_<?php echo $judge->id;?>").slideToggle("slow");
})
<?php
}
?>
//$(document).ready(function(){$(".a").click(function(){$(".b").slideUp('slow')});$(".a,.c").click(function(){$(this).next().slideToggle('slow')})})
</script>
<?php
