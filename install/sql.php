<?php
// $tablepre.'options'
$optiontable = array(
	'cols' => array(
		'option_name' => array('type' => 'TEXT'),
		'option_value' => array('type' => 'TEXT'),
		'autoload' => array('type' => 'INT32','default' => 0)
	)
);


// $tablepre.'users'
$usertable = array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'username' => array('type' => 'TEXT'),
		'password' => array('type' => 'TEXT'),
		'realname' => array('type' => 'TEXT'),
		'email' => array('type' => 'TEXT'),
		'question' => array('type' => 'TEXT'),
		'answer' => array('type' => 'TEXT'),
		'regtime' => array('type' => 'INT64'),
		'regip' => array('type' => 'TEXT'),
		'lastlogintime' => array('type' => 'INT64'),
		'lastloginip' => array('type' => 'TEXT'),
		'submitamount' => array('type' => 'INT32','default' => 0),
		'acamount' => array('type' => 'INT32','default' => 0),
		'acrate' => array('type' => 'INT32','default' => 0),
		'programminglanguage' => array('type' => 'INT32','default' => 0),
		'checksum' => array('type' => 'TEXT'),
	),
	'primary key' => 'id');

// $tablepre.'problems'
$problemtable =  array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'title' => array('type' => 'TEXT'),
		'slug' => array('type' => 'TEXT'),
		'code' => array('type' => 'TEXT200'),
		'description' => array('type' => 'TEXT'),
		'cached_html' => array('type' => 'TEXT'),
		'submitamount' => array('type' => 'INT32','default' => 0),
		'acamount' => array('type' => 'INT32','default' => 0),
		'acrate' => array('type' => 'INT32','default' => 0),
		'difficulty' => array('type' => 'INT32','default' => 0),
		'usefile' => array('type' => 'INT32'),
		'inputfile' => array('type' => 'TEXT'),
		'outputfile' => array('type' => 'TEXT'),
		'publishtime' => array('type' => 'INT64'),
	),
	'primary key' => 'id'
);


// $tablepre.'problemgroups'
$problemgrouptable = array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'groupname' => array('type' => 'TEXT'),
		'parent' => array('type' => 'INT32','default' => 0)
	),
	'primary key' => 'id',
);


// $tablepre.'contests'
$contesttable = array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'contestname' => array('type' => 'TEXT'),
		'rule' => array('type' => 'TEXT'),
		'starttime' => array('type' => 'INT64'),
		'endtime' => array('type' => 'INT64'),
		'description' => array('type' => 'TEXT')
	),
	'primary key' => 'id'
);

// $tablepre.'problem_contest_relationships'
$problemcontestbindtable = array(
	'cols' => array(
		'problemid' => array('type' => 'INT32'),
		'contestid' => array('type' => 'INT32')
	)
);

$problem_pbgroup_relationshiptable = array(
	'cols' => array(
		'problemid' => array('type' => 'INT32'),
		'problemgroupid' => array('type' => 'INT32')
	)
);


$judge_table = array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'name' => array('type' => 'TEXT'),
		'status' => array('type' => 'INT32','default' => 0),
		'language_supported' => array('type' => 'TEXT'),
		'variables' => array('type' => 'TEXT')
		),
		'primary key' =>  'id'
	);


$record_table = array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'userid' => array('type' => 'INT32'),
		'problemid' => array('type' => 'INT32'),
		'status' => array('type' => 'INT32'),
		'time' => array('type' => 'INT32'),
		'jtime' => array('type' => 'INT32'), //评测时间
		'ip' => array('type' => 'TEXT'),
		'score' =>  array('type' => 'INT32'),
		'fullscore' => array('type' => 'INT32'),
		'detail' => array('type' => 'TEXT'),
		'judgeid' => array('type' => 'INT32')
		)
	);


