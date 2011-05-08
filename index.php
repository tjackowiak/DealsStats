<?php
error_reporting(E_ALL | E_STRICT);
ini_set('memory_limit', "8M");

require dirname(__FILE__).'/DealsStatsEnv.php';
require dirname(__FILE__).'/conf/__init_conf__.php';
$conf = dealstats_read_config_file('default');
DealsStatsEnv::setEnvConfig($conf);
require dirname(__FILE__).'/parser/__init__.php';
require dirname(__FILE__).'/WebPageHarvester.php';
require 'phpQuery.php';



// Cykl zycia
// ----------
// 1. pobieramy grupy ofert dla portalu (np. miasta)
$site = 'citeam.pl';
$ct = new CiteamParser();
$groups = $ct->getDealsGroups();

// 2. z bazy wyciagamy oferty, ktore w danej grupie powinny byc jeszcze aktywne
$dbRoot = dirname(__FILE__).'/db/';
$db = new SQLite3($dbRoot.$site.'.sqlite3');

$offers = array();
foreach($groups as $groupName => $groupData)
{
	echo $groupName.PHP_EOL;
	$offers += $ct->getGroupOffers($groupName);
}

// 3. ze strony pobieramy dostepna liste ofert dla danej grupy
// 4. weryfikujemy, czy pojawily sie nowe oferty
// 4.1. jesli tak pobieramy o nich informacje
// 5. aktualizujemy informacje o trwajacych ofertach:
//  - ilosc sprzedanych kuponow
//  - status (aktywna/wyprzedana)

die();


















$offers = isort($offers, 'sold');

foreach($offers as $offer)
{

	$end_time = $offer['endingTime'] - time();
	$timeLeft = time_difference($end_time);
	$offer = (object)$offer;

	echo "[$offer->group]\t$offer->title}\n"
	."[$offer->id][$offer->status]\t"
	."[Price: $offer->priceRegular -> $offer->pricePromo]\t"
	."[Sold: $offer->sold]\t"
	."[Ending: ".date("Y-m-d H:i:s", $offer->endingTime)."]\t"
	."[Left: ${timeLeft['days']}d ${timeLeft['hours']}h "
	."${timeLeft['mins']}m ${timeLeft['secs']}s]\n"
	// ."=================================================================\n"
	.str_repeat("=", 90)."\n"

	."\n";	
}
