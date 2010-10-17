<?php
require_once '../pre_include.php';

$db->delete_item('plang');
$db->delete_item('wlang');

$db->insert_into('plang', array('name' => 'g++', 'syntax' => 'cpp'));
$db->insert_into('plang', array('name' => 'gcc', 'syntax' => 'c'));
$db->insert_into('plang', array('name' => 'pascal', 'syntax' => 'pascal'));

$db->insert_into('wlang', array('name' => 'English', 'file' => 'en_US'));
$db->insert_into('wlang', array('name' => 'Chinese Simplified', 'file' => 'zh_CN'));

