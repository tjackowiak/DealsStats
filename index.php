<?php

error_reporting(E_ALL | E_STRICT);
ini_set('memory_limit', "8M");

require dirname(__FILE__).'/__init_conf__.php';
require dirname(__FILE__).'/DealsStatsEnv.php';
require dirname(__FILE__).'/CiteamParser.php';
require dirname(__FILE__).'/WebPageHarvester.php';
require 'phpQuery.php';

$conf = dealstats_read_config_file('default');
// print_r($conf);
DealsStatsEnv::setEnvConfig($conf);

$ct = new CiteamParser();
$groups = $ct->getDealsGroups();

foreach($groups as $groupName => $groupData)
{
	$ct->getGroupOffers($groupName);
}

