<?php
require_once '../pre_include.php';
require_once $includes_path . 'xhtml_validator.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<body>

<?
if (isset($_POST['text']))
{
	$txt = $_POST['text'];
	$v = new Xhtml_validator($txt);
	try {
		$v->load();
		echo $v->show_tree();
	} catch (Xhtml_validator_exc $e) {
		$s = new Xhtml_validator_show_err($txt, $e);
		echo $s->show();
	}
}

?>
<form method='POST' style="width:80%;height:500px;margin:auto">
<textarea name="text" style="width:100%;height:80%"> </textarea> <br />
<input type='submit' />
</form>
</body>
</html>
