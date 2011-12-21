<?php
/*
 * $File: prob_submit.php
 * $Date: Wed Dec 21 16:59:06 2011 +0800
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
 * page argument:
 *		[<pid: int>]
 *			the id of a problem which is to submit
 *		'submit' : inner argument
 */

if (!is_string($page_arg))
	die("Hello? What are you doing?");

require_once $includes_path . 'submit.php';
if ($page_arg == 'submit')
{
	try
	{
		submit_src();
		$html = '0';
		$html .= __('Successful submission!') . '<br />';
		die($html);
	}
	catch (Exc_orzoj $e)
	{
		die('1' . __('Failed to submit: %s', $e->msg()));
	}
}

$pid = 0;
if (sscanf($page_arg, "%d", $pid) != 1)
	die(__("Sir, what can I do for you?"));

if (!user_check_login())
	die(__("Please login first."));
?>

<form action="<?php t_get_link($cur_page, 'submit');?>" id="submit-form" method="post">
<?php _tf_form_generate_body('submit_src_get_form', $pid); ?>
<div style="text-align: right">
	<button id="submit-button" type="submit" class="in-form"><?php echo __("Good Luck^ ^"); ?></button>
</div>
</form>

<script type="text/javascript">

$("button").button();

var is_closed = false,	// whether the user closed ths box
	cur_id = 0;			// id for the timer

function show_running_status()
{
	$.ajax({
		"type" : "post",
		"cache" : false,
		"url" : "<?php t_get_link('ajax-status-list'); ?>",
		"data" : ({"prob_submit" : <?php echo $pid; ?>}),
		"success" : function(data) {
			if (data.charAt(0) == '0')
			{
				$.colorbox({
					"html": "<div style='text-align: center;'>" + data.substr(1) + "</div>",
					"width": "300px",
					"onClosed" : function(){
						is_closed = true;
						if (cur_id != -1)
							clearTimeout(cur_id);
					}
				});
				if (!is_closed)
					cur_id = setTimeout("show_running_status();", 1000);
			}
			else
			{
				$.colorbox({
					"href" : data.substr(1)
				});
				$("#prob-submit-box").css("word-break", "break-all");
			}
		}
	});
}

$("#submit-form").bind("submit", function(){
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link($cur_page, 'submit', FALSE); ?>",
		"data": $("#submit-form").serializeArray(),
		"success" : function(data) {
			if (data.charAt(0) == '1')
				alert(data.substr(1));
			else
			{
				cur_id = -1;
				is_closed = false;
				$.colorbox({
					"html": data.substr(1),
					"title" : "<?php echo __("Good luck"); ?>",
					"onClosed" : function(){
						is_closed = true;
						if (cur_id != -1)
							clearTimeout(cur_id);
					}
				});
				if (!is_closed)
					cur_id = setTimeout("show_running_status()", 1000);
			}
		}
	});
	return false;
});
</script>

