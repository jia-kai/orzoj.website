<?php
require_once '../pre_include.php';

$n = 1000;
for ($i = 1; $i <= $n; $i ++)
	$db->insert_into('announcements', array('id' => $i, 'content' => 'hello'));
