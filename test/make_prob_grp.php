<?php

require_once '../pre_include.php';
$db->insert_into('prob_grps', array('id' => 1, 'title' => __('Mathematics')));
$db->insert_into('prob_grps', array('id' => 2, 'title' => __('DP')));
$db->insert_into('prob_grps', array('id' => 3, 'title' => __('Graph')));
$db->insert_into('prob_grps', array('id' => 4, 'title' => __('Geometry')));
$db->insert_into('prob_grps', array('id' => 5, 'title' => __('String')));
$db->insert_into('prob_grps', array('id' => 6, 'pgid' => 3, 'title' => __('Shortest Path')));
$db->insert_into('prob_grps', array('id' => 7, 'pgid' => 3, 'title' => __('Network Flow')));
