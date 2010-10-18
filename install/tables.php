<?php
/* 
 * $File: tables.php
 * $Date: Mon Oct 18 16:59:34 2010 +0800
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

require_once realpath('..') . '/includes/const.php';
require_once realpath('..') . '/includes/record.php';

$tables = array(
	/* scheds */
	'scheds'=> array( //  scheduled tasks
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
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
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'username' => array('type' => 'TEXT200'),
			'realname' => array('type' => 'TEXT200'), // only visiable by administrator
			'nickname' => array('type' => 'TEXT200'), // display name
			'passwd' => array('type' => 'TEXT200'),
			'salt' => array('type' => 'TEXT200', 'default' => ''),
			'aid' => array('type' => 'INT32'), // avatar id
			'email' => array('type' => 'TEXT'),
			'self_desc' => array('type' => 'TEXT'), // self description
			'plang' => array('type' => 'INT32'), // preferred programming language 
			'wlang' => array('type' => 'INT32'), // preferred website language
			'view_gid' => array('type' => 'TEXT'),
				// serialized array of group id who can view the user's source
			// 'theme_id' => array('type' => 'INT32', 'default' => 0), // current website theme
			'tid' => array('type' => 'INT32', 'default' => 0), // team id
			'reg_time' => array('type' => 'INT64'), // register time
			'reg_ip' => array('type' => 'TEXT'), // register ip
			'last_login_time' => array('type' => 'INT64', 'default' => 0),
			'last_login_ip' => array('type' => 'TEXT', 'default' => ''),

			'cnt_submit' => array('type' => 'INT32', 'default' => 0), // number of submissions
			'cnt_ac' => array('type' => 'INT32', 'default' => 0), // number of accepted submissions
			'cnt_unac' => array('type' => 'INT32', 'default' => 0), // number of unaccepted submissions
			'cnt_ce' => array('type' => 'INT32', 'default' => 0), // number of compiling-error submissions
			'ac_ratio' => array('type' => 'INT32', 'default' => 0) // accepte ratio: floor((cnt_ac / cnt_submit) * 10000, which means two fraction numbers will be hold.
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('username')
			),
			array('cols' => array('tid'))
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
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
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
	'user_teams' => array( // managed by administrators
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'name' => array('type' => 'TEXT200'),
			'desc' => array('type' => 'TEXT'),
			'img' => array('type' => 'TEXT')  // image file path related to /contents/uploads/team_image
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('name'))
		),
		'index_len' => array('name' => TEAM_NAME_LEN_MAX)
	),

	/* user_avatars */
	'user_avatars' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'file' => array('type' => 'TEXT') // related to /contents/uploads/user_avatar
		),
		'primary_key' => 'id'
	),

	/* problems */
	'problems' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'title' => array('type' => 'TEXT'),
			'code' => array('type' => 'TEXT200'), // like the code on SPOJ
			'desc' => array('type' => 'TEXT'),
				// serialized array of (<field name> => <value>)
				// e.g. array('time'=>'1s', 'memory'=>'256MB', 'desc'=>'...')
				// see simple-doc.txt
			'perm' => array('type' => 'TEXT'), // permission
			// serialized array (order, no_match, grp_allow, grp_deny),
			// order = 0: allow, deny
			// order = 1: deny, allow
			// grp_deny, grp_allow: array of ids of corresponding groups 
			// 
			// |---------------------------------------------------------------------
			// |     Match			| allow, deny result	| deny, allow result	|
			// |--------------------+-----------------------+-----------------------|
			// | Match allow only	| request allowed		| request allowed		|
			// |--------------------+-----------------------+-----------------------|
			// | Match deny only	| request denied		| request denied		|
			// |--------------------+-----------------------+-----------------------|
			// | No match			| request denied iff no_match = 0				|
			// |--------------------+-----------------------+-----------------------|
			// | Match both			| final match: denied	| final match: allowed	|
			// |--------------------------------------------------------------------|
			'io' => array('type' => 'TEXT'), // serialized array of input/output file name, or empty string if using stdio

			'time' => array('type' => 'INT64'), // when this problem is added

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
		),
		'index_len' => array(
			'code' => PROB_CODE_LEN_MAX
		)
	),

	/* prob_grps */ // problem groups
	'prob_grps' => array( // classification of problems
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'pgid' => array('type' => 'INT32', 'default' => 0), // parent group id, or 0 if no parent group
			'name' => array('type' => 'TEXT'),
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
				'cols' => array('pid')),
			array(
				'cols' => array('gid'))
		)
	),

	/* cache_pgrp_child */
	'cache_pgrp_child' => array( // cache of all children of each problem group
		'cols' => array(
			'gid' => array('type' => 'INT32'),
			'chid' => array('type' => 'INT32') // id of one of the children of the problem group
		),
		'index' => array(
			array('cols' => array('gid'))
		)
	),

	/* contests */
	'contests' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'type' => array('type' => 'INT32'), // contest type, defined in /includes/contest/ctal.php
			'name' => array('type' => 'TEXT'), // contest name
			'desc' => array('type' => 'TEXT'), // description
			'time_start' => array('type' => 'INT64'),
			'time_end' => array('type' => 'INT64'),
			'perm' => array('type' => 'TEXT'), // see 'problems' table
			'result_cache' => array('type' => 'TEXT') // result cache, maintained by specific contest type
		),
		'primary_key' => 'id',
		'index' => array(
			array('cols' => array('time_start', 'time_end')),
			array('cols' => array('time_end'))
		)
	),

	/* map_prob_ct */
	'map_prob_ct' => array( // map of problems and contests
		'cols' => array(
			'pid' => array('type' => 'INT32'),
			'cid' => array('type' => 'INT32'),
			'time_start' => array('type' => 'INT64'), // contest start time
			'time_end' => array('type' => 'INT64') // contest end time
		),
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('pid')),
			array(
				'type' => 'UNIQUE',
				'cols' => array('cid')),
			array(
				'cols' => array('pid', 'time_start', 'time_end')),
		)
	),

	/* plang */ 
	'plang' => array( // programming language
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'name' => array('type' => 'TEXT200'), // display name and name used on orzoj-server
			'type' => array('type' => 'TEXT200')
			// the type of this language
			// standard types:
			//	cpp, c, pascal, java
		),
		'primary_key' => 'id'
	),

	/* wlang */
	'wlang' => array( // website language
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'name' => array('type' => 'TEXT200'), // language name
			'file' => array('type' => 'TEXT') // the .mo file name in /contents/lang/
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('name')
			)
		),
		'index_len' => array(
			'name' => WLANG_NAME_LEN_MAX
		),
	),

	/* records */
	'records' => array( // this table is read directly by themes
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'uid' => array('type' => 'INT32'), // user id
			'pid' => array('type' => 'INT32'), // problem id
			'jid' => array('type' => 'INT32', 'default' => 0), // judge id
			'lid' => array('type' => 'INT32'), // language id
			'src_len' => array('type' => 'INT32'), // source length in bytes
			'status' => array('type' => 'INT32', 'default'), // see includes/record.php
			'stime' => array('type' => 'INT64'), // submission time
			'jtime' => array('type' => 'INT64', 'default' => 0), // time when it is judged
			'ip' => array('type' => 'TEXT'), // from which ip it is submitted
			'score' => array('type' => 'INT32', 'default' => 0),
			'time' => array('type' => 'INT32', 'default' => 0), // microsecond
			'mem' => array('type' => 'INT32', 'default' => 0), // maximal memory, kb
			// if status == RECORD_STATUS_RUNNING,
			//		current case number (starting at 0) is stored in 'time',
			//		and total number of cases is stored in 'mem'
			'detail' => array('type' => 'TEXT', 'default' => '')
			// encoded array of Case_result. see includes/exe_status.php
			// or error info if judge process not started
			// values in this field are not HTML encoded
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'cols' => array('uid', 'pid', 'status', 'lid')),
			array(
				'cols' => array('pid', 'status')),
			array(
				'cols' => array('score', 'time'))
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
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'name' => array('type' => 'TEXT200'),
			'status' => array('type' => 'INT32'),  // see /includes/const.php
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
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
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

	/* orz_req */
	'orz_req' => array( // requests to orzoj-server in orz.php
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'data' => array('type' => 'TEXT'),
			// serialized array of request data, see orzoj-server/web.py:fetch_task()
			// if type=src, src is not set and should be found in 'sources' table
		),
		'primary_key' => 'id'
	),

	/* messages */
	'messages' => array( // user-to-user messages
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'time' => array('type' => 'INT64'),
			'uid_snd' => array('type' => 'INT32'),
			'uid_rcv' => array('type' => 'INT32'),
			'subject' => array('type' => 'TEXT'),
			'content' => array('type' => 'TEXT'),
			'is_read' => array('type' => 'INT32', 'default' => 0),
			'rm_snd' => array('type' => 'INT32', 'default' => 0), // whether the message is deleted by sender
			'rm_rcv' => array('type' => 'INT32', 'default' => 0) // whether the message is deleted by receiver
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'cols' => array('uid_snd', 'rm_snd')
			),
			array(
				'cols' => array('uid_rcv', 'rm_rcv')
			)
		)
	),

	/* posts */
	'posts' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'time' => array('type' => 'INT64'),
			'uid' => array('type' => 'INT32'), // user id
			'prob_id' => array('type' => 'INT32'), // related problem id
			'pid' => array('type' => 'INT32', 'default' => 0), // parent id, 0 means root post
			'rid' => array('type' => 'INT32'), // root post id
			'score' => array('type' => 'INT32'), // user can grade this post from 0 to 100
			'is_top' => array('type' => 'INT32', 'default' => 0), //whether the post should be one the top, ordering by time if more than one. root post only
			// TODO: commented things will be supported next version or later
			//'attrib' => array('type' => 'TEXT'),
			/* a serialize array of following attributes, should be only setted on root post
			 * 'type' => string : one of {'question', 'normal', 'solution'}
			 */
			'last_reply_time' => array('type' => 'INT64'),
			'last_reply_user' => array('type' => 'INT32'), // user id
			'subject' => array('type' => 'TEXT'),
			'content' => array('type' => 'TEXT'),
			'last_modify_time' => array('type' => 'INT32', 'default' => '-1'), // -1 means no body has modified this post yet
			'last_modify_user' => array('type' => 'INT32', 'default' => '-1') // user id, -1 see above
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'cols' => array('pid')
			)
		)
	),

	/* sts_prob_user */
	'sts_prob_user' => array( // the user status of a specific problem
		'cols' => array(
			'uid' => array('type' => 'INT32'),
			'pid' => array('type' => 'INT32'),
			'status' => array('type' => 'INT32')
		),
		'index' => array(
			array(
				'cols' => array('uid', 'pid')
			)
		)
	)

);

