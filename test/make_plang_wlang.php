<?php
require_once '../pre_include.php';

$db->delete_item('plang');
$db->delete_item('wlang');

$db->insert_into('plang', array('name' => 'g++', 'type' => 'cpp'));
$db->insert_into('plang', array('name' => 'gcc', 'type' => 'c'));
$db->insert_into('plang', array('name' => 'fpc', 'type' => 'pas'));

$db->insert_into('wlang', array('name' => 'English', 'file' => 'en_US'));
$db->insert_into('wlang', array('name' => '简体中文(中国大陆)', 'file' => 'zh_CN'));
$db->insert_into('wlang', array('name' => '正體中文(中國臺灣)', 'file' => 'zh_TW'));

