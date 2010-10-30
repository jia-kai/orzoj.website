<?php
require_once '../pre_include.php';

option_set('judge_info_list', serialize(array('platform', 'description', 'cpuinfo', 'meminfo')));
option_set('static_password', 'hello');
option_set('email_validate_no_dns_check', '1');
option_set('max_src_length', 1024 * 32);


