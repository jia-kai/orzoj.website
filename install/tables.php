<?php
/* 
 * $File: tables.php
 * $Date: Mon Sep 27 20:19:44 2010 +0800
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

require_once realpath('..') . '/includes/const.inc.php';

define('GID_ADMIN', 1); // admin group id
define('GID_LOCK', 2); // locked group id
define('GID_ALL', 3); // every should be in this group
define('GID_NONE', 4); // nobody should be in this group

$tables = array(
	/* jobs */
	'jobs'=> array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'time' => array('type' => 'INT64'),
			'file' => array('type' => 'TEXT'), // file where the function is, relative to $root_path
			'func' => array('type' => 'TEXT'), // function name
			'args' => array('type' => 'TEXT') // serialized array
		),
		'primary_key' => 'id'
	),

	/* users */
	'users' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'username' => array('type' => 'TEXT'),
			'passwd' => array('type' => 'TEXT'),	// u_make_pass()
			'realname' => array('type' => 'TEXT'),
			'aid' => array('type' => 'INT32'), // avatar id
			'email' => array('type' => 'TEXT'),
			'self_desc' => array('type' => 'TEXT'), // self description
			'tid' => array('type' => 'INT32'), // team id
			'reg_time' => array('type' => 'INT64'), // register time
			'reg_ip' => array('type' => 'TEXT'), // register ip
			'plang' => array('type' => 'INT32'), // preferred programming language 
			'wlang' => array('type' => 'INT32'), // website language
			'last_login_time' => array('type' => 'INT64'),
			'last_login_ip' => array('type' => 'TEXT'),
			'submit_amount' => array('type' => 'INT32', 'default' => 0),
			'ac_amount' => array('type' => 'INT32', 'default' => 0),
			'unac_amount' => array('type' => 'INT32', 'default' => 0),
			'ce_amount' => array('type' => 'INT32', 'default' => 0)
		),
		'primary_key' => 'id'
	),

	/* user_groups */
	'user_groups' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'desc' => array('type' => 'TEXT'), // description
		),
		'primary_key' => 'id'
	),

	/* map_user_group */
	'map_user_group' => array(
		'cols' => array(
			'uid' => array('type' => 'INT32'),
			'gid' => array('type' => 'INT32')
		),
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('uid')),
			array(
				'type' => 'UNIQUE',
				'cols' => array('gid'))
		)
	),

	/* user_teams */
	'user_teams' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'title' => array('type' => 'TEXT'),
			'desc' => array('type' => 'TEXT')
		),
		'primary_key' => 'id'
	),

	/* user_avatars */
	'user_avatars' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'file' => array('type' => 'TEXT') // in the /contents/uploads directory
		),
		'primary_key' => 'id'
	),

	/* problems */
	'problems' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'title' => array('type' => 'TEXT'),
			'code' => array('type' => 'TEXT200'), // like the code on SPOJ
			'slug' => array('type' => 'TEXT200'), // url friendly title.
			'decription' => array('type' => 'TEXT'),
			'grp_deny' => array('type' => 'TEXT'),  // serialized array of gid
			'grp_allow' => array('type' => 'TEXT'),

			'ac_amount' => array('type' => 'INT32', 'default' => 0),
			'unac_amount' => array('type' => 'INT32', 'default' => 0),
			'ce_amount' => array('type' => 'INT32', 'default' => 0),
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('code')
			),
			array(
				'type' => 'UNIQUE',
				'cols' => array('slug')
			)
		),
		'index_len' => array(
			'code' => PROB_CODE_LEN_MAX,
			'slug' => PROB_SLUG_LEN_MAX
		)
	),

	/* prob_groups */
	'prob_groups' => array( // classification of problems
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'title' => array('type' => 'TEXT'),
			'desc' => array('type' => 'TEXT')
		),
		'primary_key' => 'id'
	),


	/* map_prob_grp */
	'map_prob_grp' => array( // map of problems and problem groups
		'cols' => array(
			'pid' => array('type' => 'INT32'),
			'gid' => array('type' => 'INT32')
		),
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('pid')),
			array(
				'type' => 'UNIQUE',
				'cols' => array('gid'))
		)
	),

	/* plang */ 
	'plang' => array( // programming language
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'name' => array('type' => 'TEXT')
		),
		'primary_key' => 'id'
	),

	/* wlang */
	'wlang' => array( // website language
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'name' => array('type' => 'TEXT')
		),
		'primary_key' => 'id'
	),

	/* records */
	'records' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'uid' => array('type' => 'INT32'), // user id
			'pid' => array('type' => 'INT32'), // problem id
			'sid' => array('type' => 'INT32'), // source id
			'jid' => array('type' => 'INT32'), // judge id
			'view_gid' => array('type' => 'TEXT'), // serialized array group id
			'src_len' => array('type' => 'INT32'), // source length in bytes
			'status' => array('type' => 'INT32'), // see includes/record.inc.php
			'stime' => array('type' => 'INT64'), // submission time
			'jtime' => array('type' => 'INT64'), // time when it is judged
			'ip' => array('type' => 'TEXT'), // from which ip it is submitted
			'score' => array('type' => 'INT32'),
			'fullscore' => array('type' => 'INT32'),
			'time' => array('type' => 'INT32'), // microsecond
			'mem' => array('type' => 'INT32'), // maximal memory, kb
			'detail' => array('type' => 'TEXT') // serialized array of case details. see includes/record.inc.php
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'cols' => array('uid')),
			array(
				'cols' => array('pid'))
		)
	),

	/* judges */
	'judges' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'name' => array('type' => 'TEXT'),
			'status' => array('type' => 'INT32'),  // see /includes/const.inc.php
			'lang_sup' => array('type' => 'TEXT'), // serialized array of id of supported languages
			'detail' => array('type' => 'TEXT') // serialized array of query_ans from orzoj-server
		),
		'primary_key' => 'id'
	),

	/* announcements */
	'announcements' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'content' => array('type' => 'TEXT')
		),
		'primary_key' => 'id'
	),


	/* options */
	'options' => array(
		'cols' => array(
			'key' => array('type' => 'TEXT200'),
			'value' => array('type' => 'TEXT'),
			'auto_load' => array('type' => 'INT32', 'default' => 0)
		),
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('key')
			)
		),
		'index_len' => array(
			'key' => OPTION_KEY_LEN_MAX
		)
	)
);

