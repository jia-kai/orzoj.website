<?php
require_once '../pre_include.php';

function getmicrotime()
{ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
}

$start = getmicrotime();
$m = 10;
for ($i = 0; $i < $m; $i ++)
{
	$res = mysql_query('SELECT * FROM orzojnew.orzoj_announcements WHERE (id <= 300 || id <= 400)');
	if (!$res)
		die(mysql_error());
	$result = array();
	while ($tmp = mysql_fetch_array($res))
		$result[] = $tmp;
}
echo count($result) . "<br />";
$end = getmicrotime();
echo ($end - $start);


