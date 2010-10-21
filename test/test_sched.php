<?php
/*
 * $File: test_sched.php
 * $Date: Thu Oct 21 18:42:19 2010 +0800
 */

require_once $includes_path . 'sched.php';
if (!defined('IN_ORZOJ'))
{
	require_once '../pre_include.php';
	var_dump(sched_add(time() + 10, __FILE__, 'func', array(1, 2, 'hello')));
}


function func($a)
{
	throw new Exc_runtime('just for test. arg=' . $a);
}

