<?php
/*
 * $File: register.php
 * $Date: Sat Jul 17 23:16:10 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @license gpl
 */
require_once 'header.php';
?>
<div align="center">
<?php
$register_form = t_get_register_form();
?>
	<form name="<?php echo$register_form['name']?>" method="<?php echo$register_form['method']?>" action="<?php echo$register_form['action']?>" target="<?php echo$register_form['target']?>">
<?php
foreach ($register_form['content'] as $id => $content)
{
	echo $content['title'].':'.$content['code'].'<br>';
}
foreach ($register_form['button'] as $id => $button)
{
	echo $button['code'];
}
?>
</form>
<?php
?>
</div>
<?php
require_once "footer.php";
?>
