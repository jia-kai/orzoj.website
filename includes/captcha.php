<?php
/* 
 * $File: captcha.php
 * $Date: Thu Nov 11 13:44:47 2010 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj.

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

if (!defined('IN_ORZOJ'))
	exit;

require_once $includes_path . 'plugin.php';

$_captcha_types = array();

/**
 * Get the html code of a captcha
 * @param string $name the name of a captcha (depends on you)
 * @param string $type type of the captcha (e.g. register,login,admin_login,submit_prob,post_topic)
 * @return string The HTML Code
 */
function captcha_get_html($name, $type)
{
	global $_captcha_types;
	$html = '';
	if (isset($_captcha_types[$type]) && count($_captcha_types[$type]))
	{
		foreach ($_captcha_types[$type] as $funcs)
		{
			$html .= $funcs['get_html_func']($name);
		}
	}
	else
	{
		$html = _captcha_system_get_html($name);
	}
	return $html;
}

/**
 * Verify the captcha
 * @param string $name name of a captcha(@see captcha_get_html)
 * @param string $type type of this captcha(@see captcha_get_html)
 * @return TRUE on success.An exception will be thrown
 * @exception Exc_captcha if verification fails.
 */
function captcha_verify($name, $type)
{
	global $_captcha_types;
	$result = TRUE;
	if (isset($_captcha_types[$type]) && count($_captcha_types[$type]))
	{
		foreach ($_captcha_types[$type] as $funcs)
		{
			$result = $result && $funcs['verify_func']($name);
			if (!$result) break;
		}
	}
	else
	{
		$result = _captcha_system_verify($name);
	}
	return $result;
}

/**
 * Register two functions for generating and verifying captcha to replace the system's
 * @param string $type Type of captcha which you want to replace
 * @param string $get_html_func which function to call to generate HTML.
 * @param string $verify_func which function to call to verify.
 */
function captcha_register($type, $get_html_func, $verify_func)
{
	global $_captcha_types;
	if (!isset($_captcha_types[$type])) $_captcha_types[$type] = array();
	$_captcha_types[$type][] = array('get_html_func' => $get_html_func,'verify_func' => $verify_func);
}

/**
 * @ignore
 */
function _captcha_system_get_html($name)
{
	mt_srand(time());
	$ways = array(
		__('%s + %s = ','%d','%d'),
		__('What\'s the answer of %s plus %s ?','%d','%d'),
		__('Please tell me the answer of %s plus %s.','%d','%d'),
		__('What equals to the answer of %s plus %s ?','%d','%d')
		);
	shuffle($ways);
	$num1 = mt_rand(0,20);
	$num2 = mt_rand(0,20);
	cookie_set(md5('orzoj_system_captcha_'.$name),md5($num1+$num2));
	return sprintf($ways[0] . ' <input name="orzoj_system_captcha_'.$name.'" type="text"/>',$num1,$num2);
}


/**
 * @ignore
 */
function _captcha_system_verify($name)
{
	$answer = cookie_get(md5('orzoj_system_captcha_'.$name));
	if ($answer == md5(trim($_REQUEST['orzoj_system_captcha_'.$name]))) 
		return true;
	else
		return false;
}


/**
 * @ignore
 */
function _problem_captcha_html($str,$pid)
{
	$html = captcha_get_html('prob_submit_'.$pid,'problem_submit');
	return $str.tf_form_get_raw(__('CAPTCHA'),$html);
}

/**
 * @ignore
 */
function _problem_captcha_verify($pid)
{
	$ok = captcha_verify('prob_submit_'.$pid,'problem_submit');
	if ($ok)
		return;
	else
		throw new Exc_captcha(__('Wrong CAPTCHA'));
}

/**
 * @ignore
 */
function _post_add_captcha_html($str)
{
	$pid = uniqid();
	$html = captcha_get_html('post_add_'.$pid,'post_add');
	return $str.tf_form_get_raw(__('CAPTCHA'),$html.'<input name="post_captcha_id" type="hidden" value="'.$pid.'"/>');
}

/**
 * @ignore
 */
function _post_add_captcha_verify($val)
{
	$pid = $_REQUEST['post_captcha_id'];
	$ok = captcha_verify('post_add_'.$pid,'post_add');
	if ($ok)
		return $val;
	else
		throw new Exc_captcha(__('Wrong CAPTCHA'));
}

/**
 * @ignore
 */
function _post_reply_captcha_html($val, $tid)
{
	$html = captcha_get_html('post_reply_'.$tid,'post_reply');
	return $val.tf_form_get_raw(__('CAPTCHA'),$html);
}

/**
 * @ignore
 */
function _post_reply_verify($tid)
{
	$ok = captcha_verify('post_reply_'.$tid,'post_reply_');
	if ($ok)
		return;
	else
		throw new Exc_captcha(__('Wrong CAPTCHA'));
}


filter_add('after_submit_src_form','_problem_captcha_html');
filter_add('before_submit_src','_problem_captcha_verify');
filter_add('after_post_add_form','_post_add_captcha_html');
filter_add('before_post_topic_add','_post_add_captcha_verify');
filter_add('after_post_reply_form', '_post_reply_captcha_html');
filter_add('before_post_reply','_post_reply_verify');
