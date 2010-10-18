<?php

require_once '../pre_include.php';
$db->delete_item('users');
for ($i = 1; $i <= 123; $i ++)
	$db->insert_into('users', 
	array(
		'username' => "user $i",
		'realname' => "realname $i",
		'nickname' => "nickname $i",
		'passwd' => "passwd $i",
		'salt' => "salt $i",
		'aid' => rand(1, 2),
		'email' => "email $i",
		'self_desc' => "self_desc $i",
		'plang' => rand(1, 3),
		'wlang' => rand(1, 2),
		'view_gid' => serialize(array(GID_ALL)),
		'tid' => 1,
		'reg_time' => time(),
		'reg_ip' => '127.0.0.1',
		'last_login_time' => time(),
		'last_login_ip' => '127.0.0.1',
		'cnt_submit' => rand(1, 100),
		'cnt_ac' => rand(1, 100),
		'cnt_unac' => rand(1, 100),
		'cnt_ce' => rand(1, 100),
		'ac_ratio' => rand(1, 10000)
	)
);

