<?php
function conf_file_generate($dblayer,$dbhost,$dbport,$dbusername,$dbpassword,$dbname,$tablepre)
{
	$dbusername = addslashes($dbusername);
	$dbpassword = addslashes($dbpassword);
	$dbname = addslashes($dbname);
	$tablepre = addslashes($tablepre);
	$str = <<<EOF
<?php
\$dblayer = '$dblayer';
\$dbhost = '$dbhost';
\$dbport = '$dbport';
\$dbusername = '$dbusername';
\$dbpassword = '$dbpassword';
\$dbname = '$dbname';
\$tablepre = '$tablepre';
EOF;
	$fpointer = fopen("config.php","w");
	if ($fpointer)
	{
		fprintf($fpointer,"%s",$str);
		fclose($fpointer);
		return true;
	}
	else
		return false;
}


