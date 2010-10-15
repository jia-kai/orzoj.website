<?php

require_once '../pre_include.php';

$db->delete_item('themes');
$db->insert_into('themes', array('id' => DEFAULT_THEME_ID, 'name' => 'default'));
