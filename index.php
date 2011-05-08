<?php

error_reporting(E_ALL | E_STRICT);
ini_set('memory_limit', "8M");

require dirname(__FILE__).'/__init_conf__.php';
require dirname(__FILE__).'/DealsStatsEnv.php';
require dirname(__FILE__).'/CiteamParser.php';
require dirname(__FILE__).'/WebPageHarvester.php';
require 'phpQuery.php';

$conf = dealstats_read_config_file('default');
DealsStatsEnv::setEnvConfig($conf);

$ct = new CiteamParser();
$groups = $ct->getDealsGroups();

$offers = array();
foreach($groups as $groupName => $groupData)
{
	echo $groupName.PHP_EOL;
	$offers += $ct->getGroupOffers($groupName);
}

$offers = isort($offers, 'sold');

foreach($offers as $offer)
{
	echo "[${offer['id']}][${offer['status']}]\t${offer['title']}\n"
	."================================================================\n"
	."[${offer['priceRegular']} -> ${offer['pricePromo']}]\t"
	."[Sold: ${offer['sold']}]\t"
	."[Ending: ".date("Y-m-d H:i:s", $offer['endingTime'])."]"

	."\n\n";	
}
