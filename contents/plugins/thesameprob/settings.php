<?php
if (isset($_GET['step']) && $_GET['step'] == 1)
{
	option_set('The Same Problem',$_POST['prob']);
	echo 'succ';
}
else
{
?>
	<form action="<?php echo plugin_get_configure_uri(array('step' => 1))?>" method="post">
Problem Content:<Br/>
<textarea name="prob" cols="50" rows="10"><?php echo htmlencode(option_get('The Same Problem',''));?></textarea><Br/>
<input name="submit" type="submit" value="submit">
</form>
<?php
}
?>
