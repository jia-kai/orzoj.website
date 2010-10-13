<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';
require_once $includes_path . 'record.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'submit.php';

$src = <<<_EOF_
#include <cstdio>

int main()
{
	freopen("a+b.in", "r", stdin);
	freopen("a+b.out", "w", stdout);
	int a, b;
	scanf("%d %d", &a, &b);
	printf("%d\\n", a + b + 1);
}

_EOF_;

$row = $db->select_from('plang', 'id', array($DBOP['=s'], 'name', 'g++'));

$rid = submit_add_record(prob_get_id_by_code('a+b'),
	$row[0]['id'],
	$src);

submit_add_judge_req($rid, 'a+b.in', 'a+b.out');
