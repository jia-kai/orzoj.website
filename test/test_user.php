<?php
require_once '../pre_include.php';
require_once $includes_path . 'user.php';

$VAL_SET = array('username', 'password', 'realname', 'aid',
	'email', 'self_desc', 'tid', 'plang', 'wlang');

?>
<html><body>
<?php

try
{
	if (isset($_GET['action']))
		$action = $_GET['action'];
	if (user_check_login())
		echo htmlencode("log in as:\n" . print_r($user, TRUE), TRUE);
	else echo 'not log in <br /><br />';

	if (isset($action) && $action == 'logout')
		user_logout();

	if (!isset($action) || $action != 'register')
	{
?>
register new user
<form action="?action=register" method="POST">
<?php
		foreach ($VAL_SET as $val)
			echo "$val:<input type='text' name='$val' /><br />\n";
?>
<input type='submit' />
</form>
<br /><br />

log in
<form action="?action=login" method="POST">
user name:<input type='text' name='username' /><br />
password:<input type='text' name='password' /><br />
<input type='submit' />
</form>

<?php
	}
	else
	{
		if (!user_check_name($_POST['username']))
			echo 'invalid user name';
		else echo 'uid: ' . user_add($_POST, $_POST['password']);
	}
}
catch (Exc_orzoj $e)
{
	echo 'error: <br />' . htmlencode($e->msg());
}

?>
</body></html>

