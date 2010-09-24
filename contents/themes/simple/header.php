<?php
/*
 * $File: header.php
 * $Date: Sat Jul 17 23:13:13 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @license gpl 
 */
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=<?php t_charset()?>">
<title><?php
if (is_register())
{
	echo __('Register').' << ';
	t_webname();
}
else
{
	echo __('Not Found').' << ';t_webname();
}
?></title>
<?php t_html_head()?>
</head>
<body>
<div align="center" width="800">
<h1><a href="<?php echo t_siteurl()?>"><?php t_webname()?></a></h1>

