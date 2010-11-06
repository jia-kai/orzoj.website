<?php
/*
 * $File: functions.php
 * $Date: Fri Nov 05 20:14:23 2010 +0800
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
	if (isset($_POST['admin-login-passwd']))
	{
		$passwd_encr = $db->select_from('users', 'passwd', array($DBOP['='], 'id', $user->id));
		$passwd_encr = $passwd_encr[0]['passwd'];
		if (is_null(_user_check_passwd($user->username, $_POST['admin-login-passwd'], $passwd_encr)))
		{
			return FALSE;
			admin_user_logout();
		}
		session_set('login_time', time());
		session_set('login_uid', $user->id);
		return TRUE;
	}
	if (is_null($time = session_get('login_time')) || is_null($uid = session_get('login_uid')))
		return FALSE;
	if ($uid != $user->id || time() - $time >= ADMIN_LOGIN_TIMEOUT)
	{
		admin_user_logout();
		return FALSE;
	}
	session_set('login_time', time());
	return TRUE;
}

/**
 * exit administration session
 * @return void
 */
function admin_user_logout()
{
	session_clear('admin');
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
class _Grp_tree_node
{
	var $name, $desc, $child;
	function add_child($ch)
	{
		array_push($this->child, $ch);
	}
	function __construct($name = NULL, $desc = NULL)
	{
		$this->name = $name;
		$this->desc = $desc;
		$this->child = array();
	}
}

/**
 * get options for problem group select
 * @param string|NULL $gid0_name the name of group with gid 0 (default to 'All')
 * @return array array of array(<text>, <group id>)
 */
function &make_pgid_select_opt($gid0_name = NULL)
{
	global $db;
	$rows = $db->select_from('prob_grps', array('id', 'pgid', 'name'));
	if (is_null($gid0_name))
		$gid0_name = __('All');
	$tree = array(new _Grp_tree_node($gid0_name));
	foreach ($rows as $row)
	{
		$gid = intval($row['id']);
		$pgid = intval($row['pgid']);
		if (isset($tree[$gid]))
			$tree[$gid]->name = $row['name'];
		else
			$tree[$gid] = new _Grp_tree_node($row['name']);
		if (!isset($tree[$pgid]))
			$tree[$pgid] = new _Grp_tree_node();
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

/**
 * get group id selector
 * @param string $name selector name
 * @param int $type selector type (0: user group; otherwise problem group)
 * @param array|NULL $default array of id of groups, items selected by default
 * @param string|NULL $onclick onclick js function, which takes the group id as argument
 * @param bool $direct_echo
 * @return void|string
 */
function form_get_gid_selector($name, $type, $default = NULL, $onclick = NULL, $direct_echo = TRUE)
{
	global $db;
	$class = 'gid-selector';
	if (!is_null($onclick))
		$class .= ' with-onclick';
	$code = "<div class='$class'>";
	$rows = $db->select_from($type ? 'prob_grps' : 'user_grps');
	$tree = array();
	foreach ($rows as $row)
	{
		$gid = intval($row['id']);
		$pgid = intval($row['pgid']);
		if (isset($tree[$gid]))
		{
			$tree[$gid]->name = $row['name'];
			$tree[$gid]->desc = $row['desc'];
		}
		else
			$tree[$gid] = new _Grp_tree_node($row['name'], $row['desc']);
		if (!isset($tree[$pgid]))
			$tree[$pgid] = new _Grp_tree_node();
		$tree[$pgid]->add_child($gid);
	}
	$selected = array();
	if (is_array($default))
		foreach ($default as $f)
			$selected[$f] = TRUE;
	$name = 'gid_selector_' . $name . '[]';
	_form_get_gid_selector_dfs($name, $code, $tree, 0, $selected, $onclick);
	$code .= '</div>';
	if ($direct_echo)
		echo $code;
	else
		return $code;
}

/**
 * get gid selector value
 * @param string $name selector id
 * @return array
 * @exception Exc_runtime
 */
function form_get_gid_selector_val($name)
{
	$name = 'gid_selector_' . $name;
	if (!isset($_POST[$name]) || !is_array($_POST[$name]))
		return array();
	foreach ($_POST[$name] as &$v)
		$v = intval($v);
	return array_unique($_POST[$name]);
}

/**
 * @ignore
 */
function _form_get_gid_selector_dfs(&$post_name, &$output, &$tree, $root, &$selected, &$onclick)
{
	$output .= '<ul>';
	foreach ($tree[$root]->child as $ch)
	{
		$desc = $tree[$ch]->desc;
		$name = $tree[$ch]->name;
		if (is_null($onclick))
			$attr = sprintf('id="%s"', $id = get_unique_id());
		else
			$attr = '';
		if (isset($selected[$ch]))
			$attr .= ' checked="checked"';
		$output .= "<li><input name='$post_name' value='$ch' type='checkbox' $attr/>";
		if (is_null($onclick))
			$attr = "for='$id'";
		else
			$attr = "onclick='$onclick($ch)'";
		$output .= "<label title='$desc' $attr>$name</label></li>";
		if (!empty($tree[$ch]->child))
		{
			$output .= '<li class="subtree">';
			_form_get_gid_selector_dfs($post_name, $output, $tree, $ch, $selected, $onclick);
			$output .= '</li>';
		}
	}
	$output .= '</ul>';
}

/**
 * get an editor for editing problem/contest permission
 * @param string $name the editor name to be used in form_get_perm_editor_val
 * @param string|NULL $default default value
 * @param bool $direct_echo whether to echo the HTML code or return it
 * @return void|string
 */
function form_get_perm_editor($name, $default = NULL, $direct_echo = TRUE)
{
	$name = 'perm_editor_' . $name;
	if (is_null($default))
		$default = array(0, 1, array(), array());
	else
		$default = unserialize($default);
	$code = '<div class="perm-editor">';
	$code .= form_get_select(__('Order:'), $name . '_order', array(__('Allow, deny') => 0, __('Deny, allow') => 1),
		$default[0], FALSE) . '<br />';
	$code .= form_get_select(__('What to do if no match:'), $name . '_no_match',
		array(__('Allow') => 1, __('Deny') => 0),
		$default[1], FALSE) . '<br />';

	$code .= '<div style="clear: both; float: left">';
	$code .= '<label>' . __('Allowed user groups:') . '</label><br />' .
		form_get_gid_selector($name . '_allow', 0, $default[2], NULL, FALSE);
	$code .= '</div><div style="float: left; margin-left: 10px;">';
	$code .= '<label>' . __('Denied user groups:') . '</label><br />' .
		form_get_gid_selector($name . '_deny', 0, $default[3], NULL, FALSE);
	$code .= '</div>';
	$code .= '</div>';
	if ($direct_echo)
		echo $code;
	else
		return $code;
}

/**
 * get problem/contest permission editor value
 * @param string $name editor name passed to form_get_perm_editor
 * @return string the serialized value as described in /install/tables.php
 * @exception Exc_runtime
 */
function form_get_perm_editor_val($name)
{
	$name = 'perm_editor_' . $name;
	$ret = array();
	$ret_idx = 0;
	foreach (array('_order', '_no_match') as $idx)
	{
		$idx = $name . $idx;
		if (!isset($_POST[$idx]))
			throw new Exc_runtime(__('incomplete post'));
		$ret[$ret_idx ++] = (intval($_POST[$idx]) == 1);
	}
	$ret[2] = form_get_gid_selector_val($name . '_allow');
	$ret[3] = form_get_gid_selector_val($name . '_deny');
	return serialize($ret);
}

/**
 * get a select element
 * @param string $prompt
 * @param string $post_name
 * @param array $options in the format array(&lt;display name&rt; => &lt;option value&rt;)
 * @param string $default the value of option selected by default
 * @param bool $direct_echo
 * @return string|void
 */
function form_get_select($prompt, $post_name, &$options, $default = NULL, $direct_echo = TRUE)
{
	$id = get_unique_id();
	$str = "<label for='$id'>$prompt</label>
		<select name='$post_name' id='$id'>";
	foreach ($options as $name => $value)
	{
		$str .= "<option value='$value' ";
		if ($value == $default)
			$str .= 'selected="selected"';
		$str .= ">$name</option>";
	}
	$str .= "</select>";
	if ($direct_echo)
		echo $str;
	else
		return $str;
}

/**
 * get an information div
 * @param string $type type of the div, which is one of 'info', 'notice', 'warning', 'error' 
 * @param string $content
 * @param bool $direct_echo
 * @return void|string
 */
function get_info_div($type, $content, $direct_echo = TRUE)
{
	$str = "<div class='info-div $type'>$content</div>";
	if ($direct_echo)
		echo $str;
	else
		return $str;
}

/**
 * get value of a key in an array, or return the default value if unavailable
 * @param &array $array
 * @param mixed $key
 * @param mixed $default
 * return mixed
 */
function get_array_val(&$array, $key, $default = NULL)
{
	return is_array($array) && isset($array[$key]) ? $array[$key] : $default;
}

/**
 * get an input element
 * @param string $prompt
 * @param string $post_name
 * @param string|NULL $default
 * @param bool $add_div whether to put the input in a div with class form-field
 */
function form_get_input($prompt, $post_name, $default = NULL, $add_div = TRUE)
{
	$id = get_unique_id();
	if (is_null($default))
		$default = '';
	if ($add_div)
		echo '<div class="form-field">';
	echo "<label for='$id'>$prompt</label><input type='text' name='$post_name' value='$default' id='$id' />";
	if ($add_div)
		echo '</div>';
}

/**
 * get a hidden input
 * @param string $name
 * @param string $value
 */
function form_get_hidden($name, $value)
{
	echo "<input type='hidden' name='$name' value='$value' />";
}

/**
 * get a textarea
 * @param string $prompt
 * @param string $post_name
 * @param string|NULL $default
 * @param bool $small
 */
function form_get_textarea($prompt, $post_name, $default = NULL, $small = FALSE)
{
	$id = get_unique_id();
	if (is_null($default))
		$default = '';
	$class = $small ? 'small' : 'big';
	echo "<div class='form-field'><label for='$id'>$prompt</label>
		<textarea class='$class' id='$id' name='$post_name'>$default</textarea></div>";
}

/**
 * get a textarea with CKEditor
 */
function form_get_ckeditor($prompt, $post_name, $default = NULL)
{
	$id = get_random_id();
	if (is_null($default))
		$default = '';
	echo "<div class='form-field'><label for='$id'>$prompt</label><br />";
	echo "<textarea id='$id' name='$post_name'>";
	echo $default;
	echo '</textarea><script type="text/javascript">
		CKEDITOR.replace("' . $id . '");
		</script></div>';
}

/**
 * get the value of a post
 * @param string $name
 * @return string
 * @exception Exc_runtime if no such $name
 */
function get_post($name)
{
	if (!isset($_POST[$name]))
		throw new Exc_runtime(__('incomplete post, field %s not found', $name));
	return $_POST[$name];
}

