<?php
/*
 * $File: contest_view_prob.php
 * $Date: Tue Oct 26 14:59:10 2010 +0800
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
require_once $includes_path . 'problem.php';

/*
 * page argument: <problem id:int>|<contest id:int>
 *
 * POST:
 *		arg: string, used to replace page_arg
 *		[back_to_list]: posted to ajax-contest-view for going back to contest list
 *
 * Note: the id should be passed either by page argument or POST method
 */

if (isset($_POST['arg']))
	$page_arg = $_POST['arg'];

if (sscanf($page_arg, '%d|%d', $pid, $cid) != 2)
	die('invalid argument');

$html_id_page = _tf_get_random_id();

echo "<div id='$html_id_page'>";

echo '<div style="clear: both; float: left;"><a class="contest-button-a" href="';
t_get_link('contest', $cid);
echo '" onclick="back_to_contest(); return false;">' .
	__('Back to contest') . '</a>
	<a class="contest-button-a" id="contest-submit-a" href="';
t_get_link('show-ajax-contest-submit', $pid);
echo '">' . __('Submit') . '</a>
	</div>';

?>

<script type="text/javascript">
$("#contest-submit-a").attr("href", "<?php t_get_link('ajax-contest-submit', $pid, FALSE);?>");
$("#contest-submit-a").colorbox();
$(".contest-button-a").button();

function back_to_contest()
{
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-view');?>",
		"data": ({"id": <?php echo $cid;
			if (isset($_POST['back_to_list']))
				echo ', "back_to_list": "' . $_POST['back_to_list'] . '"';
		?>}),
		"success": function (data) {
			$("#<?php echo $html_id_page;?>").parent().html(data);
		}
	});
}
</script>

<div style="clear: both;">

<?php
$fcid = prob_future_contest($pid);
if (!is_null($fcid) && $fcid != $cid)
	echo __('Sorry, this problem belongs to a future contest #%d and you are not alloed to view it now',
		$fcid);
else
{
	try
	{
		echo prob_view($pid);
	} catch (Exc_orzoj $e)
	{
		echo __('Failed to view problem: %s', htmlencode($e->msg()));
	}
}
?>

</div>

</div>
