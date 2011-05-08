<?php

/**
 * BD structure
 */

/**
 * Deals
 */
$schema = <<<'TXT'
DROP TABLE IF EXISTS deals;
CREATE TABLE deals (
	'id'            PRIMARY KEY,
	'title'         STRING,
	'group'         STRING,
	'priceRegular'  FLOAT,
	'pricePromo'    FLOAT,
	'timeStart',
	'timeEnd',
	'link'          STRING,
	'sold'          INTEGER
);
CREATE UNIQUE INDEX iDealsId ON deals(id);
CREATE UNIQUE INDEX iDealsTimeEnd ON deals(timeEnd);

DROP TABLE IF EXISTS dealsUpdates;
CREATE TABLE dealsUpdates (
	id        INTEGER,
	time      INTEGER,
	quantity  INTEGER,

	FOREIGN KEY(id) REFERENCES deals(id)
);
CREATE INDEX iDealsUpdatesTime ON dealsUpdates(time);

DROP TABLE IF EXISTS dealsDailyStats;
CREATE TABLE dealsDailyStats (
	'id'        INTEGER,
	'group'     STRING,
	'date'      INTEGER,
	'quantity'  INTEGER,

	FOREIGN KEY(id) REFERENCES deals(id)
);
TXT;


/**
 * Create dabase files
 */
$dbRoot = dirname(dirname(__FILE__)).'/db/';
$sites = array(
	'citeam.pl',
	'groupon.pl',
	'fastdeal.pl',
);

foreach($sites as $site)
{
	echo 'Creating database for '.$site.PHP_EOL;
	$db = new SQLite3($dbRoot.$site.'.sqlite3');
	$db->exec($schema);
}
