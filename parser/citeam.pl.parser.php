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

		// print_r($page['httpCode'].' - '.strlen($page['html']));

		phpQuery::newDocument($page['html']);
		foreach( pq('entry') as $entry )
		{
			$url = pq($entry)->find('link')->attr('href');
			$details = $this->getOfferDetails($url);
			$details['group'] = $groupName;

			$this->dealsGroups[$groupName]['offers']
				[$details['id']] = $details;
		}

		return $this->dealsGroups[$groupName]['offers'];
	}

	/**
	 * Metoda sluzy do pobierania informacji o nowej ofercie
	 * Zalozenie jest takie, ze wczesniej nie mielismy o niej zadnych danych
	 * Szczegolna uwage nalezy zwrocic na bledy sugerujace zmiane kodu strony.
	 * W takim przypadku rzucamy blad i nie zapisujemy danych. 
	 */
	public function getOfferDetails($url)
	{
		// echo "\tgetting info for $url\n";
		$page = WebPageHarvester::harvestPage($url);
		phpQuery::newDocument($page['html']);

		/**
		 * Id Oferty
		 *
		 * Szukany znacznik:
		 * <a class="buyNow" href="/transakcje/new?offer=621" title="Kup Teraz!"
		 * rel="nofollow">Kup teraz!</a> 
		 */
		$offerId = pq("a.buyNow[href^='/transakcje/new?offer']")->attr("href");
		$offerId = preg_find('/\d+$/', $offerId);
		if(empty($offerId))
		{
			// Jesli oferta nie jest aktywna, oznaczone jest to przez znaczik:
			// <span class="buyNow inactive" title="Kup Teraz!">
			// w takim przypadku szukamy id oferty w linkach do obrazkow.
			// Nie powinno sie to jednak zdazyc przy nowych ofertach.
			trigger_error("[CITEAM PARSER] Nowa oferta ($url) bez id".
				" i aktywnego 'Kup teraz!'", E_USER_WARNING);

			if(pq("span.buyNow.inactive")->size() === 1)
			{
				$offerId = pq("div.offerContent>img[src*='main_offer_photos']")
					->attr('src');
				$offerId = preg_find('/main_offer_photos\/(\d+)\//',
					$offerId, 1);
			}
		}
		// Nie udalo sie wykryc Id oferty
		if(empty($offerId))
			throw new Exception("Empty OfferId!", 1);


		/**
		 * Tytul oferty
		 *
		 * W czystej postaci wystepuje w znacznikach dla Facebooka
		 */
		$offerTitle = pq("meta[property='og:title']")->attr('content');
		if(empty($offerTitle))
			throw new Exception("Empty OfferTitle!", 1);

		
		/**
		 * Status oferty
		 *
		 * Oferta moze byc jeszcze dostepna (ending time), ale bez mozliwosci
		 * kupowania kuponow (wyprzedana)
		 */
		$offerStatus = 'active';
		if(pq("a.buyNow[href^='/transakcje/new?offer']")->size() === 0
			&& pq("span.buyNow.inactive")->size() === 1)
		{
			$offerStatus = 'inactive';
		}

		/**
		 * Ilosc sprzedanych kuponow
		 *
		 * Znacznik:
		 * <div class="box amount">
		 *  <span class="counter">
		 */
		$offerSold = trim(pq("div.box.amount span.counter")->html());
		if(!is_numeric($offerSold))
			throw new Exception("Wrong 'offers sold' value", 1);
		$offerSold = (int)$offerSold;
			
		/**
		 * Cena
		 *
		 * Znacznik:
		 * <div class="priceBox"> 
    	 *	<div class="prices">
    	 *   <span class="promoPrice">199 </span> 
    	 *   <span class="regPrice">420 </span>
    	 */
      	$price = pq("div.priceBox div span");
		$offerPricePromo 	= trim($price->filter(".promoPrice")->html());
		$offerPriceReg		= trim($price->filter(".regPrice")->html());
		$offerPricePromo 	= preg_find('/\d+(,\d+)?/', $offerPricePromo);
		$offerPriceReg		= preg_find('/\d+(,\d+)?/', $offerPriceReg);
		$offerPricePromo 	= s2f($offerPricePromo);
		$offerPriceReg		= s2f($offerPriceReg);

		if(empty($offerPricePromo) || empty($offerPriceReg))
			throw new Exception("Wrong price value", 1);

		/**
		 * Data waznosci
		 *
		 * Znacznik:
		 * <div id="countDown" data-ending-time="2011-05-08">
		 * Data uplywa z koncem podanego dnia 
		 */
		$endingTime = pq("#countDown")->attr('data-ending-time');
		$endingTime = strtotime("+1 day", strtotime($endingTime));

		if(empty($endingTime))
			throw new Exception("Wrong 'ending time' value", 1);
			
			

		return array(
			'id'			=> $offerId,
			'title'			=> $offerTitle,
			'sold'			=> $offerSold,
			'pricePromo'	=> $offerPricePromo,
			'priceRegular'	=> $offerPriceReg,
			'status'		=> $offerStatus,
			'endingTime'	=> $endingTime,
		);
	}
}