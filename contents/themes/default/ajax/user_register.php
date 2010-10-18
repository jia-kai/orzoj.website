<?php
/*
 * $File: user_register.php
 * $Date: Sun Oct 17 19:34:43 2010 +0800
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

if ($page_arg == 'do')
{
	try
	{
		$id = user_register(TRUE);
		die('0' . __('Congratulations! You have successfully registered, and your user id is %d.', $id) .
			'<br />' . __('Login in 2 seconds ...'));
	}
	catch (Exc_orzoj $e)
	{
		die('1' . __('Failed to register: ') . htmlencode($e->msg()));
	}
}

?>

<form action="#" id="register-form">
<?php _tf_form_generate_body('user_register_get_form'); ?>
<div style="text-align: right">
	<button id="register-button" type="submit" class="in-form" ><?php echo __('Register!'); ?></button>
</div>
</form>

<script type="text/javascript">

$("#register-button").button();
$("#register-form").bind("submit", function(){
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link($cur_page, 'do', FALSE);?>",
		"data": $("#register-form").serializeArray(),
		"success": function(data) {
			if (data.charAt(0) == '1')
				alert(data.substr(1));
			else
			{
				$.colorbox({"html": data.substr(1)});
				setTimeout("window.location='<?php t_get_link('action-login');?>'", 2000);
			}
		}
	});
	return false;
});
</script>

