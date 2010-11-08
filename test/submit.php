<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';
require_once $includes_path . 'record.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'submit.php';

$src = '
#include <cstdio>

int main()
{
//	freopen("a+b.in", "r", stdin); freopen("a+b.out", "w", stdout);
	int a, b;
	scanf("%d%d", &a, &b);
	printf("%d\n", a + b);
	return 0;
}
	';

if (isset($_GET['src']))
	die(htmlencode($src));
$n = 1;
if (isset($_GET['n']))
	$n = intval($_GET['n']);

$row = $db->select_from('plang', 'id', array($DBOP['=s'], 'name', 'g++'));

for ($i = 0; $i < $n; $i ++)
{
	$rid = submit_add_record(prob_get_id_by_code('a+b'),
		$row[0]['id'],
		$src, array('', ''));
}

