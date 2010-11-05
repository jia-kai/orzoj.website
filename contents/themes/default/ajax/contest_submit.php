<?php
/*
 * $File: contest_submit.php
 * $Date: Fri Nov 05 09:49:58 2010 +0800
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
 * page argument: <problem id:int>|"do"
 */

if (!is_string($page_arg) || empty($page_arg))
	die('give me the problem id, please');

if (!user_check_login())
	die(__('Please login first.'));

require_once $includes_path . 'submit.php';

if ($page_arg == 'do')
{
	try
	{
		submit_src();
		die('0' . __('Successful submission!'));
	} catch (Exc_orzoj $e)
	{
		die('1' . __('Failed to submit: %s', $e->msg()));
	}
}
?>

<form action="<?php t_get_link('show-ajax-contest-submit', 'do');?>" method="post" id="contest-submit-form">
<?php  _tf_form_generate_body('submit_src_get_form', intval($page_arg)); ?>
<div style="text-align: right">
	<button type="submit" class="in-form"><?php echo __("Good Luck^ ^"); ?></button>
</div>
</form>

<script type="text/javascript">
$("button").button();
$("#contest-submit-form").bind("submit", function(){
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-submit', 'do', FALSE);?>",
		"data": $("#contest-submit-form").serializeArray(),
		"success": function(data) {
			if (data.charAt(0) == "1")
				alert(data.substr(1));
			else
			{
				$.colorbox({"html": data.substr(1)});
				setTimeout("$.colorbox.close()", 1000);
			}
		}
	});
	return false;
});
</script>

