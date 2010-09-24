<?php
/* 
 * $File: msg.php
 * $Date: Fri Sep 24 17:25:07 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package orzoj-message
 * @license http://gnu.org/licenses GNU GPLv3
 * @version phpweb-1.0.0alpha1
 * @copyright (C) Fan Qijiang
 * @author Fan Qijiang <fqj1994@gmail.com>
 */
/*
	Orz Online Judge is a cross-platform programming online judge.
	Copyright (C) <2010>  (Fan Qijiang) <fqj1994@gmail.com>

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

ob_start();
define('IN_ORZOJ',true);
$root_path = dirname(__FILE__) . '/';
$include_path = $root_path . 'includes/';

define('STATUS_OK',0);
define('STATUS_ERROR',1);

define('VERSION',1);


require_once $root_path.'config.php';
require_once $include_path.'common.php';
require_once $include_path.'db/'.$db_type.'.php';
require_once $include_path.'xml.php';
$dbclass = 'dbal_'.$db_type;
$db = new $dbclass;
if ($db->connect($db_host,$db_port,$db_user,$db_password,$db_dbname))
{
	unset($db_password);
}
else
{
	exit('0');
}



if (isset($_REQUEST['action']))
{
	if (!isset($_REQUEST['version'])) exit('0');
	if (VERSION != $_REQUEST['version']) exit('0');
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

if (isset($_REQUEST['data']))
{
	$data = json_decode($_REQUEST['data']);
	if (isset($data->thread_id) && isset($data->req_id) && isset($data->data) && isset($data->checksum))
	{
		$dynamic_password = option_get('dynamic_password');
		$stdchecksum = sha1($data->thread_id . $data-> req_id . sha1($dynamic_password . $static_password) . $data->data);
		if ($stdchecksum == $data->checksum)
		{
		}
		else
			exit('0');
	}
	else
	{
		exit('0');
	}
}
else
{
	exit('1');
}
