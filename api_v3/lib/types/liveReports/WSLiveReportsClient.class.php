<?php

class WSLiveReportsClient extends nusoap_client
{
	const PARAM_TYPE_TIMESTAMP = 'xsd:dateTime';
	
	function __construct()
	{
		$wsdlUrl = vConf::get('live_analytics_web_service_url');
		parent::__construct($wsdlUrl, 'wsdl');
		$this->keepType(true);
	}
	
	/**
	 * @param VidiunliveReportType $reportType
	 * @param VidiunliveReportInputFilter $filter
	 * @return VidiunLiveStatsListResponse 
	 **/
	public function getReport($reportType, WSLiveReportInputFilter $filter, WSLiveReportInputPager $pager)
	{
		$params = array();
		
		$params["reportType"] = $this->parseParam($reportType, 'tns:liveReportType');
		$params["filter"] = $this->parseParam($filter, 'tns:liveReportInputFilter');
		$params["pager"] = $this->parseParam($pager, 'tns:liveReportInputPager');
		

		return $this->doCall("getReport", $params, 'WSLiveStatsListResponse');
	}
	
	/**
	 * @param VidiunliveReportType $reportType
	 * @param VidiunliveReportInputFilter $filter
	 * @return VidiunLiveStatsListResponse
	 **/
	public function getEvents($reportType, WSLiveReportInputFilter $filter, WSLiveReportInputPager $pager)
	{
		$params = array();
	
		$params["reportType"] = $this->parseParam($reportType, 'tns:liveReportType');
		$params["filter"] = $this->parseParam($filter, 'tns:liveReportInputFilter');
		$params["pager"] = $this->parseParam($pager, 'tns:liveReportInputPager');
	
		return $this->doCall("getEvents", $params, 'WSLiveEventsListResponse');
	}
	
	/**
	 * Returns all entries that are considered live.
	 * In the future when the WS enables, we'd like to add a hours-before parameter
	 * @param int $partnerId
	 */
	public function getLiveEntries($partnerId)
	{
		$params = array();
		
		$params["partnerId"] = $this->parseParam($partnerId, 'xsd:int');

		return $this->doCall("getLiveEntries", $params, 'WSLiveEntriesListResponse');
	}
	
	protected function parseParam($value, $type = null)
	{
		if($type == self::PARAM_TYPE_TIMESTAMP)
		{
			if(is_null($value))
				return null;
	
			return timestamp_to_iso8601($value);
		}
			
		if(is_null($value))
			return 'Null';
			
		return $value;
	}
	
	protected function doCall($operation, array $params = array(), $type = null)
	{
		vApiCache::disableConditionalCache();
		$namespace = 'http://tempuri.org';
		$soapAction = '';
		$headers = array();
		$headers["VIDIUN_SESSION_ID"] = (string)(new UniqueId());
		$this->setDebugLevel(0);
		
		$result = $this->call($operation, $params, $namespace, $soapAction, $headers);
		$this->throwError($result);
	
		if($type)
			return new $type($result);
			
		return $result;
	}
	
	protected function throwError(array $result)
	{
		if ($this->getError()) {
			VidiunLog::err("VidiunClient error calling operation: [".$this->operation."], error: [".$this->getError()."], request: [".$this->request."], response: [".$this->response."]");
			if(array_key_exists("detail", $result) && is_array($result["detail"])) {
				$exceptionArr = $result["detail"];
				foreach($exceptionArr as $key => $value) {
					if($key == "AnalyticsException") {
						$ex = new WSAnalyticsException($value);
						VidiunLog::err("Vidiun client failed with the following message : " . $ex->message);
					}
				}
			}
			throw new VidiunAPIException(VidiunErrors::LIVE_REPORTS_WS_FAILURE);
		}
	}
}		
	
