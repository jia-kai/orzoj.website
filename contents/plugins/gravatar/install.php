<?php
if ($db->table_exists('plugin_gravatar_enabled_users'))
	$db->delete_table('plugin_gravatar_enabled_users');
$db->create_table('plugin_gravatar_enabled_users',array(
	'cols' => array('uid' => array('type' => 'INT32')),
	'index' => array(
		array('type' => 'UNIQUE','cols' => array('uid'))
	)
	));
?>
