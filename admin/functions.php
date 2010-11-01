<?php
/*
 * $File: functions.php
 * $Date: Sun Oct 31 21:40:39 2010 +0800
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

require_once $includes_path . 'user.php';

/**
 * get the URL of specified file in $admin_path
 * @param string $file file path related to $admin_path
 * @return string
 */
function _url($file, $return_str = FALSE)
{
	global $admin_path;
	$ret = get_page_url($admin_path . $file);
	if ($return_str)
		return $ret;
	echo $ret;
}

/**
 * check whether the user logged in as administration session
 * @return bool
 */
function admin_check_user_login()
{
	global $db, $DBOP, $user;
	if (!user_check_login())
		return FALSE;
	$where = array($DBOP['='], 'uid', $user->id);
	if (isset($_POST['admin-login-passwd']))
	{
		$db->delete_item('user_admin_login', $where);
		$passwd_encr = $db->select_from('users', 'passwd', array($DBOP['='], 'id', $user->id));
		$passwd_encr = $passwd_encr[0]['passwd'];
		if (is_null(_user_check_passwd($user->username, $_POST['admin-login-passwd'], $passwd_encr)))
			return FALSE;
		$db->insert_into('user_admin_login', array(
			'uid' => $user->id,
			'time' => time()
		));
		return TRUE;
	}
	$row = $db->select_from('user_admin_login', 'time', $where);
	if (empty($row))
		return FALSE;
	if (time() - $row[0]['time'] >= ADMIN_LOGIN_TIMEOUT)
	{
		admin_user_logout();
		return FALSE;
	}
	$db->update_data('user_admin_login', array('time' => time()), $where);
	return TRUE;
}

/**
 * exit administration session
 * @return void
 */
function admin_user_logout()
{
	global $user, $db, $DBOP;
	if (user_check_login())
		$db->delete_item('user_admin_login', array(
			$DBOP['='], 'uid', $user->id));
	session_clear();
}

/**
 * get contents to be put in the 'head' tag
 * @return string
 */
function admin_echo_html_head()
{
	global $website_name;
	echo '
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>' . $website_name . ' - ' . __('Administration') . '</title>
	<meta http-equiv="pragma" content="no-cache" /> ';
}

/**
 * use HTTP header to redirect to OrzOJ index page
 * @return NEVER
 */
function redirect_to_index()
{
	ob_clean();
	header('Location: ' . _url('..', TRUE));
	die();
}

/**
 * @ignore
 */
class _Pgid_tree_node
{
	var $name, $child;
	function add_child($ch)
	{
		array_push($this->child, $ch);
	}
	function __construct($name)
	{
		$this->name = $name;
		$this->child = array();
	}
}

/**
 * get options for problem group select
 * @return array array of array(<text>, <group id>)
 */
function &make_pgid_select_opt()
{
	global $db;
	$rows = $db->select_from('prob_grps', array('id', 'pgid', 'name'));
	$tree = array(new _Pgid_tree_node(__('All')));
	foreach ($rows as $row)
	{
		$gid = intval($row['id']);
		$pgid = intval($row['pgid']);
		$tree[$gid] = new _Pgid_tree_node($row['name']);
		$tree[$pgid]->add_child($gid);
	}
	$opt = array();
	_make_gid_select_dfs($tree, 0, $opt, '');
	return $opt;
}

/**
 * @ignore
 */
function _make_gid_select_dfs(&$tree, $root, &$result, $prefix)
{
	array_push($result, array($prefix . $tree[$root]->name, $root));
	if (strlen($prefix))
		$prefix = substr($prefix, 0, strlen($prefix) - 2) . '&nbsp;&nbsp;';
	$prefix .= '|--';
	foreach ($tree[$root]->child as $ch)
		_make_gid_select_dfs($tree, $ch, $result, $prefix);
}

/**
 * echo page number navigation links and form
 * it will send the user requested page number via
 * $_GET['pgnum'] (staring at 0) or via $_POST['pgnum'] (starting at 1)
 * @param int $pgnum current page number (starting at 0)
 * @param int $tot_page total number of pages
 * @return void
 */
function make_pgnum_nav($pgnum, $tot_page)
{
	global $cur_page_link;
	echo '<div class="pgnum-nav">';
	if ($pgnum)
	{
		$t = $pgnum - 1;
		echo "<span><a href='$cur_page_link&amp;pgnum=$t'>&lt;" . __('Prev') . '</a> | </span>';
	}
	$t = $pgnum + 1;
	echo "<span><form action='$cur_page_link' method='post'><input type='text' name='pgnum' value='$t' /> / $tot_page";
	echo '</form></span>';
	if ($t < $tot_page)
		echo "<span> | <a href='$cur_page_link&amp;pgnum=$t'>" . __('Next') . '&gt;</a></span>';
	echo '</div>';
}

