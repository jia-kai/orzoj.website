<?php
require_once '../pre_include.php';

$db->delete_item('plang');
$db->delete_item('wlang');

$db->insert_into('plang', array('name' => 'g++', 'type' => 'cpp'));
$db->insert_into('plang', array('name' => 'gcc', 'type' => 'c'));
$db->insert_into('plang', array('name' => 'pascal', 'type' => 'pascal'));

$db->insert_into('wlang', array('name' => 'English', 'file' => 'en_US'));
$db->insert_into('wlang', array('name' => 'Simplified Chinese (PRC)', 'file' => 'zh_CN'));
$db->insert_into('wlang', array('name' => 'Traditional Chinese (Taiwan)', 'file' => 'zh_TW'));

