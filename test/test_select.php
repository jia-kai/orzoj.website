<?php
require_once '../pre_include.php';

print_r($db->select_from('users', array('username')));


