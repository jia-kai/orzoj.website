<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';

$users = array('jiakai');
foreach ($users as $u)
{
	$uid = user_get_id_by_username($u);
	if ($uid)
		for ($i = GID_START_ADMIN; $i <= GID_END_ADMIN; $i ++)
			$db->insert_into('map_user_grp', array(
				'uid' => $uid,
				'gid' => $i,
				'pending' => 0,
				'admin' => 1
			));
}

