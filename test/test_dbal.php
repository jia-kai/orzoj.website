<?php
require_once '../pre_include.php';

function getmicrotime()
{ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
}



$start = getmicrotime();
$m = 10;
$whereclause = array($DBOP['||'], $DBOP['<='], 'id', 300, $DBOP['<='], 'id', 400);

for ($i = 0; $i < $m; $i ++)
	$res = $db->select_from('announcements', NULL, $whereclause) or die('error:' . mysql_error());

echo count($res) . "<br />";
$end = getmicrotime();
echo ($end - $start);

