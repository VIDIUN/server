<?php
/**
 * api for getting reports data by the report type and some inputFilter
 * @service report
 * @package api
 * @subpackage services
 */
class ReportService extends VidiunBaseService
{
	protected static $crossPartnerReports = array(
		ReportType::PARTNER_USAGE,
		ReportType::VAR_USAGE,
		ReportType::VPAAS_USAGE_MULTI,
	);

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if (in_array(strtolower($actionName), array('execute', 'getcsv'), true))
		{
			$this->applyPartnerFilterForClass('Report');
		}
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerGroup()
	 */
	protected function partnerGroup($peer = null)
	{
		if (in_array(strtolower($this->actionName), array('execute', 'getcsv'), true))
			return $this->partnerGroup . ',0';
			
		return $this->partnerGroup;
	}
		
	/**
	 * Validates that all object ids are allowed partner ids
	 * 
	 * @param string $objectIds comma separated IDs
	 * @return string comma seperated ids
	 */
	protected function validateObjectsAreAllowedPartners($reportType, $objectIds, $delimiter)
	{
		if(!$objectIds && $reportType != ReportType::VPAAS_USAGE_MULTI)
		{
			return $this->getPartnerId();
		}
			
		$c = new Criteria();
		$c->addSelectColumn(PartnerPeer::ID);
		$subCriterion1 = $c->getNewCriterion(PartnerPeer::PARTNER_PARENT_ID, $this->getPartnerId());
		$subCriterion2 = $c->getNewCriterion(PartnerPeer::ID, $this->getPartnerId());
		$subCriterion1->addOr($subCriterion2);
		$c->add($subCriterion1);
		if ($objectIds)
		{
			$c->add(PartnerPeer::ID, explode($delimiter, $objectIds), Criteria::IN);
		}
		
		$stmt = PartnerPeer::doSelectStmt($c);
		$partnerIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
		if (!$partnerIds)
			return Partner::PARTNER_THAT_DOWS_NOT_EXIST;

		return implode($delimiter, $partnerIds);
	}
		
	/**
	 * report getGraphs action allows to get a graph data for a specific report. 
	 * 
	 * @action getGraphs
	 * @param VidiunReportType $reportType  
	 * @param VidiunReportInputFilter $reportInputFilter
	 * @param string $dimension
	 * @param string $objectIds - one ID or more (separated by ',') of specific objects to query
	 * @return VidiunReportGraphArray 
	 */
	public function getGraphsAction( $reportType , VidiunReportInputFilter $reportInputFilter , $dimension = null , $objectIds = null, VidiunReportResponseOptions $responseOptions = null  )
	{
		if (!$responseOptions)
		{
			$responseOptions = new VidiunReportResponseOptions();
		}
		$vResponseOptions = $responseOptions->toObject();

		if(in_array($reportType, self::$crossPartnerReports))
			$objectIds = $this->validateObjectsAreAllowedPartners($reportType, $objectIds, $vResponseOptions->getDelimiter());
	
		$reportGraphs =  VidiunReportGraphArray::fromReportDataArray(vKavaReportsMgr::getGraph(
		    $this->getPartnerId(),
		    $reportType,
		    $reportInputFilter->toReportsInputFilter(),
		    $dimension,
		    $objectIds,
			$vResponseOptions),
			$vResponseOptions->getDelimiter());

		return $reportGraphs;
	}

	/**
	 * report getTotal action allows to get a graph data for a specific report. 
	 * 
	 * @action getTotal
	 * @param VidiunReportType $reportType  
	 * @param VidiunReportInputFilter $reportInputFilter
	 * @param string $objectIds - one ID or more (separated by ',') of specific objects to query
	 * @return VidiunReportTotal 
	 */
	public function getTotalAction( $reportType , VidiunReportInputFilter $reportInputFilter , $objectIds = null, VidiunReportResponseOptions $responseOptions = null)
	{
		if (!$responseOptions)
		{
			$responseOptions = new VidiunReportResponseOptions();
		}
		$vResponseOptions = $responseOptions->toObject();

		if(in_array($reportType, self::$crossPartnerReports))
			$objectIds = $this->validateObjectsAreAllowedPartners($reportType, $objectIds, $vResponseOptions->getDelimiter());

		$reportTotal = new VidiunReportTotal();
		
		list ( $header , $data ) = vKavaReportsMgr::getTotal(
		    $this->getPartnerId() ,
		    $reportType ,
		    $reportInputFilter->toReportsInputFilter() , $objectIds, $vResponseOptions);
		
		$reportTotal->fromReportTotal ( $header , $data, $vResponseOptions->getDelimiter() );
			
		return $reportTotal;
	}
	
	/**
	 * report getBaseTotal action allows to get the total base for storage reports  
	 * 
	 * @action getBaseTotal
	 * @param VidiunReportType $reportType  
	 * @param VidiunReportInputFilter $reportInputFilter
	 * @param string $objectIds - one ID or more (separated by ',') of specific objects to query
	 * @return VidiunReportBaseTotalArray 
	 */
	public function getBaseTotalAction( $reportType , VidiunReportInputFilter $reportInputFilter , $objectIds = null , VidiunReportResponseOptions $responseOptions = null)
	{
		if (!$responseOptions)
		{
			$responseOptions = new VidiunReportResponseOptions();
		}

		$reportSubTotals =  VidiunReportBaseTotalArray::fromReportDataArray(  
			vKavaReportsMgr::getBaseTotal( 
				$this->getPartnerId() , 
				$reportType , 
				$reportInputFilter->toReportsInputFilter() ,
				$objectIds,
				$responseOptions->toObject()));

		return $reportSubTotals;
	}
	
	/**
	 * report getTable action allows to get a graph data for a specific report. 
	 * 
	 * @action getTable
	 * @param VidiunReportType $reportType  
	 * @param VidiunReportInputFilter $reportInputFilter
	 * @param VidiunFilterPager $pager
	 * @param VidiunReportType $reportType 
	 * @param string $order
	 * @param string $objectIds - one ID or more (separated by ',') of specific objects to query
	 * @return VidiunReportTable 
	 */
	public function getTableAction($reportType, VidiunReportInputFilter $reportInputFilter, VidiunFilterPager $pager, $order = null, $objectIds = null, VidiunReportResponseOptions $responseOptions = null)
	{
		if (!$responseOptions)
		{
			$responseOptions = new VidiunReportResponseOptions();
		}
		$vResponseOptions = $responseOptions->toObject();

		$isCsv = false;
		if (vCurrentContext::$vs_partner_id == Partner::BATCH_PARTNER_ID)
		{
			$isCsv = true;
		}

		if(in_array($reportType, self::$crossPartnerReports))
			$objectIds = $this->validateObjectsAreAllowedPartners($reportType, $objectIds, $vResponseOptions->getDelimiter());

		$reportTable = new VidiunReportTable();

		// Temporary hack to allow admin console to request a report for any partner
		//	can remove once moving to Kava
		$partnerId = $this->getPartnerId();
		if ($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID && $objectIds && ctype_digit($objectIds))
		{
			$partnerReports = array(
				VidiunReportType::VAR_USAGE,
				VidiunReportType::VPAAS_USAGE,
				VidiunReportType::ENTRY_USAGE,
				VidiunReportType::PARTNER_USAGE,
			);

			if (in_array($reportType, $partnerReports))
			{
				$partnerId = $objectIds;
			}
		}
		
		list ( $header , $data , $totalCount ) = vKavaReportsMgr::getTable(
		    $partnerId ,
		    $reportType ,
		    $reportInputFilter->toReportsInputFilter() ,
		    $pager->pageSize , $pager->pageIndex ,
		    $order , $objectIds, null , $isCsv , $vResponseOptions);

		$reportTable->fromReportTable ( $header , $data , $totalCount, $vResponseOptions->getDelimiter() );
			
		return $reportTable;
	}	
	
	/**
	 * 
	 * will create a CSV file for the given report and return the URL to access it
	 * @action getUrlForReportAsCsv
	 * 
	 * @param string $reportTitle The title of the report to display at top of CSV 
	 * @param string $reportText The text of the filter of the report
	 * @param string $headers The headers of the columns - a map between the enumerations on the server side and the their display text  
	 * @param VidiunReportType $reportType  
	 * @param VidiunReportInputFilter $reportInputFilter
	 * @param string $dimension	  
	 * @param VidiunFilterPager $pager
	 * @param VidiunReportType $reportType 
	 * @param string $order
	 * @param string $objectIds - one ID or more (separated by ',') of specific objects to query
	 * @return string 
	 */
	public function getUrlForReportAsCsvAction ( $reportTitle , $reportText , $headers , $reportType , VidiunReportInputFilter $reportInputFilter , 
		$dimension = null , 
		VidiunFilterPager $pager = null , 
		$order = null , $objectIds = null,
		VidiunReportResponseOptions $responseOptions = null)
	{
		ini_set( "memory_limit","512M" );
		
		if(!$pager)
			$pager = new VidiunFilterPager();

		if (!$responseOptions)
		{
			$responseOptions = new VidiunReportResponseOptions();
		}
		$vResponseOptions = $responseOptions->toObject();

		if(in_array($reportType, self::$crossPartnerReports))
			$objectIds = $this->validateObjectsAreAllowedPartners($reportType, $objectIds, $vResponseOptions->getDelimiter());

		try {
			$report = vKavaReportsMgr::getUrlForReportAsCsv(
				$this->getPartnerId(),
				$reportTitle,
				$reportText,
				$headers,
				$reportType,
				$reportInputFilter->toReportsInputFilter(),
				$dimension,
				$objectIds,
				$pager->pageSize,
				$pager->pageIndex,
				$order,
				$vResponseOptions);
		}
		catch(Exception $e){
			$code = $e->getCode();
			if ($code == vCoreException::SEARCH_TOO_GENERAL)
					throw new VidiunAPIException(VidiunErrors::SEARCH_TOO_GENERAL);
		}

		if ((infraRequestUtils::getProtocol() == infraRequestUtils::PROTOCOL_HTTPS))
			$report = str_replace("http://","https://",$report);

		return $report;
	}
	
	/**
	 *
	 * Will serve a requested report
	 * @action serve
	 * 
	 * @param string $id - the requested id
	 * @return string
	 * @vsOptional 
	 */
	public function serveAction($id) {
		// VS verification - we accept either admin session or download privilege of the file
		$vs = $this->getVs();
		if(!$vs || !($vs->isAdmin() || $vs->verifyPrivileges(vs::PRIVILEGE_DOWNLOAD, $id)))
			VExternalErrors::dieError(VExternalErrors::ACCESS_CONTROL_RESTRICTED);

		if(!preg_match('/^[\w-_]*$/', $id))
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);

		$partner_id = $this->getPartnerId();
		$folderPath = "/content/reports/$partner_id";
		$fullPath = myContentStorage::getFSContentRootPath() . $folderPath;
		$file_path = "$fullPath/$id";

		return $this->dumpFile($file_path, 'text/csv');
	}
	
	/**
	 * @action execute
	 * @param int $id
	 * @param VidiunKeyValueArray $params
	 * @return VidiunReportResponse
	 */
	public function executeAction($id, VidiunKeyValueArray $params = null)
	{
		$dbReport = ReportPeer::retrieveByPK($id);
		if (is_null($dbReport))
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
			
		$this->addPartnerIdToParams($params);
		
		$execParams = VidiunReportHelper::getValidateExecutionParameters($dbReport, $params);
		
		$vReportsManager = new vReportManager($dbReport);
		list($columns, $rows) = $vReportsManager->execute($execParams);
		
		$reportResponse = VidiunReportResponse::fromColumnsAndRows($columns, $rows);
		
		return $reportResponse;
	}
	
	/**
	 * @action getCsv
	 * @param int $id
	 * @param VidiunKeyValueArray $params
	 * @return file
	 */
	public function getCsvAction($id, VidiunKeyValueArray $params = null)
	{
		$this->addPartnerIdToParams($params);
		
		ini_set( "memory_limit","512M" );
		
		if (vKavaBase::isPartnerAllowed($this->getPartnerId(), vKavaBase::VOD_DISABLED_PARTNERS))
		{
			$customReports = vConf::getMap('custom_reports');
			if (!isset($customReports[$id]))
				throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
			
			list($columns, $rows) = vKavaReportsMgr::customReport($id, $params->toObjectsArray());
		}
		else 
		{
			$dbReport = ReportPeer::retrieveByPK($id);
			if (is_null($dbReport))
				throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
				
			$execParams = VidiunReportHelper::getValidateExecutionParameters($dbReport, $params);
			
			$vReportsManager = new vReportManager($dbReport);
			list($columns, $rows) = $vReportsManager->execute($execParams);
		}
		
		$fileName = array('Report', $id, $this->getPartnerId());
		foreach($params as $param)
		{
			$fileName[] = $param->key;
			$fileName[] = $param->value;
		}
		$fileName = implode('_', $fileName) . '.csv';
		header('Content-Type: text/csv');
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		echo "\xEF\xBB\xBF"; // a fix for excel, copied from myReportsMgr
		echo implode(',', $columns) . "\n";
		foreach($rows as $row) 
		{
			$row = str_replace(',', ' ', $row);
			echo implode(',', $row) . "\n";
		}
		die;
	}
	
	/**
	 * Returns report CSV file executed by string params with the following convention: param1=value1;param2=value2 
	 * 
	 * @action getCsvFromStringParams
	 * @param int $id
	 * @param string $params
	 * @return file
	 */
	public function getCsvFromStringParamsAction($id, $params = null)
	{
		$paramsArray = $this->parseParamsStr($params);
		return $this->getCsvAction($id, $paramsArray);
	}

	/**
	 * @action exportToCsv
	 * @param VidiunReportExportParams $params
	 * @return VidiunReportExportResponse
	 * @throws VidiunAPIException
	 */
	public function exportToCsvAction(VidiunReportExportParams $params)
	{
		$this->validateReportExportParams($params);

		if (!$params->recipientEmail)
		{
			$vuser = vCurrentContext::getCurrentVsVuser();
			if ($vuser)
			{
				$params->recipientEmail = $vuser->getEmail();
			}
			else
			{
				$partnerId = vCurrentContext::getCurrentPartnerId();
				$partner = PartnerPeer::retrieveByPK($partnerId);
				$params->recipientEmail = $partner->getAdminEmail();
			}
		}

		$dbBatchJob = vJobsManager::addExportReportJob($params);

		$response = new VidiunReportExportResponse();
		$response->referenceJobId = $dbBatchJob->getId();
		$response->reportEmail = $params->recipientEmail;

		return $response;
	}

	protected function validateReportExportParams(VidiunReportExportParams $params)
	{
		if (!$params->reportItems)
		{
			throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER);
		}
		foreach ($params->reportItems as $reportItem)
		{
			/**
			 * @var VidiunReportExportItem $reportItem
			 */
			if (!$reportItem->action || !$reportItem->reportType || !$reportItem->filter)
			{
				throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER);
			}
		}
	}

	protected function parseParamsStr($paramsStr)
	{
		$paramsStrArray = explode(';', $paramsStr);
		$paramsKeyValueArray = new VidiunKeyValueArray();
		foreach($paramsStrArray as $paramStr)
		{
			$paramStr = trim($paramStr);
			$paramArray = explode('=', $paramStr);
			$paramKeyValue = new VidiunKeyValue();
			$paramKeyValue->key = isset($paramArray[0]) ? $paramArray[0] : null;
			$paramKeyValue->value = isset($paramArray[1]) ? $paramArray[1] : null;
			$paramsKeyValueArray[] = $paramKeyValue;
		}
		return $paramsKeyValueArray;
	}
	
	protected function addPartnerIdToParams($params)
	{
		// remove partner id parameter
		foreach($params as $param)
		{
			if (strtolower($param->key) == 'partner_id')
			{
				$param->key = '';
				$param->value = '';
			}
		}
		// force partner id parameter
		$partnerIdParam = new VidiunKeyValue();
		$partnerIdParam->key = 'partner_id';
		$partnerIdParam->value = $this->getPartnerId();
		$params[] = $partnerIdParam;
	}
}
