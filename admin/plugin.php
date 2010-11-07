<?php
/*
 * $File: plugin.php
 * $Date: Sun Nov 07 19:56:20 2010 +0800
 * $Date: Sun Nov 07 19:56:20 2010 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('IN_ORZOJ'))
	exit;

$baseurl = 'index.php?page=plugin';
$plugin_dir = $root_path . 'contents/plugins/';
if (!isset($_GET['action']))
	$_GET['action'] = '';
$_GET['action'] = trim($_GET['action']);

$installed_plugins = unserialize(option_get('installed_plugins',serialize(array())));
$enabled_plugins = unserialize(option_get('enabled_plugins',serialize(array())));

/**
 *
 * From wordpress
 *
 * Strip close comment and close php tags from file headers used by WP
 * See http://core.trac.wordpress.org/ticket/8497
 *
 *
 * @param string $str
 * @return string
 */
function _cleanup_header_comment($str) {
	return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
}


/**
 *
 * From wordpress
 *
 * Parse the file contents to retrieve its metadata.
 *
 * Searches for metadata for a file, such as a plugin or theme.  Each piece of
 * metadata must be on its own line. For a field spanning multple lines, it
 * must not have any newlines or only parts of it will be displayed.
 *
 * Some users have issues with opening large files and manipulating the contents
 * for want is usually the first 1kiB or 2kiB. This function stops pulling in
 * the file contents when it has all of the required data.
 *
 * The first 8kiB of the file will be pulled in and if the file data is not
 * within that first 8kiB, then the author should correct their plugin file
 * and move the data headers to the top.
 *
 * The file is assumed to have permissions to allow for scripts to read
 * the file. This is not checked however and the file is only opened for
 * reading.
 *
 *
 * @param string $file Path to the file
 * @param bool $markup If the returned data should have HTML markup applied
 */
function _get_file_data( $file, $default_headers) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 8192 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	$all_headers = $default_headers;


	foreach ( $all_headers as $field => $regex ) {
		preg_match( '/' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, ${$field});
		if ( !empty( ${$field} ) )
			${$field} = _cleanup_header_comment( ${$field}[1] );
		else
			${$field} = '';
	}

	$file_data = compact( array_keys( $all_headers ) );

	return $file_data;
}

$_errorinfo = FALSE;

/**
 * @ignore
 */
function _plugin_opendir_error_handler($errorno,$errorstr,$errfile)
{
	global $_errorinfo;
	$_errorinfo = $errorstr;
}

/**
 * @ignore
 */
function _plugin_opendir_get_error()
{
	global $_errorinfo;
	return $_errorinfo;
}

/**
 * @ignore
 */
function _get_supported_operation($file)
{
	$op = array();
	$file .= '/';
	if (file_exists($file.'install.php')) $op[] = 'install';
	if (file_exists($file.'uninstall.php')) $op[] = 'uninstall';
	if (file_exists($file.'settings.php')) $op[] = 'settings';
	return $op;
}

/**
 * @ignore
 */
function list_plugin()
{
	/* {{{ */
	global $plugin_dir,$installed_plugins,$enabled_plugins;
	global $plugin_dir,$installed_plugins,$enabled_plugins,$baseurl;
	$headers = array(
		'Name' => 'Plugin Name','Description' => 'Description','AuthorURI' => 'Author URI','PluginURI' => 'Plugin URI','Version' => 'Version','Author' => 'Author'
	);
?>
<table width="100%" border="1">
<tr><th><?php echo __('Plugin')?></th><th><?php echo __('Description')?></th></tr>
<?php
	set_error_handler('_plugin_opendir_error_handler');
	$dir = opendir($plugin_dir);
	if ($dir)
	{
		while ($fl = readdir($dir))
		{
			if ($fl[0] == '.') continue;
			$pgdir = $fl;
			$fl = $plugin_dir . $fl;
			if (is_dir($fl))
			{
				if (!file_exists($fl . '/plugin.php'))
					echo sprintf('<tr><td>%s</td><td>%s</td></tr>',__('Unknown'),__('Invalid Plugin'));
				else
				{
					$arr = _get_file_data($fl . '/plugin.php', $headers);
					echo '<tr><td>';
					if (strlen($arr['PluginURI']) > 0) echo '<a href="',$arr['PluginURI'],'">';
					echo $arr['Name'];
					if (strlen($arr['Version'] > 0)) echo '(',$arr['Version'],')';
					if (strlen($arr['PluginURI']) > 0) echo '</a>';
					if (strlen($arr['Author']) > 0)
					{
						echo ' By ';
						if (strlen($arr['AuthorURI']) > 0)
							echo '<a href="',$arr['AuthorURI'],'">';
						echo $arr['Author'];
						if (strlen($arr['AuthorURI']) > 0)
							echo '</a>';
					}
					echo '</td><td>';
					echo $arr['Description'];
					$supported_operation = _get_supported_operation($fl);
					if (in_array($pgdir, $enabled_plugins))
					{
						echo '<br />';
						echo '<a href="',$baseurl,'&action=deactivate&pluginname=',htmlencode($pgdir),'">',__('Deactivate'),'</a>&nbsp;';
						if (in_array('settings',$supported_operation))
						{
							echo '<br />';
							echo '<a href="',$baseurl,'&action=settings&pluginname=',htmlencode($pgdir),'">',__('Settings'),'</a>&nbsp;';
						}
					}
					else
					{
						if (in_array('install', $supported_operation) && !in_array($pgdir, $installed_plugins))
						{
							echo '<br />';
							echo '<a href="',$baseurl,'&action=install&pluginname=',htmlencode($pgdir),'">',__('Install'),'</a>&nbsp;';
						}
						if (in_array('uninstall',$supported_operation) && in_array($pgdir, $installed_plugins) && !in_array($pgdir, $enabled_plugins))
						{
							echo '<br />';
							echo '<a href="',$baseurl,'&action=uninstall&pluginname=',htmlencode($pgdir),'">',__('Uninstall'),'</a>&nbsp;';
						}
						if (((in_array($pgdir,$installed_plugins)) || (!in_array('install',$supported_operation))) && (!in_array($pgdir,$enabled_plugins)))
						{
							echo '<br />';
							echo '<a href="',$baseurl,'&action=activate&pluginname=',htmlencode($pgdir),'">',__('Activate'),'</a>&nbsp;';
						}
					}
					echo '</td></tr>';
				}
			}
		}
?>
</td></tr>
<?php
	}
?>
</table>
<?php
restore_error_handler();
$err = _plugin_opendir_get_error();
if ($err)
{
	trigger_error($err);
}
/* }}} */
}

/**
 * @ignore
 */
function install_plugin($plugname)
{
	global $installed_plugins;
	if (in_array($plugname, $installed_plugins))
		return;
	else
	{
		$installed_plugins[] = $plugname;
		option_set('installed_plugins',serialize($installed_plugins));
	}
}

/**
 * @ignore
 */
function uninstall_plugin($plugname)
{
	global $installed_plugins;
	while (($key = array_search($plugname, $installed_plugins)) !== FALSE)
	{
		unset($installed_plugins[$key]);
	}
	option_set('installed_plugins',serialize($installed_plugins));
}

/**
 * @ignore
 */
function activate_plugin($plugname)
{
	global $enabled_plugins;
	if (in_array($plugname, $enabled_plugins))
		return;
	else
	{
		$enabled_plugins[] = $plugname;
		option_set('enabled_plugins',serialize($enabled_plugins));
	}
}

/**
 * @ignore
 */
function deactivate_plugin($plugname)
{
	global $enabled_plugins;
	while (($key = array_search($plugname, $enabled_plugins)) !== FALSE)
		unset($enabled_plugins[$key]);
	option_set('enabled_plugins',serialize($enabled_plugins));
}



switch ($_GET['action'])
{
case 'install':
	require_once $plugin_dir . $_GET['pluginname']. '/install.php';
	install_plugin($_GET['pluginname']);
	echo __('This plugin is successfully installed.');
	break;
case 'uninstall':
	require_once $plugin_dir . $_GET['pluginname']. '/uninstall.php';
	uninstall_plugin($_GET['pluginname']);
	echo __('This plugin is successfully uninstalled.');
	echo __('Click <a href="%s">here</a> to remove all files of this plugin.',$baseurl . '&action=delete&pluginname=' . htmlencode($_GET['pluginname']));
	break;
case 'delete':
	delete_plugin($_GET['pluginname']);
	echo __('This plugin is successfully deleted.');
	break;
case 'activate':
	activate_plugin($_GET['pluginname']);
	echo __('This plugin is successfully activated.');
	break;
case 'deactivate':
	deactivate_plugin($_GET['pluginname']);
	echo __('This plugin is successfully deactivated.');
	break;
case 'settings':
	function plugin_get_configure_uri($GET)
	{
		global $baseurl;
		$hello = $baseurl.'&pluginname='.urlencode($_GET['pluginname']).'&action=settings';
		foreach ($GET as $key => $value)
		{
			$hello .= '&'.urlencode($key).'='.urlencode($value);
		}
		return $hello;
	}
	require_once $plugin_dir . $_GET['pluginname'] . '/settings.php';
	break;
case '':
	list_plugin();
	break;
default:
	set_error(404,'Not Found');
}

/*
 * vim:foldmethod=marker
 */
