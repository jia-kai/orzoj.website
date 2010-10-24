<?php
$dir = opendir('./');
while ($file = readdir($dir))
{
	if (strpos($file,'make') !== FALSE)
	{
		require_once($file);
	}
}
