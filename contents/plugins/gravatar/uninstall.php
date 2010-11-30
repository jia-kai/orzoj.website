<?php
if ($db->table_exists('plugin_gravatar_enabled_users'))
	$db->delete_table('plugin_gravatar_enabled_users');
?>
