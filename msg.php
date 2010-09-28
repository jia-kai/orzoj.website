<?php
/* 
 * $File: msg.php
 * $Date: Tue Sep 28 16:47:30 2010 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'pre_include.php';
require_once $includes_path . 'msg_func.php';

define('MSG_VERSION', 1);

/* MSG_STATUS */
define('MSG_STATUS_OK', 0);
define('MSG_STATUS_ERROR', 1);


if (isset($_REQUEST['action'])) // login
{
	if (!isset($_REQUEST['version'])) exit('0');
	if (MSG_VERSION != $_REQUEST['version']) exit('0');
	if ($_REQUEST['action'] == 'login1')
	{
		$tmp_dynamic_password = option_get('tmp_dynamic_password');
		$dp = unserialize($tmp_dynamic_password);
		if (is_array($dp) && time() - $dp['time']  < 10)
		{
			exit($dp['password']);
		}
		else
		{
			mt_srand(time());
			$password = (uniqid(mt_rand(),true));
			$newpassword = array('time' => time(),'password' => $password);
			option_set('tmp_dynamic_password',serialize($newpassword));
			exit($password);
		}
	}
	else if ($_REQUEST['action'] == 'login2')
	{
		$tmp_dynamic_password = option_get('tmp_dynamic_password');
		$dp = unserialize($tmp_dynamic_password);
		$stdchecksum = sha1(sha1($dp['password'] . $static_password));
		if (!isset($_REQUEST['checksum'])) exit('0');
		else
		{
			$verify = sha1(sha1($dp['password']) . $static_password);
			if ($_REQUEST['checksum'] == $stdchecksum)
			{
				option_set('dynamic_password',$dp['password']);
				exit($verify);
			}
			else exit('0');
		}
	}
}


// xxx --test--
$func_param->task = 1;
$func_param->msg = "error";
$func_param->judge = 1;

call_func('report_error');
/*
if (isset($_REQUEST['data'])) // decode data from $_REQUEST
{
	$data = json_decode($_REQUEST['data']);
	if (isset($data->thread_id) && isset($data->req_id) && isset($data->data) && isset($data->checksum))
	{
		$thread_id = $data->thread_id;
		$req_id = $data->req_id;
		$dynamic_password = option_get('dynamic_password');
		// FIXME: check $req_id increment
		$stdchecksum = sha1($data->thread_id . $data-> req_id . sha1($dynamic_password . $static_password) . $data->data);
		if ($stdchecksum != $data->checksum)
			exit('0');
	}
	else
		exit('0');

	$func_param = json_decode($data->data);

	// use $func_param as a global variable
	// calling functions are in msg_function.php
	call_user_func($func_param->action);
}
else
	exit('1');
 */

