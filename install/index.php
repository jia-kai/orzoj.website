<?php
/*
 * $File: index.php
 * $Date: Mon Sep 27 11:40:51 2010 +0800
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
?>
EOF;
	$fptr = fopen("config.php", "w");
	if ($fptr)
	{
		fprintf($fptr, "%s", $str);
		fclose($fptr);
		return TRUE;
	}
	else
		return FALSE;
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
	require_once 'tables.php';
	$db_host = $_POST['db_host'];
	$db_layer = $_POST['db_layer'];
	$db_port = $_POST['db_port'];
	$db_username = $_POST['db_username'];
	$db_password = $_POST['db_password'];
	$db_name = $_POST['db_name'];
	$table_prefix = $_POST['table_prefix'];

	$root_path = rtrim(realpath('../'),'/').'/';
	$includes_path = $root_path . 'includes/';
	require $root_path.'includes/db/'.$db_layer.'.php';
	$classname = 'dbal_'.$db_layer;
	$db = new $classname;

	if ($db->connect($db_host, $db_port, $db_username, $db_password, $db_name))
	{
		$db->set_prefix($table_prefix);
		foreach ($tables as $name => $table)
		{
			if ($db->table_exists($name))
				$db->delete_table($name);

			if ($db->create_table($name, $table))
			{
				echo 'Create '. $name . ' successfully.<br />';
				ob_flush();
			}
			else
			{
				echo 'Failed to create table "' . $name .
					'": ' . htmlspecialchars($db->error());
				die('</body></html>');
			}
		}
		if (!conf_file_generate($db_layer, $db_host, $db_port, $db_username,
			$db_password, $db_name, $table_prefix))
			echo 'Faied to write configuration file';
		else
			echo "Please move config.php to the top directory of orzoj and delete install directory.";
	}
	else
		echo 'Failed to connect to database.';
	break;
}
?>
</body>
</html>

