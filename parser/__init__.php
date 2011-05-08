<?php

$sites = DealsStatsEnv::getEnvConfig('dealStats.sites');
$root = dirname(__FILE__).'/';
foreach($sites as $site)
{
	if(file_exists($root.$site.'.parser.php'))
		require $root.$site.'.parser.php';
	else
		trigger_error("File '".$root.$site.'.parser.php'."' not exists".PHP_EOL);
}