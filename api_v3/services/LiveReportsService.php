<?php

/**
 *
 * @service liveReports
 * @package api
 * @subpackage services
 */
class LiveReportsService extends VidiunBaseService
{
	// kava implementation
	protected function arrayToApiObject(array $input, $objectType)
	{
		$result = new $objectType;
		foreach ($input as $name => $value)
		{
			$result->$name = $value;
		}
		return $result;
	}
	
	protected function arrayToApiObjects(array $input, $objectType)
	{
		$result = array();
		foreach ($input as $item)
		{
			$result[] = $this->arrayToApiObject($item, $objectType);
		}
		return $result;
	}

	protected static function addCoordinates($items)
	{
		$keys = array();
		foreach ($items as $item)
		{
			$countryName = $item->countryName;
			$regionName = $item->regionName;
			$cityName = $item->cityName;
			$keys[vKavaBase::getCoordinatesKey(array($countryName))] = true;
			$keys[vKavaBase::getCoordinatesKey(array($countryName, $regionName, $cityName))] = true;
		}
		$coords = vKavaBase::getCoordinatesForKeys(array_keys($keys));
		// parse the coordinates
		$coords = array_map('vKavaBase::parseCoordinates', $coords);
		
		foreach ($items as $item)
		{
			$countryName = $item->countryName;
			unset($item->countryName);

			$regionName = $item->regionName;
			unset($item->regionName);

			$cityName = $item->cityName;
			unset($item->cityName);

			// country
			$item->country = new VidiunCoordinate();
			$item->country->name = strtoupper($countryName);
			$key = vKavaBase::getCoordinatesKey(array($countryName));
			if (isset($coords[$key]))
			{
				list($item->country->latitude, $item->country->longitude) = $coords[$key];
			}

			// city
			$item->city = new VidiunCoordinate();
			$item->city->name = strtoupper($cityName);
			$key = vKavaBase::getCoordinatesKey(array($countryName, $regionName, $cityName));
			if (isset($coords[$key]))
			{
				list($item->city->latitude, $item->city->longitude) = $coords[$key];
			}
		}
	}

	protected function getReportKava($reportType,
			VidiunLiveReportInputFilter $filter = null,
			VidiunFilterPager $pager = null)
	{
		if ($reportType != VidiunLiveReportType::ENTRY_TOTAL && 
			$pager->pageIndex > 1)
		{
			throw new APIException(VidiunErrors::ANALYTICS_UNSUPPORTED_QUERY);
		}

		$reportTypes = array(
			VidiunLiveReportType::PARTNER_TOTAL => 
				array('partnerTotal', 'VidiunLiveStats'),
			VidiunLiveReportType::ENTRY_TOTAL => 
				array('entryTotal', 'VidiunEntryLiveStats'),
			VidiunLiveReportType::ENTRY_GEO_TIME_LINE => 
				array('entryGeoTimeline', 'VidiunGeoTimeLiveStats'),
			VidiunLiveReportType::ENTRY_SYNDICATION_TOTAL => 
				array('entrySyndicationTotal', 'VidiunEntryReferrerLiveStats'),
		);
		
		if (!isset($reportTypes[$reportType]))
		{
			throw new APIException(VidiunErrors::ANALYTICS_UNSUPPORTED_QUERY);
		}
		
		list($methodName, $objectType) = $reportTypes[$reportType];
		if ($methodName == 'entryTotal' &&
			vString::beginsWith(vCurrentContext::$client_lang, 'KWP:'))
		{
			$methodName = 'entryQuality';
		} 

		try
		{
			list($items, $totalCount) = call_user_func(array('vKavaLiveReportsMgr', $methodName), 
				$this->getPartnerId(), 
				$filter, 
				$pager->pageIndex, 
				$pager->pageSize);
		}
		catch (vKavaNoResultsException $e)
		{
			$items = array();
			$totalCount = 0;
		}
		
		$items = $this->arrayToApiObjects($items, $objectType);
		if ($objectType == 'VidiunGeoTimeLiveStats' && $items)
		{
			self::addCoordinates($items);
		}
		
		$result = new VidiunLiveStatsListResponse();
		$result->objects = $items;
		$result->totalCount = $totalCount;
		return $result;
	}

	protected function getEventsKava($reportType,
			VidiunLiveReportInputFilter $filter = null)
	{
		if ($reportType != VidiunLiveReportType::ENTRY_TIME_LINE)
		{
			throw new APIException(VidiunErrors::ANALYTICS_UNSUPPORTED_QUERY);
		}
	
		try
		{
			$data = vKavaLiveReportsMgr::entryTimeline($this->getPartnerId(), $filter);
		}
		catch (vKavaNoResultsException $e)
		{
			$data = '';
		}
	
		$graph = new VidiunReportGraph();
		$graph->id = 'audience';
		$graph->data = $data;
			
		$result = new VidiunReportGraphArray();
		$result->offsetSet(null, $graph);
		return $result;
	}
	
	/**
	 * @action getEvents
	 * @param VidiunLiveReportType $reportType
	 * @param VidiunLiveReportInputFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunReportGraphArray
	 */
	public function getEventsAction($reportType,
			VidiunLiveReportInputFilter $filter = null,
			VidiunFilterPager $pager = null)
	{
		if(is_null($filter))
			$filter = new VidiunLiveReportInputFilter();
		if(is_null($pager))
			$pager = new VidiunFilterPager;
		
		if (vKavaBase::isPartnerAllowed($this->getPartnerId(), vKavaBase::LIVE_DISABLED_PARTNERS))
		{
			return $this->getEventsKava($reportType, $filter);
		}
		
		$client = new WSLiveReportsClient();
		$wsFilter = $filter->getWSObject();
		$wsFilter->partnerId = vCurrentContext::getCurrentPartnerId();
		$wsPager = new WSLiveReportInputPager($pager->pageSize, $pager->pageIndex);
		
		$wsResult = $client->getEvents($reportType, $wsFilter, $wsPager);
		$resultsArray = array();
		$objects = explode(";", $wsResult->objects);
		foreach($objects as $object) {
			if(empty($object))
				continue;
			
			$parts = explode(",", $object);
			$additionalValue = "";
			if(count($parts) > 2)
				$additionalValue = "," . $parts[2];
			$resultsArray[$parts[0]] = $parts[1] . $additionalValue;
		}
		
		$vResult = VidiunReportGraphArray::fromReportDataArray(array("audience" => $resultsArray));
		
		return $vResult;
	}
	
	/**
	 * @action getReport
	 * @param VidiunLiveReportType $reportType
	 * @param VidiunLiveReportInputFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunLiveStatsListResponse
	 */
	public function getReportAction($reportType, 
			VidiunLiveReportInputFilter $filter = null,
			VidiunFilterPager $pager = null)
	{
		if(is_null($filter))
			$filter = new VidiunLiveReportInputFilter();
		if(is_null($pager))
			$pager = new VidiunFilterPager();
		
		if (vKavaBase::isPartnerAllowed($this->getPartnerId(), vKavaBase::LIVE_DISABLED_PARTNERS))
		{
			return $this->getReportKava($reportType, $filter, $pager);			
		}
		
		ini_set('memory_limit', '700M');

		$client = new WSLiveReportsClient();
		$wsFilter = $filter->getWSObject();
		$wsFilter->partnerId = vCurrentContext::getCurrentPartnerId();
		
		$wsPager = new WSLiveReportInputPager($pager->pageSize, $pager->pageIndex);
		
		switch($reportType) {
			case VidiunLiveReportType::ENTRY_GEO_TIME_LINE:
			case VidiunLiveReportType::ENTRY_SYNDICATION_TOTAL:
				return $this->requestClient($client, $reportType, $wsFilter, $wsPager);
				
			case VidiunLiveReportType::PARTNER_TOTAL:
				if($filter->live && empty($wsFilter->entryIds)) {
					$entryIds = $this->getAllLiveEntriesLiveNow();
					if(empty($entryIds)) {
						$response = new VidiunLiveStatsListResponse();
						$response->totalCount = 1;
						$response->objects = array();
						$response->objects[] = new VidiunLiveStats();
						return $response;
					}
					
					$wsFilter->entryIds = $entryIds;
				}
				return $this->requestClient($client, $reportType, $wsFilter, $wsPager);
				
			case VidiunLiveReportType::ENTRY_TOTAL:
				$totalCount = null;
				if(!$filter->live && empty($wsFilter->entryIds)) {
					list($entryIds, $totalCount) = $this->getLiveEntries($client, vCurrentContext::getCurrentPartnerId(), $pager);
					if(empty($entryIds))
						return new VidiunLiveStatsListResponse();

					$wsFilter->entryIds = implode(",", $entryIds);
				}
				
				/** @var VidiunLiveStatsListResponse */
				$result = $this->requestClient($client, $reportType, $wsFilter, $wsPager);
				if($totalCount)
					$result->totalCount = $totalCount;

				if ($entryIds) {
					$this->sortResultByEntryIds($result, $entryIds);
				}
				return $result;
		}
		
	}
	
	/**
	 * @action exportToCsv
	 * @param VidiunLiveReportExportType $reportType 
	 * @param VidiunLiveReportExportParams $params
	 * @return VidiunLiveReportExportResponse
	 */
	public function exportToCsvAction($reportType, VidiunLiveReportExportParams $params)
	{
		if(!$params->recpientEmail) {
			$vuser = vCurrentContext::getCurrentVsVuser();
			if($vuser) {
				$params->recpientEmail = $vuser->getEmail();
			} else {
				$partnerId = vCurrentContext::getCurrentPartnerId();
				$partner = PartnerPeer::retrieveByPK($partnerId);
				$params->recpientEmail = $partner->getAdminEmail();
			}
		}
		
		// Validate input
		if($params->entryIds) {
			$entryIds = explode(",", $params->entryIds);
			$entries = entryPeer::retrieveByPKs($entryIds);
			if(count($entryIds) != count($entries))
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $params->entryIds);
		}
		
		
		$dbBatchJob = vJobsManager::addExportLiveReportJob($reportType, $params);
		
		$res = new VidiunLiveReportExportResponse();
		$res->referenceJobId = $dbBatchJob->getId();
		$res->reportEmail = $params->recpientEmail;
		
		return $res;
	}
	
	/**
	 *
	 * Will serve a requested report
	 * @action serveReport
	 *
	 *
	 * @param string $id - the requested id
	 * @return string
	 */
	public function serveReportAction($id) {
		
		$fileNameRegex = "/^(?<dc>[01]+)_(?<fileName>\\d+_Export_[a-zA-Z0-9]+_[\\w\\-]+.csv)$/";
	
		// VS verification - we accept either admin session or download privilege of the file
		$vs = $this->getVs();
		if(!$vs || !($vs->isAdmin() || $vs->verifyPrivileges(vs::PRIVILEGE_DOWNLOAD, $id)))
			VExternalErrors::dieError(VExternalErrors::ACCESS_CONTROL_RESTRICTED);
	
		if(!preg_match($fileNameRegex, $id, $matches)) {
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
		}
		
		// Check if the request should be handled by the other DC
		$curerntDc = vDataCenterMgr::getCurrentDcId();
		if($matches['dc'] == 1 - $curerntDc)
			vFileUtils::dumpApiRequest ( vDataCenterMgr::getRemoteDcExternalUrlByDcId ( 1 - $curerntDc ) );
		
		// Serve report
		$filePath = $this->getReportDirectory( $this->getPartnerId()) . DIRECTORY_SEPARATOR . $matches['fileName'];
		return $this->dumpFile($filePath, 'text/csv');
	}
	
	protected function getReportDirectory($partnerId) {
		$folderPath = "/content/reports/live/$partnerId";
		$directory =  myContentStorage::getFSContentRootPath() . $folderPath;
		if(!file_exists($directory))
			mkdir($directory);
		return $directory;
	}
	
	/**
	 * Returns all live entry ids that are live now by partner id 
	 */
	protected function getAllLiveEntriesLiveNow() {
		// Partner ID condition is embeded in the default criteria.
		$baseCriteria = VidiunCriteria::create(entryPeer::OM_CLASS);
		$filter = new entryFilter();
		$filter->setTypeEquel(VidiunEntryType::LIVE_STREAM);
		$filter->setIsLive(true);
		$filter->setPartnerSearchScope(baseObjectFilter::MATCH_VIDIUN_NETWORK_AND_PRIVATE);
		$filter->attachToCriteria($baseCriteria);
		
		$entries = entryPeer::doSelect($baseCriteria);
		$entryIds = array();
		foreach($entries as $entry)
			$entryIds[] = $entry->getId();
		
		return implode(",", $entryIds);
	}
	
	/**
	 * Returns all live entries that were live in the past X hours
	 */
	protected function getLiveEntries(WSLiveReportsClient $client, $partnerId, VidiunFilterPager $pager) {
		// Get live entries list
		/** @var WSLiveEntriesListResponse */
		$response = $client->getLiveEntries($partnerId);
		
		if($response->totalCount == 0)
			return null;
		
		// Hack to overcome the bug of single value
		$entryIds = $response->entries;
		if(!is_array($entryIds)) {
			$entryIds = array();
			$entryIds[] = $response->entries;
		}

		// Order entries by first broadcast
		$baseCriteria = VidiunCriteria::create(entryPeer::OM_CLASS);
		$filter = new entryFilter();
		$filter->setTypeEquel(VidiunEntryType::LIVE_STREAM);
		$filter->setIdIn($entryIds);
		$filter->setPartnerSearchScope(baseObjectFilter::MATCH_VIDIUN_NETWORK_AND_PRIVATE);
		$baseCriteria->addAscendingOrderByColumn(entryPeer::NAME);
		$filter->attachToCriteria($baseCriteria);
		$pager->attachToCriteria($baseCriteria);
		
		$entries = entryPeer::doSelect($baseCriteria);
		$entryIds = array();
		foreach($entries as $entry)
			$entryIds[] = $entry->getId();
		
		$totalCount = $baseCriteria->getRecordsCount();
		return array($entryIds, $totalCount);
	}
	
	protected function requestClient(WSLiveReportsClient $client, $reportType, $wsFilter, $wsPager) {
		/** @var WSLiveStatsListResponse */
		$result = $client->getReport($reportType, $wsFilter, $wsPager);
		$vResult = $result->toVidiunObject();
		return $vResult;
	}

	/**
	 * Sorts the objects array in the result object according to the order of entryIds provided
	 * @param $result
	 * @param $entryIds
	 */
	protected function sortResultByEntryIds($result, $entryIds)
	{
		$resultHash = array();
		foreach ($result->objects as $object) {
			$resultHash[$object->entryId] = $object;
		}

		$result->objects = array();
		foreach ($entryIds as $entryId) {
			if ($resultHash[$entryId]) {
				$result->objects[] = $resultHash[$entryId];
			}
		}
	}
}

