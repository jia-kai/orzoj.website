<?php
define('IN_ORZOJ', TRUE);
require_once '../../includes/geshi.php';

$formats = array('cpp', 'c', 'pascal', 'java');
foreach ($formats as $f)
{
	$geshi = new GeSHi($f, $f);
	$geshi->enable_classes();
	$fout = fopen("$f.css", 'w');
	fwrite($fout, $geshi->get_stylesheet(FALSE));
	fclose($fout);
}

