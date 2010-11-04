<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';

define('NRAND', 40);

$start = GID_UINFO_VIEWER + 1;

for ($i = 0; $i < NRAND; $i ++)
{
	$pgid = rand(-1, $i - 1);
	if ($pgid == -1)
		$pgid = GID_ALL;
	else $pgid += $start;
	$id = $db->insert_into('user_grps', array(
		'pgid' => $pgid,
		'name' => rand(),
		'desc' => rand()
	));
	user_update_grp_cache_add($id);
}

