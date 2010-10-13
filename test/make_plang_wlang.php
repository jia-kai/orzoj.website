<?php
require_once '../pre_include.php';

$db->insert_into('plang', array('name' => 'g++'));
$db->insert_into('plang', array('name' => 'pascal'));
$db->insert_into('plang', array('name' => 'gcc'));

$db->insert_into('wlang', array('name' => 'English', 'file' => 'xx'));
$db->insert_into('wlang', array('name' => 'Chinese', 'file' => 'xx'));

