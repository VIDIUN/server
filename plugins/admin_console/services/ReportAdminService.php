<?php
/**
 * @service reportAdmin
 * @package plugins.adminConsole
 * @subpackage api.services
 */
class ReportAdminService extends VidiunBaseService
{
    /* (non-PHPdoc)
     * @see VidiunBaseService::initService()
     */
    public function initService($serviceId, $serviceName, $actionName)
    {
        parent::initService($serviceId, $serviceName, $actionName);

		if(!AdminConsolePlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, AdminConsolePlugin::PLUGIN_NAME);
	}
    
	/**
	 * @action add
	 * @param VidiunReport $report
	 * @return VidiunReport
	 */
	function addAction(VidiunReport $report)
	{
		$dbReport = new Report();
		$report->toInsertableObject($dbReport);
		$dbReport->save();
		
		$report->fromObject($dbReport, $this->getResponseProfile());
		return $report;
	}
	
	/**
	 * @action get
	 * @param int $id
	 * @return VidiunReport
	 */
	function getAction($id)
	{
		$dbReport = ReportPeer::retrieveByPK($id);
		if (is_null($dbReport))
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
			
		$report = new VidiunReport();
		$report->fromObject($dbReport, $this->getResponseProfile());
		return $report;
	}
	
	/**
	 * @action list
	 * @param VidiunReportFilter $filter
	 * @param VidiunReport $report
	 * @return VidiunReportListResponse
	 */
	function listAction(VidiunReportFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunReportFilter();
			
		if (!$pager)
			$pager = new VidiunFilterPager();
			
		$reportFilter = new ReportFilter();
		
		$filter->toObject($reportFilter);
		$c = new Criteria();
		$reportFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		
		$dbList = ReportPeer::doSelect($c);
		$c->setLimit(null);
		$totalCount = ReportPeer::doCount($c);

		$list = VidiunReportArray::fromDbArray($dbList, $this->getResponseProfile());
		$response = new VidiunReportListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;    
	}
	
	/**
	 * @action update
	 * @param int $id
	 * @param VidiunReport $report
	 * @return VidiunReport
	 */
	function updateAction($id, VidiunReport $report)
	{
		$dbReport = ReportPeer::retrieveByPK($id);
		if (is_null($dbReport))
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
			
		$report->toUpdatableObject($dbReport);
		$dbReport->save();
		
		$report->fromObject($dbReport, $this->getResponseProfile());
		return $report;
	}
	
	/**
	 * @param int $id
	 * @action delete
	 */
	function deleteAction($id)
	{
		$dbReport = ReportPeer::retrieveByPK($id);
		if (is_null($dbReport))
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
			
		$dbReport->setDeletedAt(time());
		$dbReport->save();
	}
	
	/**
	 * @action executeDebug
	 * @param int $id
	 * @param VidiunKeyValueArray $params
	 * @return VidiunReportResponse
	 */
	function executeDebugAction($id, VidiunKeyValueArray $params = null)
	{
		$dbReport = ReportPeer::retrieveByPK($id);
		if (is_null($dbReport))
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
			
		$query = $dbReport->getQuery();
		$matches = null;
		$execParams = VidiunReportHelper::getValidateExecutionParameters($dbReport, $params);
		
		try 
		{
			$vReportsManager = new vReportManager($dbReport);
			list($columns, $rows) = $vReportsManager->execute($execParams);
		}
		catch(Exception $ex)
		{
			VidiunLog::err($ex);
			throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR_DEBUG, $ex->getMessage());
		}
		
		$reportResponse = VidiunReportResponse::fromColumnsAndRows($columns, $rows);
		
		return $reportResponse;
	}
	
	/**
	 * @action getParameters
	 * @param int $id
	 * @return VidiunStringArray
	 */
	function getParametersAction($id)
	{
		$dbReport = ReportPeer::retrieveByPK($id);
		if (is_null($dbReport))
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);
			
		return VidiunStringArray::fromStringArray($dbReport->getParameters());
	}
	
	/**
	 * @action getCsvUrl
	 * @param int $id
	 * @param int $reportPartnerId
	 * @return string
	 */
	function getCsvUrlAction($id, $reportPartnerId)
	{
		$dbReport = ReportPeer::retrieveByPK($id);
		if (is_null($dbReport))
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_FOUND, $id);

		$dbPartner = PartnerPeer::retrieveByPK($reportPartnerId);
		if (is_null($dbPartner))
			throw new VidiunAPIException(VidiunErrors::INVALID_PARTNER_ID, $reportPartnerId);

		// allow creating urls for reports that are associated with partner 0 and the report owner
		if ($dbReport->getPartnerId() !== 0 && $dbReport->getPartnerId() !== $reportPartnerId) 
			throw new VidiunAPIException(VidiunErrors::REPORT_NOT_PUBLIC, $id); 
		
		$vs = new vs();
		$vs->valid_until = time() + 2 * 365 * 24 * 60 * 60; // 2 years 
		$vs->type = vs::TYPE_VS;
		$vs->partner_id = $reportPartnerId;
		$vs->master_partner_id = null;
		$vs->partner_pattern = $reportPartnerId;
		$vs->error = 0;
		$vs->rand = microtime(true);
		$vs->user = '';
		$vs->privileges = 'setrole:REPORT_VIEWER_ROLE';
		$vs->additional_data = null;
		$vs_str = $vs->toSecureString();
		
		$paramsArray = $this->getParametersAction($id);
		$paramsStrArray = array();
		foreach($paramsArray as $param)
		{
			$paramsStrArray[] = ($param->value.'={'.$param->value.'}');
		}

		$url = "http://" . vConf::get("www_host") . "/api_v3/index.php/service/report/action/getCsvFromStringParams/id/{$id}/vs/" . $vs_str . "/params/" . implode(';', $paramsStrArray);
		return $url;
	}
}