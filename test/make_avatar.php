<?php

require_once '../pre_include.php';

for ($i = 0; $i < 20; $i ++)
	$db->insert_into('user_avatars', array('file' => $i & 1 ? 'default-rev.gif' : 'default.gif'));
