<?php
require_once '../pre_include.php';
require_once '../install/tables.php';
foreach ($tables as $name=>$val)
	$db->delete_item($name);
