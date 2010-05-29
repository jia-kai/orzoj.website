<?php
/*
 * $File: index.php
 * $Date: Fri May 28 22:16:24 2010 +0800
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
$step = (int)($_GET['step']);
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
Database Password:<input name="dbpassword" type="text"><br>
Database Name:<input name="dbname" type="text"><br>
Table Prefix:<input name="tablepre" type="text"><br>
<input name="submit" type="submit" value="Go to the next step">
</form>
<?php
	break;
case 2:
?>
<title>Orz Online Judge Installation -- Step 3</title>
</head>
<body>
<?php
	$dbhost = $_POST['dbhost'];
	$dblayer = $_POST['dblayer'];
	$dbport = $_POST['dbport'];
	$dbusername = $_POST['dbusername'];
	$dbpassword = $_POST['dbpassword'];
	$dbname = $_POST['dbname'];
	$tablepre = $_POST['tablepre'];
	$root_path = rtrim(realpath('../'),'/').'/';
	require $root_path.'includes/db/'.$dblayer.'.php';
	$classname = 'dbal_'.$dblayer;
	$db = new $classname;
	if ($db->connect($dbhost,$dbport,$dbusername,$dbpassword,$dbname))
	{
		echo 'Congruatulations!Connect database successfully.<br>';
		$usertable = array(
			'cols' => array(
				'id' => array('type' => 'INT32','auto_assign' => true),
				'username' => array('type' => 'TEXT'),
				'password' => array('type' => 'TEXT'),
				'realname' => array('type' => 'TEXT'),
				'email' => array('type' => 'TEXT'),
				'question' => array('type' => 'TEXT'),
				'answer' => array('type' => 'TEXT'),
				'regtime' => array('type' => 'INT64'),
				'regip' => array('type' => 'TEXT'),
				'lastlogintime' => array('type' => 'INT64'),
				'lastloginip' => array('type' => 'TEXT'),
				'submitamount' => array('type' => 'INT32','default' => 0),
				'acamount' => array('type' => 'INT32','default' => 0),
				'acrate' => array('type' => 'INT32','default' => 0),
				'programminglanguage' => array('type' => 'INT32','default' => 0),
				'checksum' => array('type' => 'TEXT'),
				'usergroup' => array('type' => 'INT32','default' => 0),
				'otherinfo' => array('type' => 'TEXT')
			),
			'primary key' => 'id');
		if ($db->table_exists($tablepre.'users')) $db->delete_table($tablepre.'users');
		if ($db->create_table($tablepre.'users',$usertable))
		{
			echo 'Create user table succesfully.<br>';
			ob_flush();
		}
		else
		{
			echo 'Failed to create user table.Error message:'.htmlspecialchars($db->error()).'<br>';
			die('</body></html>');
		}
		$problemtable =  array(
			'cols' => array(
				'id' => array('type' => 'INT32','auto_assign' => true),
				'title' => array('type' => 'TEXT'),
				'description' => array('type' => 'TEXT'),
				'inputformat' => array('type' => 'TEXT'),
				'outputformat' => array('type' => 'TEXT'),
				'sampleinput' => array('type' => 'TEXT'),
				'sampleoutput' => array('type' => 'TEXT'),
				'hint' => array('type' => 'TEXT'),
				'source' => array('type' => 'TEXT'),
				'submitamount' => array('type' => 'INT32','default' => 0),
				'acamount' => array('type' => 'INT32','default' => 0),
				'acrate' => array('type' => 'INT32','default' => 0),
				'difficulty' => array('type' => 'INT32','default' => 0),
				'contestid' => array('type' => 'INT32','default' => 0),
				'dataid' => array('type' => 'INT32','default' => 0),
				'typeid' => array('type' => 'INT32','default' => 0),
				'problemgroupid' => array('type' => 'INT32','default' => 0),
				'usefile' => array('type' => 'INT32'),
				'inputfile' => array('type' => 'TEXT'),
				'outputfile' => array('type' => 'TEXT'),
				'timelimit' => array('type' => 'TEXT'),
				'memorylimit' => array('type' => 'TEXT'),
				'otherinfo' => array('type' => 'TEXT'),
			),
			'primary key' => 'id'
		);
		if ($db->table_exists($tablepre.'problems')) $db->delete_table($tablepre.'problems');
		if ($db->create_table($tablepre.'problems',$problemtable))
		{
			echo 'Create problem table succesfully.<Br>';
			ob_flush();
		}
		else
		{
			echo 'Failed to create problem table.Error message:'.htmlspecialchars($db->error()).'<Br>';
			die('</body></html>');
		}
		$problemgrouptable = array(
			'cols' => array(
				'id' => array('type' => 'INT32','auto_assign' => true),
				'groupname' => array('type' => 'TEXT'),
				'parent' => array('type' => 'INT32','default' => 0)
				),
			'primary key' => 'id',
		);
		if ($db->table_exists($tablepre.'problemgroups')) $db->delete_table($tablepre.'problems');
		if ($db->create_table($tablepre.'problemgroups',$problemgrouptable))
		{
			echo 'Create Problem Group table succesfully.<Br>';
			ob_flush();
		}
		else
		{
			echo 'Failed to create problem group table.Error message:'.htmlspecialchars($db->error()).'<Br>';
			die('</body></html>');
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
