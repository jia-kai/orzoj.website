<?php
require_once '../pre_include.php';

$db->insert_into('plang', array('name' => 'g++', 'syntax' => 'cpp'));
$db->insert_into('plang', array('name' => 'gcc', 'syntax' => 'c'));
$db->insert_into('plang', array('name' => 'pascal', 'syntax' => 'pascal'));

$db->insert_into('wlang', array('name' => 'English', 'file' => 'xx'));
$db->insert_into('wlang', array('name' => 'Chinese', 'file' => 'xx'));

