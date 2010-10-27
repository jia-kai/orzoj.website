<?php
require_once 'pre_include.php';

$rows = $odb->select_from('users');

foreach ($rows as $row)
	$db->insert_into('users', array(
		'username' => strtolower($row['username']),
		'realname' => htmlencode($row['realname']),
		'nickname' => htmlencode('nick-' . $row['username']),
		'passwd' => 'old:' . $row['password'],
		'salt' => 'x',
		'aid' => rand(1, 2),
		'email' => 'unknown@gmail.com',
		'self_desc' => 'xxxx',
		'plang' => $trans_plang[$row['language']],
		'wlang' => 2,
		'view_gid' => json_encode(array()),
		'reg_time' => time(),
		'reg_ip' => 'transferred from old orzoj'
	));

