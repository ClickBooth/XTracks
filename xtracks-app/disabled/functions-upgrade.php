<?php

class PROSPER202
{
	function mysql_version()
	{
		//select the mysql version
		$version_sql = "SELECT version FROM 202_version";
		$version_result = mysql_query($version_sql);
		$version_row = @mysql_fetch_assoc($version_result);
		$mysql_version = $version_row['version'];

		//if there is no mysql version, this is an older 1.0.0-1.0.2 release, just return version 1.0.0 for simplicitly sake
		if (!$mysql_version) { $mysql_version = '1.0.2';}

		return $mysql_version;
	}

	function php_version()
	{
		global $version;
		$php_version = $version;
		return $php_version;
	}
}