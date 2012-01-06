<?php
/* TODO
 * Add Default theme and users and some other things
 */
/* 
 * $File: index.php
 * $Date: Fri Jan 06 16:29:46 2012 +0800
 */
/**
 * @package orzoj-website
 * @subpackage install
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
ob_start();
header('Content-type:text/html;charset=utf-8');

define('IN_ORZOJ', TRUE);

$root_path = rtrim(realpath('../'), '/') . '/';

$config_file = $root_path . 'config.php';

$includes_path = $root_path . 'includes/';

require_once $includes_path . 'l10n.php';
require_once $includes_path . 'exception.php';

define('TOTAL_STEP', 4);

$step = 0;
if (isset($_GET['step']))
	$step = intval($_GET['step']);
if ($step < 0 || $step >= TOTAL_STEP) 
	die(__('How are you doing?'));

if (isset($_GET['action']) && isset($_GET['step']))
	die(__('What\'s up buddy?'));
$action = 'none';
$action_list = array(
	'set_administrator' => __('Set Administrator'),
   	'set_administrator_finish' => __('Set Administrator Finished'),
	'set_static_password' => __('Set Static Password'),
	'set_static_password_finish' => __('Set Static Password Finished')
);

if (isset($_GET['action']))
{
	$step = -1;
	$action = $_GET['action'];
	if (array_key_exists($action, $action_list) === FALSE)
		die(__('What\'s up buddy?'));
}
?>

<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=utf-8">
<link rel="stylesheet" href="style.css" type="text/css" />

<?php 
$title = __('Uh?');
if ($step != -1)
	$title = __('Orz Online Judge Website Installation Wizard - Step %d', $step);
else if ($action != 'none')
	$title = __('Orz Online Judge Website Installation Wizard - %s', $action_list[$action]);
echo '<title>' . $title . '</title>';

?>
</head>
<body>
<div class="container">
	<div class="container-inner">
<?php
echo '<div class="content">';
echo '<div class="logo"><h1><img alt="' . __('Orz Online Judge'). '" src="images/logo.gif" /></h1></div>';
echo '<div class="logo"><h1>' . $title . '</h1></div>';

//$default_db_host = $_SERVER['HTTP_HOST'];
$default_db_host = 'localhost';
preg_match('/^(.*)\/install\/[^\/]*$/', $_SERVER['REQUEST_URI'], $default_website_root);
$default_website_root = $default_website_root[1];
$config_items = array(
	array(__('Website name'), 'website_name', 'input', 'text', __('Orz Online Judge'), __('The name of this site.')),
	array(__('Database Type'), 'db_type', 'select', array('mysql' => 'MySQL &gt;5.0', 'postgresql' => 'PostgreSQL'), __('The type of database you want to run Orz Online Judge in')),
	array(__('Database Name'), 'db_dbname', 'input', 'text', 'orzoj', __('The name of the database you want to run Orz Online Judge in.')),
	array(__('User Name'), 'db_user', 'input', 'text', 'username', __('Your database username')),
	array(__('Password'), 'db_password', 'input', 'password', '', __('Your database password')),
	array(__('Database Host'), 'db_host', 'input', 'text', $default_db_host, __('Your database host')),
	array(__('Database Port'), 'db_port', 'input', 'text', '', __('The port to connect your database. If you are confused about this, leave it blank.')),
	array(__('Table Prefix'), 'table_prefix', 'input', 'text', 'orzoj_', __('If you want to run multiple Orz Online Judge installations in a single database, change this.')),
	array(__('Website Root'), 'website_root', 'input', 'text', $default_website_root, __('The path where Orz Online Judge is located relative to the domain name. e.g. `%s`', $default_website_root))
);

function make_form_table($items)
{
	echo '<table class="form-table">';
	foreach ($items as $item)
	{
		echo '<tr>';
		echo "<th><label for=\"$item[1]\">$item[0]</label></th>";
		if ($item[2] == 'input')
		{
			echo "<td><input name=\"$item[1]\" id=\"$item[1]\" type=\"$item[3]\" value=\"$item[4]\" /></td>";
			echo "<td>$item[5]</td>";
		} else if ($item[2] == 'select')
		{
			echo "<td><select name=\"$item[1]\" id=\"$item[1]\">";
			foreach ($item[3] as $val => $show)
				echo "<option value=\"$val\">$show</option>";
			echo "</select></td>";
			echo "<td>$item[4]</td>";
		}
		echo '</tr>';
	}
	echo '</table>';

}


switch ($step)
{
case 0:
{
	echo '<p>' . __('Welcome to Orz Online Judge. Before proceeding, you will need to know the following items:') . '</p>';
	echo '<ol>';
	foreach(array(__('Database name'), __('Database username'),
		__('Database password'), __('Database host'))
		as $item)
		echo '<li>' . $item . '</li>';
	echo '</ol>';
	echo __('In all likelihood, these items were supplied to you by your Web Host. If you do not have this information, then you will need to contact them before you can continue. If you are all ready...');
	echo '<div class="step"><a href="index.php?step=1">' . __('Let\' go!') . '</a></div>';
	break;
}

case 1:
	echo '<form action="index.php?step=2" method="post">';
	echo '<p>' . __('Below you should enter your database connection details. If you\'re not sure about these, contact your host.') . '</p>';
	make_form_table($config_items);
	$Submit = __('Submit');
	echo "<p class=\"step\"><input type=\"submit\" value=\"$Submit\" name=\"submit\" /></p>";
	echo '</form>';
	break;
case 2:
	// try to write config file. if succeeded, continue to install database
	// if failed, print the content of config file and provide a link to jump to step 3

case 3:

	if ($step == 2)
	{
		$items_cant_be_empty = array('db_type', 'db_name', 'db_user', 
			'db_password', 'db_host', 'website_root');
		$str = "<?php\n";
		try
		{
			foreach ($config_items as $item)
			{
				$name = $item[1];
				$$name = '';
				if (!isset($_POST[$name]) || empty($_POST[$name]))
					if (array_search($name, $items_cant_be_empty) !== FALSE)
						throw new Exc_orzoj(__('%s can\'t be empty.', $item[0]));
				if (isset($_POST[$name]))
					$$name = $_POST[$name];
			} 
		}
		catch (Exc_orzoj $e)
		{
			echo $e->msg() . '<br />';
			echo __('Click <a href="index.php?step=1">here</a> to back to previous step.');
			break;
		}
		if ($website_root[strlen($website_root) - 1] != '/')
			$website_root .= '/';
		if (empty($db_port))
			$db_port = 3306;
		foreach ($config_items as $item)
		{
			$name = $item[1];
			$val = $$name;
			$str .= "\$$name = '$val';\n";
		}
		$str .= "define('DISABLE_URI_REWRITE', TRUE);\n";
		$fconfig = @fopen($root_path . 'config.php', 'w');
		if (!$fconfig)
		{
			echo __('It seems that I don\'t have the permission to create file `%s`, please create it yourself with following content:', $website_root . 'config.php');
			echo '<textarea readonly="readonly">';
			echo $str;
			echo '</textarea>';
			echo __('and click <a href="index.php?step=3">here</a> to continue.');
			break;
		}
		else
		{
			fwrite($fconfig, $str);
			fclose($fconfig);
		}
	}
	else if ($step == 3)
	{
		if (!is_readable($config_file))
		{
			echo __('Please make sure that `%s` exists and I can read it. Refresh this page when ready.', $config_file);
			break;
		}
		require_once $config_file;
		foreach ($config_items as $item)
		{
			$name = $item[1];
			if (!isset($$name))
			{
				echo __('Please make sure that `%s` is correct.', $config_file) . '<br />';
				echo __('If you do not know what causes this, please run the install again.');
				break;
			}
			else
			{
				$val = $$name;
				global $$name;
				$$name = $val;
			}
		}
	}
	require_once 'install_database.php';
	if (install_database())
	{
		echo __('All right sparky! Orz Online Judge is ready to use!') . '<br />';
		echo __('...and some options should be set.');
		echo '<ol>';
		foreach (array(
			array(__('Static password'), 'set_static_password', __('used to communicate withe the orzoj-server, default is `hello`')),
			array(__('Administrator account'), 'set_administrator', __('default user is `admin` and password is `admin888`'))
		) as $item)
		echo '<li><a target="_blank" href="index.php?action=' . $item[1] . '">' . $item[0] . '</a> (' . $item[2]. ')</li>';
		echo '</ol>';
		echo __('If you are not going to go further steps,  <span style="font-size: 24px; color: red;">please <b>REMOVE</b> the installation directory `<b>%s</b>` <b>RIGHT AFTER THE INSTALLATION</b>!', realpath('.')) . '<br />';
		echo __('To make this Online Judge function properly, orzoj-server and orzoj-judge is needed. Please see <a href="http://code.google.com/p/orzoj">Orz Online Judge</a> for further information') . '</span>';
	}
	break;
}


$items = array(
	array(__('Nick name'), 'nickname', 'input', 'text', 'The God', ''),
	array(__('Username'), 'username', 'input', 'text', 'admin', ''),
	array(__('Password'), 'password', 'input', 'password', '', ''),
	array(__('Confirm password'), 'password_confirm', 'input', 'password',  '', '')
);

if ($step == -1)
{
	require_once '../pre_include.php';
	require_once $includes_path . 'user.php';
	$finished = true;
	switch ($action)
	{
	case 'set_static_password':
		echo '<form action="index.php?action=set_static_password_finish" method="post"><label for="static_password">' 
			. __('Static Password')
			. '</label><input type="text" value="hello" id="static_password" name="static_password" />'
			. '<input type="submit" name="submit" value="' . __('Set static password') . '" />'
			. '</form>';
		$finished = false;
		break;

	case 'set_static_password_finish':
		try
		{
			if (!isset($_POST['static_password']))
				throw new Exc_orzoj(__('Incomplete POST'));
			$static_password = $_POST['static_password'];
		}
		catch (Exc_orzoj $e)
		{
			echo $e->msg() . '<br />';
			echo __('Click <a href="index.php?action=set_static_password">here</a> to back to previous step.');
			$finished = false;
			break;
		}
		option_set('static_password', $static_password);
		echo __('Static password has been successfully updated.') . '<br />';
		break;
	case 'set_administrator':
		echo '<form action="index.php?action=set_administrator_finish" method="post">';
		echo __('Set you own adminitration account:') . '<br>';
		make_form_table($items);
		$Register = __('Register');
		echo "<p class=\"step\"><input type=\"submit\" value=\"$Register\" name=\"submit\" /></p>";
		echo __('Detailed information of your account can be set when you are logined.') . '<br />';
		echo '</form>';
		$finished = false;
		break;

	case 'set_administrator_finish':
		try
		{
			foreach ($items as $item)
			{
				$name = $item[1];
				if (!isset($_POST[$name]) || empty($_POST[$name]))
					throw new Exc_orzoj(__('%s can\'t be empty.', $item[0]));
				$$name = $_POST[$name];
			}
			if ($password != $password_confirm)
			{
				unset($_POST['password']);
				unset($_POST['password_confirm']);
				throw new Exc_orzoj(__('Password does not match.'));
			}
			user_validate_username($username);
			user_validate_nickname($nickname);
		}
		catch (Exc_orzoj $e)
		{
			echo $e->msg() . '<br />';
			echo __('Click <a href="index.php?action=set_administrator">here</a> to back to previous step.');
			$finished = false;
			break;
		}
		unset($_POST['password_confirm']);
		$val = array();
		$val['username'] = strtolower($username);
		$val['passwd'] = _user_make_passwd($val['username'], $password);
		$val['nickname'] = htmlencode($nickname);
		$val['view_gid'] = json_encode(array(GID_ALL, GID_GUEST));
		$val['reg_time'] = time();
		$val['reg_ip'] = get_remote_addr();
		$db->update_data('users', $val, array($DBOP['='], 'id', 1));
		echo __('Adminitrator account has been updated.') . '<br />';
		break;
	}
	if ($finished)
	{
		echo __('Congratulations! All things nearly done!') . '<br />';
		echo __('But don\'t forget to <b style="font-size: 24px; color: red;">REMOVE `%s` RIGHT AFTER FINISHING THE INSTALLATION!</b>', realpath('.')) . '<br />';
		echo '<span style="font-size: 24px; color: red;">' . __('To make this Online Judge function properly, orzoj-server and orzoj-judge is needed. Please see <a href="http://code.google.com/p/orzoj">Orz Online Judge</a> for further information.') . '</span><br />';
		echo __('We wish you a happy journey here, and the Installation Wizzard will say goodbye to you~');
	}
}

echo '</div>';
?>
	</div>
</div>
</body>
</html>


