<?php
/*
Plugin Name:ORZ-Gravatar
Description:Use Gravatar as the avatar.
Author URI:http://www.fqj1994.org/
Plugin URI:http://www.orzoj.org/
Version:1.0
Author:Qijiang
 */


filter_add('after_avatar_get_url_by_user_id','gravatar_user_avatar');
filter_add('before_user_class_construct','gravatar_user_avatar_uclass');
filter_add('after_user_register_form','gravatar_user_register_form');
filter_add('after_user_update_info_form','gravatar_user_register_form');
filter_add('after_user_update_info','gravatar_updatedb');
filter_add('after_user_register','gravatar_updatedb');
function gravatar_user_avatar($url,$uid)
{
	static $size = 40;
	global $db,$DBOP;
	$result = $db->select_from('plugin_gravatar_enabled_users',NULL,array($DBOP['='],'uid',$uid));
	if (count($result))
	{
		$email = user_get_email($uid);
		if ($email == NULL)
			return $url;
		else
		{
			$grav_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?s=" . $size;
			return $grav_url;
		}
	}
	return $url;
}

function gravatar_user_avatar_uclass($p)
{
	$p['avatar'] = gravatar_user_avatar($p['avatar'],$p['id']);
	return $p;
}

function gravatar_user_register_form($str, $uid = 0)
{
	$checkbox = '';
	if ($uid > 0)
	{
		global $db,$DBOP;
		$rs = $db->select_from('plugin_gravatar_enabled_users',NULL,array($DBOP['='],'uid',$uid));
		if (count($rs))
			$checkbox = ' value="1" checked';
	}
	return $str.tf_form_get_raw('<input name="gravatar_enabled" type="checkbox"'.$checkbox.'>'.__('Use Gravatar to replace my avatar!').'</input>',NULL);
}


function gravatar_updatedb($uid)
{
	global $db,$DBOP;
	if ($uid)
	{
		$result = $db->select_from('plugin_gravatar_enabled_users',NULL,array($DBOP['='],'uid',$uid));
		$use = false;
		if (isset($_POST['gravatar_enabled']) && $_POST['gravatar_enabled'])
			$use = true;
		if (count($result))
		{
			if ($use) return;
			else
			{
				$db->delete_item('plugin_gravatar_enabled_users',array($DBOP['='],'uid',$uid));
				return;
			}
		}
		else
		{
			if ($use)
			{
				$db->insert_into('plugin_gravatar_enabled_users',array('uid' => $uid));
				return;
			}
			else
				return;
		}
	}
}

