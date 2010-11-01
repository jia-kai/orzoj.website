<?php
/*
 * $File: login.php
 * $Date: Sun Oct 31 21:43:38 2010 +0800
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php admin_echo_html_head();?>
	<link href="login_style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<form action="<?php _url('index.php');?>" method="post" target="_top">
	<table class="login-form" >
		<tr>
			<td class="prompt"><?php echo __('Username:');?></td>
			<td class="field"><?php echo $user->username;?></td>
		</tr>
		<tr>
			<td class="prompt"><label for="passwd-input"><?php echo __('Password:');?></label></td>
			<td class="field"><input id="passwd-input" type="password" name="admin-login-passwd" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="<?php echo __('Login');?>" /></td>
		</tr>
	</table>
</form>
</body>
<script type="text/javascript">
document.getElementById('passwd-input').focus();
</script>
</html>

