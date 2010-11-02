<?php
require_once '../pre_include.php';

define('NRAND', 10);

$PREDEFINED_GRP_NAMES = array(
	GID_ALL => 'All reg. users',
	GID_LOCK => 'Locked users',
	GID_GUEST => 'Guests',
	GID_ADMIN_USER => 'User admin.',
	GID_ADMIN_GROUP => 'User group admin.',
	GID_ADMIN_TEAM => 'User team admin.',
	GID_ADMIN_PROB => 'Problem admin.',
	GID_ADMIN_CONTEST => 'Contest admin.',
	GID_ADMIN_POST => 'Post admin.',
	GID_SUPER_SUBMITTER => 'Super submitter',
	GID_SUPER_RECORD_VIEWER => 'Super record viewer',
	GID_UINFO_VIEWER => 'User info. viewer'
);

$PREDEFINED_GRP_DESC = array(
	GID_ALL => 'All registered users',
	GID_LOCK => 'Locked users',
	GID_GUEST => 'Guests',
	GID_ADMIN_USER => 'User administrators (view/change user information)',
	GID_ADMIN_GROUP => 'User group administrators (add/remove user groups and assign administrators)',
	GID_ADMIN_TEAM => 'User team administrators',
	GID_ADMIN_PROB => 'Problem administrators (add/remove problems and manage problem groups)',
	GID_ADMIN_CONTEST => 'Contest administrators',
	GID_ADMIN_POST => 'Post administrators',
	GID_SUPER_SUBMITTER => 'Super submitter (' .
		'view and submit regardless of which contest the problem belongs to' .
		' or other limits on viewing or submitting problems)',
	GID_SUPER_RECORD_VIEWER => 'Super record viewer (view all records and sources)',
	GID_UINFO_VIEWER => 'User information viewer (view register IP, submission IP, real name, etc)'
);

foreach ($PREDEFINED_GRP_NAMES as $key => $val)
	$db->insert_into('user_grps', array(
		'pgid' => 0,
		'name' => $val,
		'desc' => $PREDEFINED_GRP_DESC[$key]
	));

$start = count($PREDEFINED_GRP_DESC) + 1;

for ($i = 0; $i < NRAND; $i ++)
{
	$pgid = rand(-1, $i - 1);
	if ($pgid == -1)
		$pgid = 0;
	else $pgid += $start;
	$db->insert_into('user_grps', array(
		'pgid' => $pgid,
		'name' => rand(),
		'desc' => rand()
	));
}

