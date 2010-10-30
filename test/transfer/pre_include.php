<?php
require_once '../../pre_include.php';
require_once $includes_path . 'user.php';

$odb = new Dbal_mysql();
$odb->connect($db_host, $db_port, $db_user, $db_password, 'orzoj');

$trans_plang = array(1 => 3, 2 => 2, 3 => 1);

function odb_convert_username($name, $id)
{
	try
	{
		user_validate_username($name);
		return strtolower($name);
	} catch (Exc_orzoj $e)
	{
		return "user.$id";
	}
}

function odb_get_prob_code($pid)
{
	global $odb, $DBOP;
	$row = $odb->select_from('problem', 'inputfile', array(
		$DBOP['='], 'id', $pid));
	if (empty($row))
		return NULL;
	$row = $row[0]['inputfile'];
	return substr($row, 0, strpos($row, '.'));
}

function odb_user_get_name_by_id($uid)
{
	global $odb, $DBOP;
	$row = $odb->select_from('users', 'username', array(
		$DBOP['='], 'id', $uid));
	if (empty($row))
		return NULL;
	return odb_convert_username($row[0]['username'], $uid);
}

function odb_simulate_user_login($username)
{
	global $_user_check_login_result, $user;
	if (is_null($username))
		die('no such user');
	$_user_check_login_result = TRUE;
	$user = new User(user_get_id_by_username($username));
}

