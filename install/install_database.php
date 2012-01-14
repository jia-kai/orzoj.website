<?php
/* 
 * $File: install_database.php
 * $Date: Fri Jan 06 16:28:49 2012 +0800
 */
/**
 * @package orzoj-website
 * @subpackage install
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

if (!defined('IN_ORZOJ'))
	exit;

function add_plang_wlang()
{
	global $db;
	$db->insert_into('plang', array('name' => 'g++', 'type' => 'cpp'));
	$db->insert_into('plang', array('name' => 'gcc', 'type' => 'c'));
	$db->insert_into('plang', array('name' => 'fpc', 'type' => 'pas'));

	$db->insert_into('wlang', array('name' => 'English', 'file' => 'en_US'));
	$db->insert_into('wlang', array('name' => '简体中文(中国大陆)', 'file' => 'zh_CN'));
	$db->insert_into('wlang', array('name' => '正體中文(中國臺灣)', 'file' => 'zh_TW'));
}

function add_avatar()
{
	global $db;
	$db->insert_into('user_avatars', array('file' => 'default.gif'));
}

function add_options()
{
	option_set('judge_info_list', serialize(array('platform', 'description', 'cpuinfo', 'meminfo')));
	option_set('static_password', 'hello');
	option_set('email_validate_no_dns_check', '1');
	option_set('max_src_length', 1024 * 32);
	option_set('orz_thread_reqid_max_size', 100);
	option_set('orzoj_server_max_rint', 20);
}

function make_src_code($lang, $src, $hint = '')
{
	return '<h4>' . $lang . ':</h4>'
		. '<textarea class="prob-view-single-io" readonly="readonly">'
		. $src
		. '</textarea>'
		. "<p>$hint</p>";
}
function get_src($lang, $io = '')
{
	if ($io != '')
	{
		$io = unserialize($io);
		$input = $io[0];
		$output = $io[1];
	}
	switch ($lang)
	{
	case 'c++':
		if ($io == '')
			return <<<EOF
#include <iostream>

using namespace std;

int main()
{
int a, b;
cin >> a >> b;
cout << a + b << endl;
return 0;
}
EOF;
		else
		{
			return <<<EOF
#include <fstream>

using namespace std;

int main()
{
ifstream fin("$input");
ofstream fout("$output");
int a, b;
fin >> a >> b;
fout << a + b << endl;
return 0;
}
</textarea>
<p>or a alternative way:</p>
<textarea class="prob-view-single-io" readonly="readonly">
#include <iostream>
#include <cstdio>

using namespace std;

int main()
{
freopen("$input", "r", stdin);
freopen("$output", "w", sdout);
int a, b;
cin >> a >> b;
cout << a + b << endl;
return 0;
}
</textarea>
EOF;
		}
		break;
	case 'c':
		if ($io == '')
			return <<<EOF
#include <stdio.h>

int main()
{
int a, b;
scanf("%d%d", &a, &b);
printf("%d\\n", a + b);
return 0;
}

EOF;
		else
			return <<<EOF
#include <stdio.h>

int main()
{
FILE* fin = fopen("$input", "r"),
	fout = fopen("$output", "w');
int a, b;
fscanf(fin, "%d%d", &a, &b);
fprintf(fout, "%d\\n", a + b);
fclose(fin);
fclose(fout);
return 0;
}
</textarea>
<p>or:</p>
<textarea class="prob-view-single-io" readonly="readonly">
#include <stdio.h>

int main()
{
freopen("$input", "r", stdin);
freopen("$output", "w", stdout);
int a, b;
scanf("%d%d", &a, &b);
printf("%d\\n", a + b);
return 0;
}
EOF;
		break;
	case 'pascal':
		if ($io == '')
			return <<<EOF
program ab;
var		a, b: longint;
begin
	readln(a, b);
	writeln(a + b);
end.
EOF;
		else return <<<EOF
program ab;
var		a, b: longint;
begin
	assign(input, '$input');
	reset(input);
	assing(output, '$output');
	rewrite(output);
	readln(a, b);
	writeln(a + b);
	close(input);
	close(output);
end.
EOF;
		break;
	}
}
function make_hint($io = '')
{
	$hint = __('code for this problem:') . '<br />';
	$hint .= make_src_code('c++', get_src('c++', $io), __('and some other ways.'));
	$hint .= make_src_code('c', get_src('c', $io));
	$hint .= make_src_code('pascal', get_src('pascal', $io));
	return $hint;
}
function add_prob_a_plus_b()
{
	global $db, $PROB_DESC_FIELDS_ALLOW_XHTML;
	//$hint .= add_hint(__('Where are the input and output?'), __('The input and output is determined by the problem setting, either file or from standard input and standard output.'));
	foreach (array(
		array(__('A+B Problem'), 'a+b', ''),
		array(__('A+B Problem(use file)'), 'a+b2', serialize(array('a+b.in', 'a+b.out')))
		) as $prob)
	{
		$title = $prob[0];
		$code = $prob[1];
		$io = $prob[2];
		$desc = array(
			'time' => '1s',
			'memory' => '256MB',
			'desc' => 'Calculate a + b',
			'input_fmt' => 'Two numbers in a single row.',
			'output_fmt' => 'A number, the sum of a and b.',
			'input_samp' => '1 1',
			'output_samp' => '2',
			'source' => 'Every OJ',
			'range' => '1 <= a, b <= 10',
			'hint' => make_hint($io)
		);
		foreach ($desc as $key => &$val)
			if (!in_array($key, $PROB_DESC_FIELDS_ALLOW_XHTML))
				$val = htmlencode($val);
		$db->insert_into('problems',
			array('title' => $title,
			'code' => $code,
			'perm' => serialize(array(0, 1, array(GID_ALL), array())),
			'io' => $io,
			'time' => time(),
			'desc' => serialize($desc)
		));
	}
}

function add_post_topic_welcome()
{
	global $db, $DBOP, $website_name;
	$time = time();
	$uid = 0;
	$prob_id = 0;
	$is_top = 0;
	$priority = 0;
	$is_locked = 0;
	$is_elaborate = 0;
	$type = 1;
	$subject = __('Welcome to %s!', $website_name);

	$last_reply_time = $time;
	$last_reply_user = 0;

	$val = array();
	foreach (array('time', 'uid', 'prob_id', 
		'priority', 'is_top', 'is_locked', 'is_elaborate',
		'type', 'subject', 'last_reply_time', 'last_reply_user') as $item)
		$val[$item] = $$item;
	$tid = $db->insert_into('post_topics', $val);

	$val = array();
	$floor = 1;
	$content = __('Welcome to %s!', $website_name);

	$last_modify_time = 0;
	$last_modify_user = 0;

	foreach (array('time', 'uid', 'floor', 'tid', 
		'content', 'last_modify_time', 'last_modify_user') as $item)
		$val[$item] = $$item;

	$db->insert_into('posts', $val);

}

function add_user_admin()
{
	global $db;
	$id = $db->insert_into('users',
		array(
			'username' => 'admin',
			'realname' => 'Administrator',
			'nickname' => 'The God',
			'passwd' => _user_make_passwd('admin', 'admin888'),
			'aid' => 1,
			'email' => '',
			'self_desc' => 'The god.',
			'plang' => 1,
			'wlang' => 1,
			'view_gid' => json_encode(array(GID_ALL)),
			'reg_time' => time(),
			'reg_ip' => get_remote_addr(),
			'last_login_time' => time(),
			'last_login_ip' => get_remote_addr(),
			'ac_ratio' => 0
		)
	);
	user_set_super_admin($id);
}

define('IN_INSTALLATION', TRUE);
require_once $root_path . 'pre_include.php';
require_once $includes_path . 'problem.php';
function install_database()
{
	global $includes_path, $config_items, $db;
	try
	{
		require_once 'tables.php';
		foreach ($config_items as $item)
		{
			$name = $item[1];
			global $$name;
		}
		try
		{
			db_init();
		} catch (Exc_db $e)
		{
			throw $e;
			throw new Exc_db(__('Failed to connect to database.'));
		}
		foreach ($tables as $name => $table)
		{
			if ($db->table_exists($name))
				$db->delete_table($name);
			$db->create_table($name, $table);
		}
		require_once $includes_path . 'user.php';
		user_init_default_grp();
		add_options();
		add_plang_wlang();
		add_avatar();
		add_prob_a_plus_b();
		add_post_topic_welcome();
		add_user_admin();
	}
	catch (Exc_orzoj $e)
	{
		echo $e->msg() . '<br />';
		echo __('Failed to install.') . '<br />';
		echo __('Please check if configurations are correct.') . '<br />';
		return FALSE;
	}
	return TRUE;
}

