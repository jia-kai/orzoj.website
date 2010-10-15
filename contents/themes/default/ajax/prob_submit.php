<?php
/*
 * $File: prob_submit.php
 * $Date: Fri Oct 15 14:44:12 2010 +0800
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
	die("Hello? What are you doing?");

require_once $includes_path . 'submit.php';
if ($page_arg == 'submit')
{
	try
	{
		submit_src();
		$html = '0';
		$html .= __('Submittion success!') . '<br />';
		$html .= __('You will be redirected to Status page in 2 seconds ...');
		die($html);
	}
	catch (Exc_orzoj $e)
	{
		die('1' . __('Submittion failed: ') . $e->msg());
	}
}

$pid = 0;
if (sscanf($page_arg, "%d", $pid) != 1)
	die(__("Eh? What do want me to do?"));

if (!user_check_login())
	die(__("Please login first."));

require_once $includes_path . 'submit.php';
?>

<form action="#" id="submit-form">
<?php _tf_form_generate_body('submit_src_get_form', $pid); ?>
<div style="text-align: right">
	<button id="submit-button" type="submit" class="in-form"><?php echo __("Good Luck^ ^"); ?></button>
</div>
</form>

<script type="text/javascript">
$("button").button();
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
				$.colorbox({"html": data.substr(1)});
				setTimeout("window.location='<?php t_get_link('status', NULL, FALSE); ?>';", 2000);
			}
		}
	});
	return false;
});
</script>

