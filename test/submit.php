<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';
require_once $includes_path . 'record.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'submit.php';

$src = 'main(a,b){scanf("%d%d",&a,&b);printf("%d\n",a+b);return 0;}';

$row = $db->select_from('plang', 'id', array($DBOP['=s'], 'name', 'gcc'));

$rid = submit_add_record(prob_get_id_by_code('a+b'),
	$row[0]['id'],
	$src);

submit_add_judge_req($rid, '', '');
