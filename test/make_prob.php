<?php
require_once '../pre_include.php';

$db->insert_into('problems',
	array(
		'title' => 'A+B Problem',
		'code' => 'a+b',
		'perm' => serialize(array(0, 1, array(GID_ALL), array())),
		'io' => '',
		'time' => time()
	));

