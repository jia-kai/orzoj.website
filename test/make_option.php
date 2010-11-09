<?php
require_once '../pre_include.php';

option_set('judge_info_list', serialize(array('platform', 'description', 'cpuinfo', 'meminfo')));
option_set('static_password', 'hello');
option_set('email_validate_no_dns_check', '1');
option_set('max_src_length', 1024 * 32);
option_set('otalk_amount', 0);
option_set('orz_thread_reqid_max_size', 100);
option_set('orzoj_server_max_rint', 20);
