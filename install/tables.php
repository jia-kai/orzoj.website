<?php
/* 
 * $File: tables.php
 * $Date: Mon Sep 27 02:17:58 2010 +0800
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

define('USER_GROUP_NORMAL_USER', 0);
define('USER_GROUP_ADMINISTRATOR', 1);

$tables = array(
	/* jobs */
	'jobs'=> array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'time' => array('type' => 'INT64'),
			'func' => array('type' => 'TEXT'), // function name
			'args' => array('type' => 'TEXT') // serialized array
		)
	),

	/* user_groups */

	/* groups */

	/* users */
	'users' => array(
		'cols' => array(
			'id' => array('type' => 'INT32', 'auto_assign' => TRUE),
			'username' => array('type' => 'TEXT'),
			'password' => array('type' => 'TEXT'),	// u_make_pass()
			'realname' => array('type' => 'TEXT'),
			'email' => array('type' => 'TEXT'),
			'register_time' => array('type' => 'INT64'),
			'register_ip' => array('type' => 'TEXT'),
			'group_id' => array('type' => 'INT32'), // user chosen
			'user_group_id' => array('type' => 'INT32' 'default' => USER_GROUP_NORMAL_USER), // used to manage privileges
			'language' => array('type' => 'INT32'), // language usually use
			'last_login_time' => array('type' => 'INT64'),
			'submit_amount' => array('type' => 'INT32', 'default' => 0),
			'ac_amount' => array('type' => 'INT32', 'default' => 0),
			'wa_amount' => array('type' => 'INT32', 'default' => 0),
			'ce_amount' => array('type' => 'INT32', 'default' => 0),
			'mle_amount' => array('type' => 'INT32' 'default' => 0),
			'tle_amount' => array('type' => 'INT32' 'default' => 0),
			're_amount' => array('type' => 'INT32' 'default' => 0),

			// FIXME: or portrait holds a new table?
			'portrait_type' => array('type' => 'INT32'), // 0 if use default portrait, 1 if customized
			'portrait_addr' => array('type' => 'TEXT'), // FIXME: or id ?


			'is_locked' => array('type' => 'INT32', 'default' => 0)
		),
		'primary key' => 'id'
	)

	/* problems */
	'problems' => array(
		'cols' => array(
			'id' => array('type' => 'INT32'),
			'title' => array('type' => 'TEXT'),
			'decription' => array('type' => 'TEXT'), // FIXME: or includes things like input_fmt, output_fmt?
			'input_fmt' => array('type' => 'TEXT'), 
			'output_fmt' => array('type' => 'TEXT'),
			'sample_input' => array('type' => 'TEXT'),
			'sample_output' => array('type' => 'TEXT'),
			'source' => array('type' => 'TEXT'), // where does the problem come from
			'hint' => array('type' => 'TEXT'),
			'prob_group_id' => array('type' => 'INT32'),
			'contest_id' => array('type' => 'INT32'), // FIXME: or somewhat?

			'ac_amount' => array('type' => 'INT32', 'default' => 0),
			'wa_amount' => array('type' => 'INT32', 'default' => 0),
			'ce_amount' => array('type' => 'INT32', 'default' => 0),
			'mle_amount' => array('type' => 'INT32' 'default' => 0),
			'tle_amount' => array('type' => 'INT32' 'default' => 0),
			're_amount' => array('type' => 'INT32' 'default' => 0),

			// FIXME: customized verifier holds a new table?
		/*
		'is_customized_verify' => array('type' => 'INT32'),
		'customized_verifier_id' => array('type' => 'INT32')
		 */

			'config' => array('type' => 'TEXT'), 
		/* serialized array includes:
			use_file, //FIXME: or managed by contest rule?
			intput_file,
			output_file,
			case_amount,
			case => array(
				time_limit, // in microsecond
				memory_limit // in kb
			)
		 */
		),
		'primary key' => 'id'
	),
	/* problem_groups */

	/* languages */

	/* records */

	/* judges */

	/* announcement */

/* problem_data */  //XXX

// TODO
/* options */ // ????or config???
);
