<?php
/*
Plugin Name:The same problem 
Description:Make all problems appear the same.
Author URI:http://www.fqj1994.org/
Plugin URI:http://www.orzoj.org/
Version:1.0
Supported Operation:settings
Author:Qijiang
 */


filter_add('after_prob_html','apbprob');
function apbprob($str,$pid)
{
	return option_get('The Same Problem','No Problem.');
}

?>
