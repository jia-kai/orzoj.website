<?php
/* 
 * $File: tables.php
 * $Date: Fri Jan 06 22:14:12 2012 +0800
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
			'realname' => array('type' => 'TEXT200'), 
			'nickname' => array('type' => 'TEXT200'),
			'passwd' => array('type' => 'TEXT200'),
			'salt' => array('type' => 'TEXT200', 'default' => ''),
			'aid' => array('type' => 'INT32'), // avatar id
			'email' => array('type' => 'TEXT'),
			'self_desc' => array('type' => 'TEXT'), // self description
			'plang' => array('type' => 'INT32'), // preferred programming language 
			'wlang' => array('type' => 'INT32'), // preferred website language
			'view_gid' => array('type' => 'TEXT'),
				// json encoded array of group id who can view the user's source
			// 'theme_id' => array('type' => 'INT32', 'default' => 0), // current website theme
			'reg_time' => array('type' => 'INT64'), // register time
			'reg_ip' => array('type' => 'TEXT'), // register ip
			'last_login_time' => array('type' => 'INT64', 'default' => 0),
			'last_login_ip' => array('type' => 'TEXT', 'default' => ''),

			'cnt_ac' => array('type' => 'INT32', 'default' => 0),
			// number of accepted submissions
			'cnt_unac' => array('type' => 'INT32', 'default' => 0),
			// number of unaccepted submissions except those with compilation error
			'cnt_ce' => array('type' => 'INT32', 'default' => 0),
			// number of compiling-error submissions

			'cnt_ac_prob' => array('type' => 'INT32', 'default' => 0),
			// number of problems where the user submitted an accepted solution
			'cnt_ac_prob_blink' => array('type' => 'INT32', 'default' => 0),
			// number of problems where the user's first submission is accepted
			'cnt_ac_submission_sum' => array('type' => 'INT32', 'default' => 0),
			// total number of submissions of which the corresponding problem does not
			// have an accepted solution by this user before
			'cnt_submitted_prob' => array('type' => 'INT32', 'default' => 0),
			// number of problems where the user has ever submitted
			'ac_ratio' => array('type' => 'INT32', 'default' => 0)
			// accepted ratio: cnt_ac_prob / cnt_ac_submission_sum
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('username')
			),
		),
		'index_len' => array(
			'username' => USERNAME_LEN_MAX)
	),

	// if a user belongs to a group, it belongs to all this group's ancestor groups

	/* user_grps */
	'user_grps' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'pgid' => array('type' => 'INT32'), // parent group id, or 0 if none
			'name' => array('type' => 'TEXT200'), // group name
			'desc' => array('type' => 'TEXT') // description
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'cols' => array('pgid')
			),
			array(
				'type' => 'UNIQUE',
				'cols' => array('name')
			)
		),
		'index_len' => array(
			'name' => USER_GRP_NAME_LEN_MAX
		)
	),

	/* map_user_grp */
	'map_user_grp' => array(
		'cols' => array(
			'uid' => array('type' => 'INT32'),
			'gid' => array('type' => 'INT32'),
			'pending' => array('type' => 'INT32'), // whether the user is pending to join the group
			'admin' => array('type' => 'INT32'), // whether the user is an administrator of the group
		),
		'index' => array(
			array('cols' => array('uid', 'gid')),
			array('cols' => array('gid'))
		)
	),

	/* cache_ugrp_child */
	'cache_ugrp_child' => array( // cache of all children of each user group (each group itself included)
		'cols' => array(
			'gid' => array('type' => 'INT32'),
			'chid' => array('type' => 'INT32') // id of one of the children of the user group
		),
		'index' => array(
			array('cols' => array('gid'))
		)
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

			'time' => array('type' => 'INT64'), // last modification time

			'cnt_ac' => array('type' => 'INT32', 'default' => 0),
			// number of accepted submissions

			'cnt_unac' => array('type' => 'INT32', 'default' => 0),
			// number of unaccepted submissions except those with compilation error

			'cnt_ce' => array('type' => 'INT32', 'default' => 0),
			// number of submissions with compilation error 

			'cnt_submit_user' => array('type' => 'INT32', 'default' => 0),
			// number of users who have submitted this problem

			'cnt_ac_user' => array('type' => 'INT32', 'default' => 0),
			// number of users who have submitted an accepted solution

			'cnt_ac_submission_sum' => array('type' => 'INT32', 'default' => 0),
			// total number of submissions of which the corresponding user does not
			// have an accepted solution before

			'difficulty' => array('type' => 'INT32', 'default' => DB_REAL_PRECISION),
			// (cnt_ac_submission_sum - cnt_ac_user) / cnt_ac_submission_sum

			'deleted' => array('type' => 'INT32', 'default' => 0)
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('code')
			),
			array(
				'cols' => array('deleted')
			)
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
			),
			array(
				'type' => 'UNIQUE',
				'cols' => array('name')
			)
		),
		'index_len' => array(
			'name' => PROB_GRP_NAME_LEN_MAX
		)
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
	'cache_pgrp_child' => array( // cache of all children of each problem group (each group itself included)
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
			'opt' => array('type' => 'TEXT') // contest type specified options
		),
		'primary_key' => 'id',
		'index' => array(
			array('cols' => array('time_start', 'time_end')),
			array('cols' => array('time_end'))
		)
	),
	// every contest type should have a table, where
	// result for each user is stored
	// required column: uid

	/* contests_freesub */
	// sumit at any time, no penalty
	// use the last submission to compute final score
	'contests_freesub' => array(
		'cols' => array(
			'cid' => array('type' => 'INT32'), // contest id
			'uid' => array('type' => 'INT32'), // user id
			'prob_result' => array('type' => 'TEXT'),
			// json encoded array(<problem id> =>
			//		array(<execution status>, <score>, <execution time (microsecond)>, <record id>))
			'total_score' => array('type' => 'INT32'),
			'total_time' => array('type' => 'INT32') // microsecond
		),
		'index' => array(
			array('cols' => array('cid', 'total_score')),
			array('cols' => array('cid', 'uid'))
		)
	),

	/* contests_oi */
	'contests_oi' => array( // OI contests
		'cols' => array(
			'cid' => array('type' => 'INT32'), // contest id
			'uid' => array('type' => 'INT32'), // user id
			'prob_result' => array('type' => 'TEXT'),
			// json encoded array(<problem id> =>
			//		array(<execution status>, <score>, <execution time (microsecond)>, <record id>))
			'total_score' => array('type' => 'INT32'),
			// before the contest ends, scheduled job id is stored in column 'total_score'
			// before judge process finishes, remaining number of unjudged submissions is stored in
			//		column 'total_score' with 'uid' = 0
			'total_time' => array('type' => 'INT32'), // microsecond
		),
		'index' => array(
			array('cols' => array('cid', 'total_score')),
			array('cols' => array('cid', 'uid'))
		)
	),

	/* contests_acm */
	'contests_acm' => array( // ACM/ICPC contests
		/*
		 * options in 'contests' table:
		 *		json encoded array(
		 *			'suspend_time' => <suspend time(minutes)>,
		 *			'penalty_time' => <penalty time(minutes)>,
		 *			'force_stdio' => <whether to force using stdio>,
		 *			'prob_sts' => array(<problem id> => array(<total runs, accepted runs>)))
		 */
		'cols' => array(
			'cid' => array('type' => 'INT32'), // contest id
			'uid' => array('type' => 'INT32'), // user id
			'ac_cnt'  => array('type' => 'INT32'),
			'penalty' => array('type' => 'INT32'), // in seconds
			'prob_result' => array('type' => 'TEXT')
			// json encoded array(<problem id> => array(<execution status>, <number of rejected runs>))
		),
		'index' => array(
			array('cols' => array('cid', 'ac_cnt', 'penalty')),
			array('cols' => array('cid', 'uid'))
		)
	),

	/* map_prob_ct */
	'map_prob_ct' => array( // map of problems and contests
		'cols' => array(
			'pid' => array('type' => 'INT32'),
			'cid' => array('type' => 'INT32'),
			'order' => array('type' => 'INT32') // used to specify the order of problems in a contest
		),
		'index' => array(
			array('cols' => array('pid')),
			array('cols' => array('cid'))
		)
	),

	/* plang */ 
	'plang' => array( // programming language
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'name' => array('type' => 'TEXT200'), // display name and name used on orzoj-server
			'type' => array('type' => 'TEXT200')
			// the type of this language, also the file extention when downloading a source file of this language
			// standard types:
			//	cpp, c, pas, java
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
			'cid' => array('type' => 'INT32'), // contest id, or 0 if no contest related
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
			'detail' => array('type' => 'BINARY', 'default' => '')
			// encoded array of Case_result. see includes/exe_status.php
			// or error info if judge process not started
			// values in this field are not HTML encoded
			//
			// if status == RECORD_STATUS_WAITING_TO_BE_FETCHED,
			//		serialized array(<input file name>, <output file name>) is stored in 'detail'
		),
		'primary_key' => 'id',
		'index' => array(
			array('cols' => array('uid', 'pid')),
			array('cols' => array('pid', 'status')),
			array('cols' => array('score', 'time')),
			array('cols' => array('status', 'id')),
			array('cols' => array('cid', 'status'))
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
			array('cols' => array('sent', 'time'))
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
	/*
	'orz_req' => array( // requests to orzoj-server in orz.php
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'data' => array('type' => 'TEXT'),
			// serialized array of request data, see orzoj-server/web.py:fetch_task()
			// if type=src, src is not set and should be found in 'sources' table
		),
		'primary_key' => 'id'
	),
	XXX: orz_req is not used currently, because data transfer from server to website has not been implemented
	 */

	/* orz_thread_reqid */
	'orz_thread_reqid' => array( // used for storing thread request id in orz.php
		'cols' => array(
			'tid' => array('type' => 'INT32'), // thread id
			'reqid' => array('type' => 'INT32') // request id
		),
		'primary_key' => 'tid'
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

	/* post_topics*/
	'post_topics' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'time' => array('type' => 'INT64'), // publishing time
			'uid' => array('type' => 'INT32'), // user id
			'prob_id' => array('type' => 'INT32', 'default' => 0), // related problem id, 0 means no problem is related
			'reply_amount' => array('type' => 'INT32', 'default' => 0), // number of reply
			'viewed_amount' => array('type' => 'INT32', 'default' => 0), // number of viewed users
			'priority' => array('type' => 'INT32', 'default' => 0), // every post topic should have a priority, the bigger, the prior
			'is_top' => array('type' => 'INT32', 'default' => 0), //whether the post should be on the top, ordering by priority and time if more than one.
			'is_locked' => array('type' => 'INT32', 'default' => 0), // whether the post is locked
			'is_elaborate' => array('type' => 'INT32', 'default' => 0), // where the post is a elaborate 
			//'view_gid' => array('type' => 'TEXT'),
			'type' => array('type' => 'INT32', 'default' => 1),
			/* one of the attribs below should setted
			 * 'type' => int: one of {1 : 'normal', 2 = 'question', 3 = 'solution', 4 = 'vote'} and 0 is reserved for 'all'
			 * @see includes/post.php
			 */
			'last_reply_time' => array('type' => 'INT64', 'default' => 0),
			'last_reply_user' => array('type' => 'INT32', 'default' => 0), // user id
			'subject' => array('type' => 'TEXT'),
			'content' => array('type' => 'TEXT'),
			'floor_amount' => array('type' => 'INT32', 'default' => 1)
		),
		'primary_key' => 'id',
		'index' => array(
			array('cols' => array('uid')),
			array('cols' => array('type', 'prob_id'))
		),
		'index_len' => array(
			'subject' => POST_SUBJECT_LEN_MAX,
			'content' => POST_CONTENT_LEN_MAX
		)
	),

	/* posts */
	'posts' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_increment' => TRUE),
			'time' => array('type' => 'INT64'), // publishing time
			'uid' => array('type' => 'INT32'), // user id
			'tid' => array('type' => 'INT32'), // post topic id
			'floor' => array('type' => 'INT32'),
			'content' => array('type' => 'TEXT'),
			'last_modify_time' => array('type' => 'INT64', 'default' => '0'), // 0 means no body has modified this post yet
			'last_modify_user' => array('type' => 'INT32', 'default' => '0') // user id, 0 no user has modified this post
		),
		'primary_key' => 'id',
		'index' => array(
			array('cols' => array('time', 'tid')),
		),
		'index_len' => array(
			'content' => POST_CONTENT_LEN_MAX
		)
	),

	/* sts_prob_user */
	'sts_prob_user' => array( // the user status of a specific problem
		'cols' => array(
			'uid' => array('type' => 'INT32'),
			'pid' => array('type' => 'INT32'),
			'status' => array('type' => 'INT32') // see consts starting with STS_
		),
		'index' => array(
			array(
				'cols' => array('uid', 'pid'),
				'cols' => array('uid', 'status')
			)
		)
	),
	/* custom pages */
	'pages' => array(
		'cols' => array(
			'id' => array('type' => 'INT32','auto_increment' => TRUE),
			'title' => array('type' => 'TEXT'),
			'slug' => array('type' => 'TEXT200'),
			'content' => array('type' => 'TEXT'),
			'time' => array('type' => 'INT64')
		),
		'primary_key' => 'id',
		'index' => array(
			array(
				'type' => 'UNIQUE',
				'cols' => array('slug')
			)
		),
		'index_len' => array(
			'slug' => PAGES_SLUG_LEN_MAX
		)
	)
);
