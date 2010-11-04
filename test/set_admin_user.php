<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';

$users = array('jiakai', 'test');
foreach ($users as $u)
{
	$uid = user_get_id_by_username($u);
	if ($uid)
		user_set_super_admin($uid);
}

