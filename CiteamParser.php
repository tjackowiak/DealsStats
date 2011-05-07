<?php

class CiteamParser
{
	private $dealsGroups = array();

	public function getDealsGroups()
	{
		$mainPage = DealsStatsEnv::getEnvConfig('citeam.base-url');
		$mainPage .= DealsStatsEnv::getEnvConfig('citeam.main-page');
		$site = WebPageHarvester::harvestPage($mainPage);

		phpQuery::newDocument($site['html']);
		foreach( pq('ul.cityList li a') as $li )
		{
			$name	= pq($li)->text();
			$url	= pq($li)->attr('href');
			$this->dealsGroups[$name]['url'] = $url;
		}

		return $this->dealsGroups;
	}

	public function getGroupOffers($groupName)
	{
		if(!isset($this->dealsGroups[$groupName]))
			throw new Exception("Unknown group: $groupName", 1);

		$page = DealsStatsEnv::getEnvConfig('citeam.base-url');
		$page .= $this->dealsGroups[$groupName]['url'] . '.atom';
		$page = WebPageHarvester::harvestPage($page);

		print_r($page['httpCode'].' - '.strlen($page['html']));

		phpQuery::newDocument($page['html']);
		foreach( pq('entry') as $entry )
		{
			echo "\t".pq($entry)->find('title')->text().PHP_EOL;
		}
	}

	public function getOfferDetails
}