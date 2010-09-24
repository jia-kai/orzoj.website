<?php
/*
 * $File: index.php
 * $Date: Fri Sep 24 17:28:22 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 * $License: http://gnu.org/licenses GNU GPLv3
 */
ob_start();
define('IN_ORZOJ',true);
header('Content-type:text/html;charset=utf-8');
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
if ($step < 1) $step =1 ;
switch ($step)
{
case 1:
?>
<title>Orz Online Judge Installation -- Step 1</title>
</head>
<body>
<form name="install" method="post" action="?step=2">
Database Type:<select name="dblayer"><option value="mysql">MySQL(&gt;5.0)</option><option value="postgresql">PostgreSQL</option></select>More database layer is around the cornor<Br>
Database Host Address:<input name="dbhost" type="text"><br>
Database Host Port:<input name="dbport" type="text"><Br>
Database Username:<input name="dbusername" type="text"><Br>
Database Password:<input name="dbpassword" type="password"><br>
Database Name:<input name="dbname" type="text"><br>
Table Prefix:<input name="tablepre" type="text"><br>
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
	require_once "sql.php";
	require_once "generate_config.php";
	$dbhost = $_POST['dbhost'];
	$dblayer = $_POST['dblayer'];
	$dbport = $_POST['dbport'];
	$dbusername = $_POST['dbusername'];
	$dbpassword = $_POST['dbpassword'];
	$dbname = $_POST['dbname'];
	$tablepre = $_POST['tablepre'];
	conf_file_generate($dblayer,$dbhost,$dbport,$dbusername,$dbpassword,$dbname,$tablepre);
	$root_path = rtrim(realpath('../'),'/').'/';
	require $root_path.'includes/db/'.$dblayer.'.php';
	$classname = 'dbal_'.$dblayer;
	$db = new $classname;
	if ($db->connect($dbhost,$dbport,$dbusername,$dbpassword,$dbname))
	{
		$tables = array(
			array('options','Option Table',$optiontable),
			array('users','User Table',$usertable),
			array('problems','Problem Table',$problemtable),
			array('problemgroups','Problem Group Table',$problemgrouptable),
			array('contets','Contest Table',$contesttable),
			array('problem_contest_relationships','Problem Contest Relationship Table',$problemcontestbindtable),
			array('problem_problemgroup_relationships','Problem-ProblemGroup Relationship Table',$problem_pbgroup_relationshiptable),
			array('judges','Judge Table',$judge_table)
			);
		foreach ($tables as $table)
		{
			if ($db->table_exists($tablepre.$table[0])) $db->delete_table($tablepre.$table[0]);
			if ($db->create_table($tablepre.$table[0],$table[2]))
			{
				echo 'Create '.$table[1].' successfully.<br>';
				ob_flush();
			}
			else
			{
				echo 'Failed to create '.$table[1].'.Error information:'.htmlspecialchars($db->error());
				die('</body></html>');
			}
		}
	}
	else
	{
		echo 'Failed to connect database.';
	}
	break;
}
?>
</body>
</html>
