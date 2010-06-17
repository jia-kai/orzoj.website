<?php
// $tablepre.'options'
$optiontable = array(
	'cols' => array(
		'optionname' => array('type' => 'TEXT'),
		'optionvalue' => array('type' => 'TEXT'),
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
		'usergroup' => array('type' => 'INT32','default' => 0),
		'otherinfo' => array('type' => 'TEXT')
	),
	'primary key' => 'id');

// $tablepre.'problems'
$problemtable =  array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'title' => array('type' => 'TEXT'),
		'slug' => array('type' => 'TEXT'),
		'description' => array('type' => 'TEXT'),
		'inputformat' => array('type' => 'TEXT'),
		'outputformat' => array('type' => 'TEXT'),
		'sampleinput' => array('type' => 'TEXT'),
		'sampleoutput' => array('type' => 'TEXT'),
		'hint' => array('type' => 'TEXT'),
		'source' => array('type' => 'TEXT'),
		'submitamount' => array('type' => 'INT32','default' => 0),
		'acamount' => array('type' => 'INT32','default' => 0),
		'acrate' => array('type' => 'INT32','default' => 0),
		'difficulty' => array('type' => 'INT32','default' => 0),
		'dataid' => array('type' => 'INT32','default' => 0),
		'usefile' => array('type' => 'INT32'),
		'inputfile' => array('type' => 'TEXT'),
		'outputfile' => array('type' => 'TEXT'),
		'timelimit' => array('type' => 'TEXT'),
		'memorylimit' => array('type' => 'TEXT'),
		'publishtime' => array('type' => 'INT32'),
		'otherinfo' => array('type' => 'TEXT'),
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

// $tablepre.'problemtypes'
$problemtypetable = array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'typename' => array('type' => 'TEXT')
	),
	'primary key' => 'id'
);


// $tablepre.'contests'
$contesttable = array(
	'cols' => array(
		'id' => array('type' => 'INT32','auto_assign' => true),
		'contestname' => array('type' => 'TEXT'),
		'rule' => array('type' => 'INT32'),
		'starttime' => array('type' => 'INT32'),
		'endtime' => array('type' => 'INT32'),
		'description' => array('type' => 'TEXT'),
		'judgeserver' => array('type' => 'TEXT')
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

$problem_pbtype_relationshiptable = array(
	'cols' => array(
		'problemid' => array('type' => 'INT32'),
		'problemtypeid' => array('type' => 'INT32')
	)
);

$problem_pbgroup_relationshiptable = array(
	'cols' => array(
		'problemid' => array('type' => 'INT32'),
		'problemgroupid' => array('type' => 'INT32')
	)
);

$ruletable = array(
	'cols' => array(
		'id' => array('type' => 'INT32'),
		'rulename' => array('type' => 'TEXT'),
		'ruledetail' => array('type' => 'TEXT')
	),
	'primary key' => 'id'
	);


