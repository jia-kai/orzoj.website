<?php
/* 
 * $File: tables.php
 * $Date: Wed Sep 29 11:46:18 2010 +0800
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


	// only users in  ADMIN group can add or remove groups
	// group administrators can add or remove non-administrator members

	/* users */
	'users' => array( // related file: /includes/user.php
		'cols' => array(
			'username' => array('type' => 'TEXT200'),
			'realname' => array('type' => 'TEXT'),
			'aid' => array('type' => 'INT32'), // avatar id
			'email' => array('type' => 'TEXT'),
			'self_desc' => array('type' => 'TEXT'), // self description
			'tid' => array('type' => 'INT32'), // team id
			'plang' => array('type' => 'INT32'), // preferred programming language 
			'wlang' => array('type' => 'INT32'), // preferred website language
			// callers of user.php:user_add() should pass an array as $value containing
			// the above coloumns and their corresponding values
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'passwd' => array('type' => 'TEXT200'),
			'salt' => array('type' => 'TEXT200', 'default' => ''),
			'view_gid' => array('type' => 'TEXT'),
				// serialized array of group id who can view the user's source
			'reg_time' => array('type' => 'INT64'), // register time
			'reg_ip' => array('type' => 'TEXT'), // register ip
			'last_login_time' => array('type' => 'INT64', 'default' => 0),
			'last_login_ip' => array('type' => 'TEXT', 'default' => ''),

			'cnt_submit' => array('type' => 'INT32', 'default' => 0), // number of submissions
			'cnt_ac' => array('type' => 'INT32', 'default' => 0), // number of accepted submissions
			'cnt_unac' => array('type' => 'INT32', 'default' => 0), // number of unaccepted submissions
			'cnt_ce' => array('type' => 'INT32', 'default' => 0) // number of compiling-error submissions
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('username')
			)
		),
		'index_len' => array(
			'username' => USERNAME_LEN_MAX)
	),

	// if a user belongs to a group, it belongs to all this group's ancestor groups
	//
	// BUT a user belongs to the administrator group iff it belongs to the group directly

	/* user_groups */
	'user_groups' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'pgid' => array('type' => 'INT32'), // parent group id, or 0 if none
			'desc' => array('type' => 'TEXT'), // description
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'cols' => array('pgid')
			)
		),
	),

	/* map_user_group */
	'map_user_group' => array(
		'cols' => array(
			'uid' => array('type' => 'INT32'),
			'gid' => array('type' => 'INT32'),
			'pending' => array('type' => 'INT32'), // whether the user is pending to join the group
			'admin' => array('type' => 'INT32'), // whether the user is an administrator of the group
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

	/* messages */
	'messages' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'time' => array('type' => 'INT64'),
			'uid_snd' => array('type' => 'INT32'), // sender's user id
			'uid_rcv' => array('type' => 'INT32'), // receiver's user id
			'content' => array('type' => 'TEXT'),
			'read' => array('type' => 'INT32'), // whether this message has been read
		),
		'primary_key' => 'id',
		'index' => array(
			array('cols' => array('uid_snd')),
			array('cols' => array('uid_rcv', 'read'))
		)
	),

	/* problems */
	'problems' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'title' => array('type' => 'TEXT'),
			'code' => array('type' => 'TEXT200'), // like the code on SPOJ
			'slug' => array('type' => 'TEXT200'), // url friendly title.
			'decription' => array('type' => 'TEXT'),
			'grp_deny' => array('type' => 'TEXT'),  // serialized array of id groups disallowed to view this problem 
			'grp_allow' => array('type' => 'TEXT'),

			'cnt_submit' => array('type' => 'INT32', 'default' => 0),
			'cnt_ac' => array('type' => 'INT32', 'default' => 0),
			'cnt_unac' => array('type' => 'INT32', 'default' => 0),
			'cnt_ce' => array('type' => 'INT32', 'default' => 0),
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

	// let S be the set of groups of a certain user belonging to, D and A be the sets of
	// denied gid or allowed gid of a certain problem, E is the empty set, * is the intersection of two sets,
	// then the user can access the problem iff:
	//     S * D == E and S * A != E

	/* prob_groups */
	'prob_groups' => array( // classification of problems
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'pgid' => array('type' => 'INT32'), // parent group id
			'title' => array('type' => 'TEXT'),
			'desc' => array('type' => 'TEXT')
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'cols' => array('pgid')
			)
		),
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
			'name' => array('type' => 'TEXT') // language name, also the file name in /contents/lang/
		),
		'primary_key' => 'id'
	),

	/* records */
	'records' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'uid' => array('type' => 'INT32'), // user id
			'pid' => array('type' => 'INT32'), // problem id
			'jid' => array('type' => 'INT32'), // judge id
			'lid' => array('type' => 'INT32'), // language id
			'src_len' => array('type' => 'INT32'), // source length in bytes
			'status' => array('type' => 'INT32'), // see includes/record.inc.php
			'stime' => array('type' => 'INT64'), // submission time
			'jtime' => array('type' => 'INT64'), // time when it is judged
			'ip' => array('type' => 'TEXT'), // from which ip it is submitted
			'score' => array('type' => 'INT32'),
			'full_score' => array('type' => 'INT32'),
			'time' => array('type' => 'INT32'), // microsecond
			'mem' => array('type' => 'INT32'), // maximal memory, kb
			'detail' => array('type' => 'TEXT') // serialized array of Case_result. see includes/exe_status.inc.php
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'cols' => array('uid')),
			array(
				'cols' => array('pid')),
			array(
				'cols' => array('time')),
			array(
				'cols' => array('mem')),
		)
	),

	'sources' => array(
		'cols' => array(
			'rid' => array('type' => 'INT32'), // record id
			'src' => array('type' => 'TEXT'),
			'time' => array('type' => 'INT64'),
			'sent' => array('type' => 'INT32', 'default' => 0) // whether it has been sent to orzoj-server
		),
		'primary_key' => 'rid',
		'index' => array(
			array('cols' => array('time', 'sent'))
		)
	),

	/* judges */
	'judges' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'name' => array('type' => 'TEXT200'),
			'status' => array('type' => 'INT32'),  // see /includes/const.inc.php
			'lang_sup' => array('type' => 'TEXT'), // serialized array of id of supported languages
			'detail' => array('type' => 'TEXT') // serialized array of query_ans from orzoj-server
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('name')
			)
		),
		'index_len' => array(
			'name' => JUDGE_NAME_LEN_MAX
		),
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
	/* 
	 * tetative rows:
	 * judge_info_list : serialized array of information about judge want to get from sever
	 */
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
	),

	/* msg_req */
	'msg_req' => array( // requests to orzoj-server in msg.php
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'data' => array('type' => 'TEXT'), // serialized array of request data
		),
		'primary_key' => 'id'
	)
);
