<?php
/* TODO
 * Add Default theme and users and some other things
 */
/* 
 * $File: index.php
 * $Date: Sun Nov 07 21:58:23 2010 +0800
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
require_once '../includes/l10n.php';
?>
<?php

$root_path = rtrim(realpath('../'), '/') . '/';

$config_file = $root_path . 'config.php';

$includes_path = $root_path . 'includes/';

define('MAX_STEP', 3);

$step = 0;
if (isset($_GET['step']))
	$step = intval($_GET['step']);
if ($step < 0 || $step > MAX_STEP) 
	die(__('How are you doing?'));
?>

<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=utf-8">
<link rel="stylesheet" href="style.css" type="text/css" />

<?php 
echo '<title>' . __('Orz Online Judge Installation Wizard - Step %d', $step) . '</title>';
?>
</head>
<body>
<div class="container">
	<div class="container-inner">
<?php
echo '<div class="content">';
echo '<div class="logo"><h1><img alt="' . __('Orz Online Judge'). '" src="images/logo.gif" /></div></h1>';

$default_db_host = $_SERVER['SERVER_NAME'];
preg_match('/^(.*)\/install\/[^\/]*$/', $_SERVER['REQUEST_URI'], $default_website_root);
$default_website_root = $default_website_root[1];
$items = array(
	array(__('Website name'), 'website_name', 'input', 'text', __('Orz Online Judge'), __('The name of this site.')),
	array(__('Database Type'), 'db_type', 'select', array('mysql' => 'MySQL &gt;5.0', 'postgresql' => 'PostgreSQL'), __('The type of database you want to run Orz Online Judge in')),
	array(__('Database Name'), 'db_dbname', 'input', 'text', 'orzoj', __('The name of the database you want to run Orz Online Judge in.')),
	array(__('User Name'), 'db_user', 'input', 'text', 'username', __('Your database username')),
	array(__('Password'), 'db_password', 'input', 'password', '', __('Your database password')),
	array(__('Database Host'), 'db_host', 'input', 'text', $default_db_host, __('Your database host')),
	array(__('Database Port'), 'db_port', 'input', 'text', '3306', __('The port to connect your database')),
	array(__('Table Prefix'), 'table_prefix', 'input', 'text', 'orzoj_', __('If you want to run multiple Orz Online Judge installations in a single database, change this.')),
	array(__('Website Root'), 'website_root', 'input', 'text', $default_website_root, __('The path where Orz Online Judge is located relative to the domain name. e.g. `%s`', $default_website_root))
);

switch ($step)
{
case 0:
{
	echo '<p>' . __('Welcome to Orz Online Judge. Before proceeding, you will need to know the following items:') . '</p>';
	echo '<ol>';
	foreach(array('Database name', 'Database username',
		'Database password', 'Database host', 
		'Table prefix (if you want to run more than one Orz Online Judge in a single database)') as $item)
		echo '<li>' . $item . '</li>';
	echo '</ol>';
	echo __('In all likelihood, these items were supplied to you by your Web Host. If you do not have this information, then you will need to contact them before you can continue. If you are all ready...');
	echo '<div class="step"><a href="index.php?step=1">' . __('Let\' go!') . '</a></div>';
	break;
}

case 1:
	echo '<form action="index.php?step=2" method="post">';
	echo '<p>' . __('Below you should enter your database connection details. If you\'re not sure about these, contact your host.') . '</p>';
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
	$Submit = __('Submit');
	echo "<p class=\"step\"><input type=\"submit\" value=\"$Submit\" name=\"submit\" /></p>";
	echo '</form>';
	break;
case 2:
case 3:
	function add_prob_a_plus_b()
	{
		global $db;
		$db->insert_into('problems',
			array('title' => 'A+B Problem',
			'code' => 'a+b',
			'perm' => serialize(array(0, 1, array(GID_ALL), array())),
			'io' => serialize(array('a+b.in', 'a+b.out')),
			'time' => time(),
			'desc' => serialize(array(
				'time' => '1s',
				'memory' => '256MB',
				'desc' => 'Calculate a + b',
				'input_fmt' => 'Two numbers in a single row.',
				'output_fmt' => 'A number, the sum of a and b.',
				'input_samp' => '1 2',
				'output_samp' => '3',
				'source' => 'Every OJ',
				'hint' => '1 <= a, b <= 100000'
			))
		));
	}

	function add_post_topic_welcome()
	{
		global $db, $DBOP, $website_name;
		$time = time();
		$uid = 0;
		$prob_id = 0;
		$is_top = 0;
		$priority = 0;
		$is_locked = 0;
		$is_boutique = 0;
		$type = 1;
		$subject = __('Welcome to %s!', $website_name);

		$last_reply_time = $time;
		$last_reply_user = 0;

		$val = array();
		foreach (array('time', 'uid', 'prob_id', 
			'priority', 'is_top', 'is_locked', 'is_boutique',
			'type', 'subject', 'last_reply_time', 'last_reply_user') as $item)
			$val[$item] = $$item;
		$tid = $db->insert_into('post_topics', $val);

		$val = array();
		$floor = 1;
		$content = __('Welcome to %s!', $website_name);

		$last_modify_time = 0;
		$last_modify_user = 0;

		foreach (array('time', 'uid', 'floor', 'tid', 
			'content', 'last_modify_time', 'last_modify_user') as $item)
			$val[$item] = $$item;

		$db->insert_into('posts', $val);

	}

	function install_database()
	{
		global $root_path, $includes_path, $items, $db, $tables;
		foreach ($items as $item)
		{
			$name = $item[1];
			global $$name;
		}
		try
		{
			db_init();
		} catch (Exc_db $e)
		{
			throw $e;
			throw new Exc_db(__('Failed to connect to database.'));
		}
		foreach ($tables as $name => $table)
		{
			if ($db->table_exists($name))
				$db->delete_table($name);
			$db->create_table($name, $table);
		}
		require_once $includes_path . 'user.php';
		user_init_default_grp();
		add_prob_a_plus_b();
		add_post_topic_welcome();
		echo __('Installation accomplished.') . '<br />';
	}

	function InstallDatabase()
	{
		global $fconfig, $config_file, $config_content;
		try
		{
			install_database();
			if (!file_exists($config_file))
			{
				fprintf($fconfig, '%s', $config_content);
				fclose($fconfig);
			}
		}
		catch (Exc_orzoj $e)
		{
			echo $e->msg() . '<br />';
			echo __('Failed to install.') . '<br />';
			echo __('Please check if configurations are correct.') . '<br />';
			break;
		}
		echo __('All right sparky! Orz Online Judge is ready to use!');
	}

	if ($step == 2)
	{
		$items_cant_be_empty = array('db_type', 'db_name', 'db_user', 
			'db_password', 'db_host', 'website_root');
		$str = "<?php\n";
		$error = false;
		foreach ($items as $item)
		{
			$name = $item[1];
			if (!isset($_POST[$name]) || empty($_POST[$name]))
				if (array_search($name, $items_cant_be_empty) !== FALSE)
				{
					echo __('%s can\'t be empty.', $item[0]);
					echo __('Click <a href="index.php?step=1">here</a> to back to previous step.');
					$error = true;
					break;
				}
			$$name = $_POST[$name];
		}
		if ($error)
			break;
		if ($website_root[strlen($website_root) - 1] != '/')
			$website_root .= '/';
		foreach ($items as $item)
		{
			$name = $item[1];
			$val = $$name;
			$str .= "\$$name = '$val';\n";
		}
		$str .= "define('DISABLE_URI_REWRITE', TRUE);\n";
		if (file_exists('installed.php'))
			echo __('Orz Online Judge has already been installed before.');
		global $fconfig, $config_content;
		$config_content = $str;
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
	}
	else if ($step == 3)
	{
		if (!file_exists($config_file))
		{
			echo __('Please check if `%s` exists.', $config_file);
			break;
		}
		require_once $config_file;
		foreach ($items as $item)
		{
			$name = $item[1];
			if (!isset($$name))
			{
				echo __('Please check if `%s` is correct.', $config_file) . '<br />';
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
	require_once 'tables.php';
	define('IN_INSTALLATION', true);
	require_once $root_path . 'pre_include.php';
	InstallDatabase();
}
echo '</div>';
?>
	</div>
</div>
</body>
</html>

