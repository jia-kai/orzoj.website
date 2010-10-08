<?php
require_once '../pre_include.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<body>
<?
if (isset($_POST['text']))
{
	try
	{
		xhtml_validate($_POST['text']);
	} 
	catch (Exc_orzoj $e)
	{
		echo 'XHTML validator error:<br />';
		echo htmlencode($e->msg());
	}
}
?>
<form method='POST' style="width:80%;height:500px;margin:auto">
<textarea name="text" style="width:100%;height:80%"> </textarea> <br />
<input type='submit' />
</form>
</body>
</html>
