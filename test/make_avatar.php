<?php

require_once '../pre_include.php';

for ($i = 0; $i < 100; $i ++)
	$db->insert_into('user_avatars', array('file' => 'default.gif'));
