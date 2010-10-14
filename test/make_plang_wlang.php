<?php
require_once '../pre_include.php';

$db->insert_into('plang', array('name' => 'g++', 'syntax_hl' => 'cpp'));
$db->insert_into('plang', array('name' => 'gcc', 'syntax_hl' => 'cpp'));
$db->insert_into('plang', array('name' => 'pascal', 'syntax_hl' => 'pascal'));

$db->insert_into('wlang', array('name' => 'English', 'file' => 'xx'));
$db->insert_into('wlang', array('name' => 'Chinese', 'file' => 'xx'));

