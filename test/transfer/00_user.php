<?php
require_once 'pre_include.php';
require_once $includes_path . 'user.php';

$rows = $odb->select_from('users');

foreach ($rows as $row)
{
	$realname = $row['realname'];
	if (strlen($realname) > REALNAME_LEN_MAX)
		$realname = 'too long';
	$realname = htmlencode($realname);

	$nickname = 'nickname-' . $row['username'];
	if (strlen($nickname) > NICKNAME_LEN_MAX)
		$nickname = 'too long';
	$nickname = htmlencode($nickname);

	$uid = $db->insert_into('users', array(
		'username' => odb_convert_username($row['username'], $row['id']),
		'realname' => $realname,
		'nickname' => $nickname,
		'passwd' => 'old:' . $row['password'],
		'salt' => 'x',
		'aid' => rand(1, 2),
		'email' => 'unknown@gmail.com',
		'self_desc' => 'no desc now',
		'plang' => $trans_plang[$row['language']],
		'wlang' => 2,
		'view_gid' => json_encode(array(GID_ALL, GID_GUEST)),
		'reg_time' => time(),
		'reg_ip' => 'transferred from old orzoj'
	));

	if ($row['usergroup'] == 2)
		user_set_super_admin($uid);
}

