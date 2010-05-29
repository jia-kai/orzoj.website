<?php
/* 
 * $File: error.php
 * $Date: Fri May 28 22:39:07 2010 +0800
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
if (!defined('IN_ORZOJ')) exit;

$errormsg = array();

/**
 * Set error message
 * @param string $errmsg new error message
 */
function error_set_message($errmsg)
{
	global $errormsg;
	$errormsg[] = $errmsg;
}

/**
 * Clear error message
 */
function error_clear_message()
{
	global $errormsg;
	$errormsg = array();
}

/**
 * Get latest error message
 * @return string|bool If there'are some error messages,the latest error message is returned.Otherwise,FALSE.
 */
function error_get_latest_message()
{
	global $errormsg;
	if (count($errormsg) > 0)
	{
		$msg = $errormsg[count($errormsg)-1];
		unset($errormsg[count($errormsg)-1]);
		return $msg;
	}
	else
		return false;
}


function error_throw_a_complete_html_page($errorinfo)
{
?>
<html>
<head>
<meta http-equiv="Content-type" content="content-type:text/html;charset=utf-8">
<title>Orz Online Judge -- Error Page</title>
</head>
<body>
Error:An error occurred at <?=strftime('%a %b %d %H:%M:%S %Y')?>.<br>
The detailed infomation about the error is:
<br>
<textarea readonly cols=50 rows=10>
<?=htmlspecialchars($errorinfo)?>
</textarea><br>
Please visit <a href="http://www.marveteam.org/" target="_blank">MarveTeam</a> or <a href="http://orzoj.marveteam.org/" target="_blank">Orz Online Judge</a> for more details.<br>
If you think it a bug,please report this bug to the developers.<br>
</body>
</html>
<?php
}
