<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';
require_once $includes_path . 'record.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'submit.php';

$src = '
#include <cstdio>
#include <cstdlib>
#include <sys/time.h>
int main()
{
	FILE *fin = fopen("a+b.in", "r"),
		*fout = fopen("a+b.out", "w");
	int a, b;
	fscanf(fin, "%d%d", &a, &b);

	struct timeval tv;
	gettimeofday(&tv, NULL);
	srand(tv.tv_sec * tv.tv_usec);
	if (rand() < RAND_MAX / 2)
		a ++;

	fprintf(fout, "%d\n", a + b);
	fclose(fin);
	fclose(fout);
}
	';

$row = $db->select_from('plang', 'id', array($DBOP['=s'], 'name', 'g++'));

$rid = submit_add_record(prob_get_id_by_code('a+b'),
	$row[0]['id'],
	$src);

submit_add_judge_req($rid, 'a+b.in', 'a+b.out');
