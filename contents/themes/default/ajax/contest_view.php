<?php
/*
 * $File: contest_view.php
 * $Date: Fri Jan 06 21:24:14 2012 +0800
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

require_once $includes_path . 'contest/ctal.php';

/*
 * page argument: none
 *
 * POST:
 *		id: int  contest id
 *		optional:
 *			time, sort_col, sort_way, page_num: used for back to list
 *			back_to_list: encoded array of variables used for going back to list
 *
 */

if (!isset($_POST['id']))
	die('which contest do you want?');

$cid = intval($_POST['id']);

try
{
	$ct = ctal_get_class_by_cid($cid);
}
catch (Exc_orzoj $e)
{
	echo __('Failed to get contest information: %s', htmlencode($e->msg()));
	echo '</div>';
	return;
}

$html_id_page = get_random_id();

echo "<div id='$html_id_page'>";

echo '<div class="contest-view-content">';

$back_to_list_var = array('time', 'sort_col', 'sort_way', 'page_num');
$have_back_to_list = TRUE;

if (isset($_POST['back_to_list']))
{
	$tmp = explode('|', $_POST['back_to_list']);
	if (count($tmp) != count($back_to_list_var))
		$have_back_to_list = FALSE;
	else
	{
		$idx = 0;
		foreach ($back_to_list_var as $v)
			$_POST[$v] = $tmp[$idx ++];
		unset($idx);
	}
	unset($tmp);
}
else
{
	foreach ($back_to_list_var as $v)
		if (!isset($_POST[$v]))
		{
			$have_back_to_list = FALSE;
			break;
		}
}

if ($have_back_to_list)
{
	echo '<div style="float:left;"><button onclick="back_to_list()">' . __('Back to list') . '</button></div>';

	$tmp = array();
	foreach ($back_to_list_var as $v)
		$tmp[$v] = $_POST[$v];
	$back_to_list_encoded = implode('|', $tmp);
	unset($tmp);
}

echo '<div style="float: right;"><a onclick="refresh(); return false;" title="' . __('Refresh') .
	'" href="';
t_get_link('contest', $cid);
echo '"><img alt="refresh" src="';
_url('images/refresh.gif');
echo '" /></a></div>';

echo '<div class="contest-name">' . $ct->data['name'] . '</div>';

echo '<div style="clear: both; float: left;">';
echo __('Contest ID: %d', $ct->data['id']) . '<br />';
echo __('Contest type: %s', ctal_get_typename_by_type($ct->data['type'])) . '<br />';
echo '</div>
	<div style="clear: both; float: left;">';
echo __('Contest description:');
echo '</div>';

echo '<div class="contest-description">';
echo $ct->data['desc'];
echo '</div>';

$ct_time_start = intval($ct->data['time_start']);
$ct_time_end = intval($ct->data['time_end']);

echo '<div style="clear: both; float: left;">';
echo __('Start time: %s', time2str($ct_time_start)) . '<br />';
echo __('End time: %s', time2str($ct_time_end)) . '<br />';
echo __('Contest duration: %s', time_interval_to_str($ct_time_end - $ct_time_start)) . '<br />';


$html_id_countdown = get_random_id();
$now = time();

echo '<div class="contest-result">';
if ($now < $ct_time_start)
{
	$count_down_len = $ct_time_start - $now;
	echo __('The contest will start in %s',
		"<span id='$html_id_countdown' class='contest-countdown'>" .
			time_interval_to_str($count_down_len) . '</span>');
}
else if ($now < $ct_time_end)
{
	$count_down_len = $ct_time_end - $now;
	echo __('The contest will end in %s',
		"<span id='$html_id_countdown' class='contest-countdown'>" .
			time_interval_to_str($count_down_len) . '</span>');
}
else
{
	if ($ct->result_is_ready())
		printf('<a onclick="view_result(); return false;" href="%s">%s</a>',
			t_get_link('show-ajax-contest-view-result', $cid, TRUE, TRUE),
			__('View contest result'));
	else
		echo __('Please wait for a few minutes to see the contest result. (You can go to the status page to see judge progress)');
}
echo '</div>';

echo '</div>';

echo '<div style="clear: both; text-align: center; margin-top: 100px;">';


if (!$ct->allow_view_prob())
	echo __('Sorry, you are not allowed to view problems in this contest now');
else
{
	echo __('Problem list') . '<br />';
	$list = $ct->get_prob_list();
	$ncol = count($list[0]) - 1;
	$col_link = intval($list[0][$ncol]);

	echo '<table class="page-table">';
	echo '<tr>';
	for ($i = 0; $i < $ncol; $i ++)
		echo '<th>' . $list[0][$i] . '</th>';
	echo '</tr>';

	for ($i = 1; $i < count($list); $i ++)
	{
		echo '<tr>';
		$row = &$list[$i];
		if (is_null($row))
			for ($j = $ncol; $j; $j --)
				echo '<td>---</td>';
		else
		{
			for ($j = 0; $j < $ncol; $j ++)
			{
				if ($j == $col_link)
					printf('<td style="text-align: left"><a href="%s" onclick="contest_view_prob(\'%d\'); return false;">%s</a></td>',
						t_get_link('show-ajax-contest-view-prob', $row[$ncol] . '|' . $cid, TRUE, TRUE),
						$row[$ncol],
						$row[$j]);
				else
					echo '<td>' . $row[$j] . '</td>';
			}
		}
		unset($row);
		echo '</tr>';
	}

	echo '</table>';
}

echo '</div>';

?>

</div> <!-- class: contest-view-content -->

<div style="clear:both">&nbsp;</div>
<!-- I need this div to make ui.tabs work properly?? -->

</div>

<script type="text/javascript">

table_set_double_bgcolor();

function replace_this_page(data)
{
	$("#<?php echo $html_id_page;?>").parent().html(data);
}

function contest_view_prob(pid)
{
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-view-prob', NULL, FALSE);?>",
		"data": ({"arg": pid + "|<?php echo $cid;?>"
			<?php if ($have_back_to_list) echo ", 'back_to_list': '$back_to_list_encoded'"; ?>
		}),
		"success": function(data) {
			replace_this_page(data);
		}
	});
}

function refresh()
{
	//console.log("refresh() executed");
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-view', NULL, FALSE);?>",
		"data": ({"id": <?php echo $cid;
		if ($have_back_to_list)
			echo ", 'back_to_list': '$back_to_list_encoded'";
?>}),
		"success": function(data) {
			replace_this_page(data);
		}
	});
}

<?php
if (isset($count_down_len))
{
	echo time_interval_to_str_gen_js('time_interval_to_str');
?>

	function count_down()
	{
		//console.log("count_down() executed");
		var d = new Date;
		if (typeof(count_down.time_end) == 'undefined')
			count_down.time_end = d.getTime() + <?php echo $count_down_len;?> * 1000;
		len = Math.floor((count_down.time_end - d.getTime()) / 1000);
		if (len < 0)
		{
			refresh();
			return;
		}
		t = $("#<?php echo $html_id_countdown;?>");
		if (t.length)
		{
			t.html(time_interval_to_str(len));
			setTimeout("count_down()", 500);
		}
	}

	count_down();


<?php
}
?>

<?php
if ($have_back_to_list)
{
?>

	$("button").button();

	function back_to_list()
	{
		$.ajax({
			"type": "post",
			"cache": false,
			"url": "<?php t_get_link('ajax-contest-list', $_POST['time'], FALSE);?>",
			"data": ({
				<?php echo "'sort_col': '$_POST[sort_col]', 'sort_way': '$_POST[sort_way]', 'page_num': '$_POST[page_num]'";?>}),
			"success": function(data) {
				replace_this_page(data);
			}
		});
	}

<?php
}
if ($ct->result_is_ready() && $ct->allow_view_result())
{
?>

	function view_result()
	{
		$.ajax({
			"type": "post",
			"cache": false,
			"url": "<?php t_get_link('ajax-contest-view-result', $cid, FALSE);?>",
<?php
			if ($have_back_to_list)
				echo "'data': ({'back_to_list': '$back_to_list_encoded'}),\n";
?>
			"success": function(data) {
				replace_this_page(data);
			}
		});
	}

<?php
}
?>
</script>

