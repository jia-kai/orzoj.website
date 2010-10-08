<?php
/*
 * $File: index.php
 * $Date: Fri Oct 08 15:34:37 2010 +0800
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

/**
 * @ignore
 */
function get_url($file)
{
	global $theme_path;
	echo get_page_url($theme_path . $file);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo t_get_html_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php get_url('style.css'); ?>" />
</head>
<body>
	<div id="left">
		<div id="logo">
			<img src="<?php get_url('images/logo.png'); ?>" alt="Logo"/>
		</div>
		<div id="sidebar">
			<div id="login">
			</div>
				<?php echo user_check_login_get_form();?>
			<div id="toprank">
			</div>
		</div>
	</div>
	<div id="right">
		<div id="guidance">
		</div>
		<div id="content">
		</div>
	</div>
	<div id="footer">
	<?php echo t_get_footer(); ?>
	</div>
</body>
</html>

