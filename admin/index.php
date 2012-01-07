<?php
/*
 * $File: index.php
 * $Date: Sat Jan 07 11:37:33 2012 +0800
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
 * global variables set:
 *		$page: current page file path on the server
 *		$cur_page: current page name
 *		$cur_page_link: current page URL (parameters should be appended to it)
 * page arguments:
 *		POST:
 *			ajax_mode:
 *				if set, do not echo html head
 */
try
{

	require_once '../pre_include.php';
	$admin_path = $root_path . 'admin/';
	require_once $admin_path . 'functions.php';

	session_add_prefix('admin');

	define('ADMIN_LOGIN_TIMEOUT', 1800); // in seconds

	if (!user_check_login())
		redirect_to_index();

	if (!admin_check_user_login())
		require_once $admin_path . 'login.php';
	else
	{
		if (isset($_GET['page']))
		{
			if ($_GET['page'] == 'exit')
			{
				admin_user_logout();
				redirect_to_index();
			}
			require_once $admin_path . 'page_defs.php';
			$page = $_GET['page'];
			if (!isset($PAGES[$page]))
				die('unknown page');
			$page = $PAGES[$page];
			for ($i = 2; $i < count($page); $i ++)
				if (!$user->is_grp_member($page[$i]))
					die('permission denied');
			$cur_page = $_GET['page'];
			$cur_page_link="index.php?page=$cur_page";
			$page = $admin_path . $page[1];
			if (!isset($_POST['ajax_mode']))
			{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>---</title>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<link href="js/style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		var CKEDITOR_BASEPATH = '<?php echo get_page_url($root_path . 'contents/editors/ckeditor');?>/';
	</script>
	<script type="text/javascript"
		src="<?php echo get_page_url($root_path . 'contents/editors/ckeditor/ckeditor.js');?>">
	</script>
	<script type="text/javascript">
		CKEDITOR.basePath = CKEDITOR_BASEPATH;
	</script>
	<script type="text/javascript" src="js/jquery.js"></script>
</head>
<?php
				if ($_GET['page'] != 'nav')
					echo '<body>';
			}
			require_once $page;
			if (!isset($_POST['ajax_mode']))
			{
				if ($_GET['page'] != 'nav')
					echo '</body>';
				echo '</html>';
			}
			die;
		}
		else
			require_once $admin_path . 'frameset.php';
	}
}
catch (Exc_orzoj $e)
{
	ob_clean();
	echo '<html><body>There is an uncaucht exception, and execution of orzoj-website scripts is aborted. Please 
		contact orzoj development team and report the bug at <a href="';
	echo ORZOJ_BUG_REPORT_ADDR;
	echo '">' . ORZOJ_BUG_REPORT_ADDR . '</a>, thanks!<br />';
	echo 'Detailed information: <br />';
	echo htmlencode($e->msg());
	echo '</body></html>';
}

