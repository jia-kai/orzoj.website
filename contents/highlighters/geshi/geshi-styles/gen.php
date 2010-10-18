<?php
define('IN_ORZOJ', TRUE);
require_once '../geshi.php';

$formats = array('cpp', 'c', 'pas', 'java');
foreach ($formats as $f)
{
	$geshi = new GeSHi($f, $f);
	$geshi->enable_classes();
	$fout = fopen("$f.css", 'w');
	fwrite($fout, $geshi->get_stylesheet(FALSE));
	fclose($fout);
}

