<?php
require_once '../pre_include.php';

option_set('judge_info_list', serialize(array('cpuinfo', 'meminfo', 'description')));
option_set('static_password', 'hello');

printf("plang id: %d\n", $db->insert_into('plang', array('name' => 'g++')));


