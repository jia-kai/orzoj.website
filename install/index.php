<?php
/* TODO
 * Add Default theme and users and some other things
 */
/* 
 * $File: index.php
 * $Date: Sat Nov 06 13:14:21 2010 +0800
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


$root_path = rtrim(realpath('..'),'/').'/';

$config_file_path = '';

if (defined('CONFIG_FILE_PATH'))
	$config_file_path = CONFIG_FILE_PATH;
else
	$config_file_path = $root_path . 'config.php';

if (file_exists($config_file_path))
{
	if (!isset($_GET['step']) || (isset($_GET['step']) && $_GET['step'] != '3'))
	{
	?>
	<div style="text-align: center; font-size: 30px;">
		<?php echo __('Orz Online Judge is already installed.'); ?>
	</div>
<?php
	die;
	}
}
function conf_file_generate($db_layer,$db_host,$db_port,$db_username,$db_password,$db_name,$table_prefix)
{
	global $config_file_path;
	$website_name = addslashes($_POST['website_name']);
	$db_username = addslashes($db_username);
	$db_password = addslashes($db_password);
	$db_name = addslashes($db_name);
	$table_prefix = addslashes($table_prefix);
	$website_root = str_replace('%2F', '/', addslashes(urlencode(rtrim($_POST['website_root'], '/') . '/')));
	$str = <<<EOF
<?php
\$website_name = '$website_name';
\$db_type = '$db_layer';
\$db_host = '$db_host';
\$db_port = '$db_port';
\$db_user = '$db_username';
\$db_password = '$db_password';
\$db_dbname = '$db_name';
\$table_prefix = '$table_prefix';
\$website_root = '$website_root';

define('DISABLE_URI_REWRITE', TRUE);

EOF;
	$fptr = @fopen($config_file_path, "w");
	if ($fptr)
	{
		fprintf($fptr, "%s", $str);
		fclose($fptr);
	}
	else
	{
		echo '<div style="text-align: center; font-size: 24px;">';
		echo '<div style="margin-left: auto; margin-right: auto;">';
		echo __('It seems that I don\'t have the permission to write configuation file </br>`<b>%s</b>`.</br>', $config_file_path);
		echo __('Please manually create `<b>%s</b>` yourself with following content and go to the next step: </br>', $config_file_path);
		echo '</div>';
		echo '<textarea style="font-size: 24px; 
		font-weight: bold; margin-left: auto; 
		margin-right: auto; text-align: left;
		border: dotted 2px #808080;
		width: 600px; height: 370px"
			readonly="readonly">';
		echo $str;
		//$str = nl2br(htmlspecialchars($str));
		//echo '<tr><td>' . $str . '</td></tr>';
		echo '</textarea><br />';
		echo '<form method="post" action="?step=3"><input style="font-size: 24px; height: 34px;" type="submit" name="submit" value="' . __('Go to the next step') . '" /></form>';
		echo '</div>';
die;
	}
}
function install_database()
{
	//define('CONFIG_FILE_PATH', $root_path . 'install/config.php');

	global $tables, $db, $includes_path;
	foreach ($tables as $name => $table)
	{
		if ($db->table_exists($name))
			$db->delete_table($name);

		$db->create_table($name, $table);
		echo 'Table "'. $name . '" created successfully<br />';
		ob_flush();
	}
	require_once $includes_path . 'user.php';
	user_init_default_grp();
	// XXX TEST
	echo __('Installation completed');//. Please move install/config.php to the top direcotry of orzoj-website and delete "install" directory.');

}

?>

<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=utf-8">
<?php
if (get_magic_quotes_gpc())
{
	if (isset($_POST))
		foreach ($_POST as $key => $v)
			$_POST[$key] = stripslashes($v);
	if (isset($_GET))
		foreach ($_GET as $key => $v)
			$_GET[$key] = stripslashes($v);
	if (isset($_COOKIE))
		foreach ($_COOKIE as $key => $v)
			$_COOKIE[$key] = stripslashes($v);
}
$step = 0;
if (isset($_GET['step']))
{
	$step = (int)($_GET['step']);
}
if ($step < 1) $step =1;
switch ($step)
{
case 1:
?>
<title>Orz Online Judge Installation -- Step 1</title>
</head>
<body style="text-align: center">
<?php
	function get_input($name, $type = 'text', $value = NULL)
	{
		if (!is_null($value))
			$value =" value=\"$value\"";
		echo "<input name=\"$name\" type=\"$type\"$value>";
	}
	function get_select($name, $options)
	{
		echo "<select name=\"$name\">";
		foreach ($options as $option)
			echo "<option value=\"$option[0]\">$option[1]</option>";
		echo '</select>';
	}
	function gen_website_name()
	{
		get_input('website_name');
	}
	function gen_db_type()
	{
		$options = array(
			array('mysql', 'MySQL(&gt;5.0)'),
			array('postgresql', 'PostgreSQL')
		);
		get_select('db_layer', $options);
	}
	function gen_db_host()
	{
		get_input('db_host');
	}
	function gen_db_port()
	{
		get_input('db_port');
	}
	function gen_db_username()
	{
		get_input('db_username');
	}
	function gen_db_password()
	{
		get_input('db_password', 'password');
	}
	function gen_db_name()
	{
		get_input('db_name');
	}
	function gen_table_prefix()
	{
		get_input('table_prefix');
	}
	function gen_website_root()
	{
		get_input('website_root', 'text', '/orzoj');
	}
	$items = array(
		array(__('Website name:'), 'gen_website_name'),
		array(__('Database type:'), 'gen_db_type'),
		array(__('Database host address:'), 'gen_db_host'),
		array(__('Database host port:'), 'gen_db_port'),
		array(__('Database username:'), 'gen_db_username'),
		array(__('Database password:'), 'gen_db_password'),
		array(__('Database name:'), 'gen_db_name'),
		array(__('Table prefix:'), 'gen_table_prefix'),
		array(__('Website root:'), 'gen_website_root')
	);
	echo '<style>';
	echo 'input{height: 28px; font-size: 20px}';
	echo 'select{height: 28px; font-size: 20px}';
	echo 'input[type="submit"]{height: 36px;}';
	echo '</style>';
	echo '<form name="install" method="post" action="?step=2">';
	echo '<table style="font-size: 24px; margin-left: auto; margin-right: auto;">';
	foreach ($items as $item)
	{
		echo '<tr><td>' . $item[0] . '</td><td>';
		$func = $item[1];
		$func();
		echo '</td></tr>';
	}
	echo '<tr><td colspan="2" style="text-align: center;"><input name="submit" type="submit" value="Go to the next step" /></td></tr>';
	echo '</table>';
	echo '</form>';
?>
<?php
	break;
case 2:
?>
<title>Orz Online Judge Installation -- Step 2</title>
</head>
<body>
<?php
	$items = array('db_host', 'db_layer', 'db_port', 
		'db_username', 'db_password', 'db_name', 'table_prefix');
	foreach ($items as $item)
	{
		$flag = FALSE;
		if (array_key_exists($item, $_POST))
			if (!empty($_POST[$item]))
			{
				$$item = $_POST[$item];
				$flag = TRUE;
			}
		if (!$flag)
			die(__('<div style="text-align: center; font-size: 30px;">Incomplete POST: %s does not exist or empty.', $item) . '</div></body></html>');
	}
	try
	{
		conf_file_generate($db_layer, $db_host, $db_port, $db_username,
			$db_password, $db_name, $table_prefix);

		require_once '../pre_include.php';
		require_once 'tables.php';

		install_database();
	}
	catch (Exc_orzoj $e)
	{
		echo '<br />' . nl2br(htmlspecialchars($e->msg()));
	}
	break;
case 3:
	try
	{
		if (!file_exists($config_file_path))
			die(__('Please create `%s` first.', $config_file_path) . '</body></html>');
		require_once '../pre_include.php';
		require_once 'tables.php';

		install_database();
	}
	catch (Exc_orzoj $e)
	{
		die('<br />' . nl2br(htmlspecialchars($e->msg())));
	}

}
?>
</body>
</html>

