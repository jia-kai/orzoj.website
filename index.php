<?php
/* 
 * $File: index.php
 * $Date: Fri May 28 22:35:50 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package orzoj-phpwebsite
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

define('IN_ORZOJ',true);
$root_path = dirname(__FILE__).'/';
$includes_path = $root_path.'includes/';

require_once $root_path.'config.php';
require_once $includes_path.'db/'.$db_type.'.php';
require_once $includes_path."error.php";

$dbclass = 'dbal_'.$db_type;
$db = new $dbclass;
if ($db->connect($db_host,$db_port,$db_user,$db_password,$db_name))
{
	$connect_result = true;
	unset($db_password);
}
else
{
	$connect_result = false;
	error_throw_a_complete_html_page(<<<EOF
Error to establish a connection to the database.
Please check following instruments.
1.Do you select the right kind of database layer?
2.Do you input the right host address and port?
3.Do you input the right username and password?
4.Do you input the right name of the database/scheme?
If all answers are "yes",please contact to the host provider to help solve the problem.If you think it a bug,please report it to us at http://www.marveteam.org/ or http://orzoj.marvateam.org/.
EOF
);
}

$action = NULL;

switch ($_GET['action'])
{
case 'problemview':
	break;
default:
	$action = 'index';
}


