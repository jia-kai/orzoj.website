<?php
/*
 * $File: user_chpasswd.php
 * $Date: Wed Oct 27 18:59:38 2010 +0800
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

if (!user_check_login())
	die(__('Please log in first.'));

if ($page_arg == 'do')
{
	if (!isset($_POST['pwd_old']) || !isset($_POST['pwd_new']) || !isset($_POST['pwd_new_confirm']))
		die('1' . __('incomplete post'));
	if ($_POST['pwd_new'] != $_POST['pwd_new_confirm'])
		die('1' . __('Passwords do not match'));
	try
	{
		user_chpasswd($user->id, $_POST['pwd_old'], $_POST['pwd_new']);
		die('0' . __('Password succesfullly changed. Log out in 2 seconds ...'));
	}
	catch (Exc_orzoj $e)
	{
		die('1' . __('Failed to change password: ') . $e->msg());
	}
}

function _user_chpasswd_get_form()
{
	echo
		tf_form_get_passwd(__('Old password:'), 'pwd_old') .
		tf_form_get_passwd(__('New passowrd:'), 'pwd_new', __('Confirm new passowrd:'), 'pwd_new_confirm');
}

?>

<form action="<?php t_get_link($cur_page, 'do');?>" method="post" id="user-chpasswd-form">
<?php _tf_form_generate_body('_user_chpasswd_get_form');?>
<div style="text-align: right">
	<button id="register-button" type="submit" class="in-form" ><?php echo __('OK'); ?></button>
</div>
</form>

<script type="text/javascript">

$("#register-button").button();
$("#user-chpasswd-form").bind("submit", function(){
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link($cur_page, 'do', FALSE);?>",
		"data": $("#user-chpasswd-form").serializeArray(),
		"success": function(data) {
			if (data.charAt(0) == '1')
				alert(data.substr(1));
			else
			{
				$.colorbox({"html": data.substr(1)});
				setTimeout("window.location='<?php t_get_link('action-logout');?>'", 2000);
			}
		}
	});
	return false;
});
</script>

