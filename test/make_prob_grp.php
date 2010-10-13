<?php

require_once '../pre_include.php';
$db->insert_into('prob_grps', array('id' => 1, 'name' => __('Mathematics')));
$db->insert_into('prob_grps', array('id' => 2, 'name' => __('DP')));
$db->insert_into('prob_grps', array('id' => 3, 'name' => __('Graph')));
$db->insert_into('prob_grps', array('id' => 4, 'name' => __('Geometry')));
$db->insert_into('prob_grps', array('id' => 5, 'name' => __('String')));
$db->insert_into('prob_grps', array('id' => 6, 'pgid' => 3, 'name' => __('Shortest Path')));
$db->insert_into('prob_grps', array('id' => 7, 'pgid' => 3, 'name' => __('Network Flow')));
$db->insert_into('prob_grps', array('id' => 8, 'pgid' => 7, 'name' => __('The title should long enough to test whether the typesetting is right.')));
