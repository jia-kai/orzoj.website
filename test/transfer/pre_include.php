<?php
require_once '../../pre_include.php';
require_once $includes_path . 'user.php';

$odb = new Dbal_mysql();
$odb->connect($db_host, $db_port, $db_user, $db_password, 'orzoj_ancient');

$trans_plang = array(1 => 3, 2 => 2, 3 => 1);

$rows = $odb->select_from('problem');
foreach ($rows as $row)
	if (!$odb->get_number_of_rows('problemdata', array($DBOP['='], 'problemid', $row['id'])))
	{
		echo "Warning: $row[title] deleted";
		$odb->delete_item('problem', array($DBOP['='], 'id', $row['id']));
	}

$rows = $odb->select_from('problemdata');
foreach ($rows as $row)
	if (!$odb->get_number_of_rows('problem', array($DBOP['='], 'id', $row['problemid'])))
	{
		echo "Warning: no problem for $row[name]\n";
	}

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
	$row = $odb->select_from('problemdata', 'name', array(
		$DBOP['='], 'problemid', $pid));
	if (empty($row))
		return NULL;
	return $row[0]['name'];
	/*
	if (empty($row))
		return NULL;
	$row = $row[0]['inputfile'];
	return substr($row, 0, strpos($row, '.'));
	 */
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

