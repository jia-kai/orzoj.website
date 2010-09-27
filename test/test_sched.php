<?php
/*
 * $File: test_sched.php
 * $Date: Mon Sep 27 20:36:46 2010 +0800
 */

require_once '../pre_include.php';

require_once $includes_path . 'sched.php';

function func()
{
	echo 'func executed with args:' . "\n";
	print_r(func_get_args());
	echo "\n";
	return 1;
}

if (isset($_GET['do']))
{
	var_dump(sched_add(time() + 30, __FILE__, 'func', array(1, 2, 'hello')));
} else
	var_dump(sched_work());

?>

