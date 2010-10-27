<?php
/*
 * $File: user_update_info.php
 * $Date: Wed Oct 27 18:51:57 2010 +0800
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
		$id = user_update_info();
		die('0' . __('user information updated sucessfully'));
	}
	catch (Exc_orzoj $e)
	{
		die('1' . __('Failed to update user information: ') . htmlencode($e->msg()));
	}
}

?>

<form action="<?php t_get_link($cur_page, 'do');?>" method="post" id="user-update-info-form">
<?php _tf_form_generate_body('user_update_info_get_form'); ?>
<div style="text-align: right">
	<button id="user-update-info-button" type="submit" class="in-form" ><?php echo __('Update!'); ?></button>
</div>
</form>

<script type="text/javascript">

$("#user-update-info-button").button();
$("#user-update-info-form").bind("submit", function(){
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link($cur_page, 'do', FALSE);?>",
		"data": $("#user-update-info-form").serializeArray(),
		"success": function(data) {
			if (data.charAt(0) == '1')
				alert(data.substr(1));
			else
				$.colorbox({"html": data.substr(1)});
		}
	});
	return false;
});
</script>

