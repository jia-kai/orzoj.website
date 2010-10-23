<?php
/*
 * $File: contest_view.php
 * $Date: Sat Oct 23 21:31:02 2010 +0800
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
	die(__('Failed to get contest information: %s', htmlencode($e->msg())));
}

echo '<div class="contest-view-content">';

if (isset($_POST['time']) && isset($_POST['sort_col']) && isset($_POST['sort_way']) && isset($_POST['page_num']))
{
	$have_back_to_list = TRUE;
	echo '<div style="float:left;"><button onclick="back_to_list()">' . __('Back to list') . '</button></div>';
}
else
	$have_back_to_list = FALSE;

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

echo '<div style="clear: both; float: left;">';
echo __('Start time: %s', time2str($ct->data['time_start'])) . '<br />';
echo __('End time: %s', time2str($ct->data['time_end'])) . '<br />';
echo __('Contest duration: %s', time_interval_to_str($ct->data['time_end'] - $ct->data['time_start'])) . '<br />';

echo '<div class="contest-countdown">';
if (time() < $ct->data['time_start'])
	echo __('The contest will start in %s', time_interval_to_str($ct->data['time_start'] - time())) . '<br />';
else if (time() < $ct->data['time_end'])
	echo __('The contest will end in %s', time_interval_to_str($ct->data['time_end'] - time())) . '<br />';
echo '</div>';

echo '</div>';


if (!$ct->view_prob_allowed())
	echo __('Sorry, you are not allowed to view problems in this contest now');
else
{
	$list = $ct->get_prob_list();
	echo '<table class="page-table">';
	echo '<tr>';
	foreach ($list[0] as $col)
		echo '<th>' . $col . '</th>';
	echo '</tr>';
	$ncol = count($list[0]);

	for ($i = 1; $i < count($list); $i ++)
	{
		echo '<tr>';
		$row = &$list[$i];
		for ($j = 0; $j < $ncol; $j ++)
			printf("<td><a href='%s' onclick='contest_view_prob(%d) return false;'>%s</a></td>",
				t_get_link('show-ajax-contest-view-prob', $row[$ncol], TRUE, TRUE),
				$row[$ncol],
				$row[$j]);
		echo '</tr>';
	}

	echo '</table>';
}

?>

</div> <!-- class: contest-view-content -->

<div style="clear:both">&nbsp;</div>
<!-- I need this div to make ui.tabs work properly?? -->

<script type="text/javascript">

function contest_view_prob(pid)
{
}

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
				$("#contest-list-<?php echo $_POST['time'];?>").html(data);
			}
		});
	}

<?php
}
?>
</script>

