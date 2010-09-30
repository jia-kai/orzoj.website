<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';
require_once $includes_path . 'record.inc.php';

$src = <<<_EOF_
#include <cstdio>

int main()
{
	freopen("a+b.in", "r", stdin);
	freopen("a+b.out", "w", stdout);
	int a, b;
	scanf("%d %d", &a, &b);
	printf("%d\\n", a + b);
while(1);
}

_EOF_;

$id = $db->insert_into('records',
	array(
		'uid' => user_get_id_by_name('jiakai'),
		'pid' => 1, 'lid' => lang_get_id_by_name('g++'),
		'src_len' => strlen($src),
		'status' => RECORD_STATUS_WAITING_TO_BE_FETCHED,
		'stime' => time(), 'ip' => 'mars'
	));

$db->insert_into('sources',
	array(
		'rid' => $id,
		'src' => $src,
		'time' => time()
	));

echo 'record id: ' . $id . "\n";

$reqid = $db->insert_into('msg_req',
	array('data' => serialize(array(
		'type' => 'src',
		'id' => $id,
		'prob' => 'apb',
		'lang' => 'g++',
		'src' => '',
		'input' => 'a+b.in',
		'output' => 'a+b.out'
	))));

echo 'msg_req id: ' . $reqid . "\n";

