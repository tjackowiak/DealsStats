<?php

class WebPageHarvester
{
	private static $floodPrevent	= true;
	private static $pageTTL			= 3600;  // seconds
	private static $cachedPagesMap	= NULL;	// array()


	public static function harvestPage($url)
	{
		// echo '[WPH] GET '.$url.PHP_EOL;
		if(self::$floodPrevent === true && self::isInCache($url))
		{
			// echo '[WPH] GET '.$url.' - from cache'.PHP_EOL;
			$return = self::getPageFromCache($url);
		}
		else
		{
			// echo '[WPH] GET '.$url.' - from web'.PHP_EOL;
			$return = self::getPageFromWeb($url);
			if(self::$floodPrevent === true)
				self::cachePage($return);
		}
		return $return;
	}

	private static function checkCacheMap()
	{
		if(self::$cachedPagesMap === NULL)
		{
			$mapFile = DealsStatsEnv::getEnvConfig('webpageharvester.cache-dir');
			$mapFile .= '/WPH_map.php';
			// echo '[Checking] file:'.$mapFile;
			if(file_exists($mapFile))
			{
				self::$cachedPagesMap = include $mapFile;
				// echo ' - exists'.PHP_EOL;
			}
			// else
				// echo ' - not exists!'.PHP_EOL;
		}
	}
	private static function saveCacheMap()
	{
		$mapFile = DealsStatsEnv::getEnvConfig('webpageharvester.cache-dir');
		$mapFile .= '/WPH_map.php';
		// print_r(self::$cachedPagesMap);

		file_put_contents($mapFile,
			"<?php\n".
			"return ".var_export(self::$cachedPagesMap, true).';');
	}

	private static function isInCache($url)
	{
		self::checkCacheMap();

		if(isset(self::$cachedPagesMap[$url])
			&& self::$cachedPagesMap[$url]['cachedTime'] > time() - self::$pageTTL)
		{
			return true;
		}

		return false;
	}

	private static function getPageFromCache($url)
	{
		$pageFile = DealsStatsEnv::getEnvConfig('webpageharvester.cache-dir');
		$pageFile .= '/WPH_page_'.md5($url);

		if(file_exists($pageFile))
		{
			$return = include $pageFile;
		}
		else
		{
			throw new Exception("Requested non existing file", 1);
		}

		return $return;
	}

	private static function cachePage($data)
	{
		self::checkCacheMap();

		$pageFile = DealsStatsEnv::getEnvConfig('webpageharvester.cache-dir');
		$pageFile .= '/WPH_page_'.md5($data['url']);

		$data['cachedTime'] = time();

		if(file_put_contents($pageFile, 
			"<?php\n".
			"return ".var_export($data, true).';'))
		{
			self::$cachedPagesMap[$data['url']] = array(
				'cachedTime'	=> $data['cachedTime'],
				'httpCode'		=> $data['httpCode'],
				'siteIsUp'		=> $data['siteIsUp'],
				);
			self::saveCacheMap();
		}
	}

	private static function getPageFromWeb($url)
	{
		// echo '[WPH] hitting web for:'.$url.PHP_EOL;

		$agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 2);

		$page = curl_exec($ch);
		//echo curl_error($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($httpcode>=200 && $httpcode<300)
			$siteUp = true;
		else
			$siteUp = false;

		return array(
			'url'		=> $url,
			'httpCode'	=> $httpcode,
			'siteIsUp'	=> $siteUp,
			'html'		=> $page,
			);
	}
}