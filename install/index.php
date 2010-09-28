<?php
/* 
 * $File: index.php
 * $Date: Tue Sep 28 15:58:08 2010 +0800
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
define('IN_ORZOJ',true);
header('Content-type:text/html;charset=utf-8');

function conf_file_generate($db_layer,$db_host,$db_port,$db_username,$db_password,$db_name,$table_prefix)
{
	$db_username = addslashes($db_username);
	$db_password = addslashes($db_password);
	$db_name = addslashes($db_name);
	$table_prefix = addslashes($table_prefix);
	$str = <<<EOF
<?php
\$db_type = '$db_layer';
\$db_host = '$db_host';
\$db_port = '$db_port';
\$db_user = '$db_username';
\$db_password = '$db_password';
\$db_dbname = '$db_name';
\$table_prefix = '$table_prefix';
EOF;
	$fptr = fopen("config.php", "w");
	if ($fptr)
	{
		fprintf($fptr, "%s", $str);
		fclose($fptr);
	}
	else
		throw new Exc_orzoj('failed to write configuration file');
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
<body>
<form name="install" method="post" action="?step=2">
Database Type:<select name="db_layer"><option value="mysql">MySQL(&gt;5.0)</option><option value="postgresql">PostgreSQL</option></select>More database layer is around the cornor<br />
Database Host Address:<input name="db_host" type="text"><br />
Database Host Port:<input name="db_port" type="text"><br />
Database Username:<input name="db_username" type="text"><br />
Database Password:<input name="db_password" type="password"><br />
Database Name:<input name="db_name" type="text"><br />
Table Prefix:<input name="table_prefix" type="text"><br />
<input name="submit" type="submit" value="Go to the next step">
</form>
<?php
	break;
case 2:
?>
<title>Orz Online Judge Installation -- Step 2</title>
</head>
<body>
<?php
	$db_host = $_POST['db_host'];
	$db_layer = $_POST['db_layer'];
	$db_port = $_POST['db_port'];
	$db_username = $_POST['db_username'];
	$db_password = $_POST['db_password'];
	$db_name = $_POST['db_name'];
	$table_prefix = $_POST['table_prefix'];
	try
	{
		$root_path = rtrim(realpath('..'),'/').'/';
		$includes_path = $root_path . 'includes/';

		require_once $includes_path . 'exception.php';
		require_once $includes_path . 'l10n.php';
		require_once $includes_path . 'db/'.$db_layer.'.php';
		require_once 'tables.php';

		conf_file_generate($db_layer, $db_host, $db_port, $db_username,
			$db_password, $db_name, $table_prefix);


		$classname = 'Dbal_'.$db_layer;
		$db = new $classname;


		$db->connect($db_host, $db_port, $db_username, $db_password, $db_name);
		$db->set_prefix($table_prefix);
		foreach ($tables as $name => $table)
		{
			if ($db->table_exists($name))
				$db->delete_table($name);

			$db->create_table($name, $table);
			echo 'Table "'. $name . '" created successfully<br />';
			ob_flush();
		}
		echo 'Installation completed. Please move install/config.php to the top direcotry of orzoj-website ' .
			'and delete "install" directory.';
	}
	catch (Exc_orzoj $e)
	{
		echo '<br />' . nl2br(htmlspecialchars($e->msg()));
	}
}
?>
</body>
</html>

