<?php
/**
 * api for getting analytics data
 * @service analytics
 * @package api
 * @subpackage services
 */
class AnalyticsService extends VidiunBaseService
{

	const PARTNER_DIMENSION = "partner";

	/**
	 * report query action allows to get a analytics data for specific query dimensions, metrics and filters.
	 *
	 * @action query
	 * @param VidiunAnalyticsFilter $filter the analytics query filter
         * @param VidiunFilterPager $pager the analytics query result pager
	 * @return VidiunReportResponse
	 */
	public function queryAction($filter, VidiunFilterPager $pager = null)
	{
                vApiCache::disableConditionalCache();
		$filter->validateForUsage($filter);
		
		$dimensionsArr = $this->extractDimensions($filter->dimensions);
		VidiunLog::debug('Extracted dimensions: ' . var_export($dimensionsArr, true));
		$metricsArr = $this->extractMetrics($filter->metrics);
		VidiunLog::debug('Extracted metrics: ' . var_export($metricsArr, true));
		$filtersArr = $this->extractFilters($filter->filters);
		VidiunLog::debug('Extracted filters: ' . var_export($filtersArr, true));
                $pagerArr = $this->extractPager($pager);
		$internalApiRequest = $this->constructInternalRequest($filter->from_time, $filter->to_time, $dimensionsArr, $metricsArr, $filtersArr, $filter->utcOffset, $filter->orderBy, $pagerArr);
		VidiunLog::info('Constructed request: ' . var_export($internalApiRequest, true));

		$internalApiServer = vConf::get('analytics_internal_API_url');
		VidiunLog::debug('Querying against: ' . var_export($internalApiServer, true));

		$apiCallResponse = $this->callAPI("POST", $internalApiServer, $internalApiRequest);
		VidiunLog::info('API call response: ' . var_export($apiCallResponse, true));

		$jsonResponse = json_decode($apiCallResponse);
		VidiunLog::debug('Response as json: ' . var_export($jsonResponse, true));

		$res = new VidiunReportResponse();
		$res->columns = implode(",", $jsonResponse->headers);
		$tempResult = array_map(array($this, 'implodeWithComma'), $jsonResponse->data);
		$res->results = array_map(array($this, 'createVidiunString'), $tempResult);

		VidiunLog::info('Response: ' . var_export($res, true));

		return $res;
	}

	private function createVidiunString($str)
	{
		$res = new VidiunString();
		$res->value = $str;
		return $res;
	}

	private function extractFilters($filters)
	{
		$res = array();
		if ($filters != null)
		{
			$res = array_map(array($this, 'extractFilter'), $filters->toArray());
		}

		// Add a filter for the current partner
		$partnerFilter = array();
		$partnerFilter['dimension'] = self::PARTNER_DIMENSION;
		$partnerFilter['values'] = array($this->getPartnerId());

		$res[] = $partnerFilter;
		return $res;
	}

	private function extractFilter($filter)
	{
		if (strtolower($filter->dimension) == self::PARTNER_DIMENSION)
		{
			throw new APIException(VidiunErrors::ANALYTICS_FORBIDDEN_FILTER);
		}

		VidiunLog::debug('Extracting filter: ' . var_export($filter, true));

		$res = array();
		$res['dimension'] = $filter->dimension;
		$res['values'] = $this->explodeAndTrim($filter->values);

		VidiunLog::debug('Extracted filter: ' . var_export($res, true));

		return $res;
	}

        private function extractPager($pager)
	{
		VidiunLog::debug('Extracting pager: ' . var_export($pager, true));
		
		$res = array();
		if(!$pager)
		{
			$pager = new VidiunFilterPager();
		}

                $res['size'] = $pager->pageSize;
                $res['index'] = $pager->pageIndex;

		VidiunLog::debug('Extracted pager: ' . var_export($res, true));

                return $res;
	}

	private function implodeWithComma($arr)
	{
		return implode(",", $arr);
	}

	private function extractMetrics($metrics)
	{
		return $this->explodeAndTrim($metrics);
	}

	private function extractDimensions($dimensions)
	{
		if ($dimensions == null)
		{
			return array();
		}

		return $this->explodeAndTrim($dimensions);
	}

	private function explodeAndTrim($arr)
	{
		return array_map('trim', explode(",",$arr));
	}

	private function constructInternalRequest($from, $to, $dimensionsArr, $metricsArr, $filtersArr, $utcOffset, $orderBy, $pager)
	{
		$data = array("from" => $from, "to" => $to, "dimensions" => $dimensionsArr, "filters" => $filtersArr, "metrics" => $metricsArr, "utcOffset" => $utcOffset, "orderBy" => $orderBy, "pager" => $pager);
		//e.g. {"from":"1","to":"2","dimensions":["partner"], "filters":[{"dimension":"partner","values":["1"]}], "metrics":["play"], "utcOffset":"240", "orderBy":"+play", "pager":{"size": 100, "index":1}}
		return json_encode($data);
	}

	private function callAPI($method, $url, $data = false)
	{
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json'
		));

		switch ($method)
		{
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);
				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, 1);
				break;
			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		if (!$result)
		{
			VidiunLog::err('Error querying internal API server: ' . curl_error($curl));
			throw new APIException(VidiunErrors::ANALYTICS_QUERY_FAILURE);
		}

		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($code >= 400)
		{
			VidiunLog::info('Erroneous response from internal API server: ' . $result);
			$errorData = json_decode($result);
			if (!$errorData)
			{
				throw new APIException(VidiunErrors::ANALYTICS_QUERY_FAILURE);
			}
			else
			{
				switch ($errorData->kind) {
					case "incorrectContentType":
						throw new APIException(VidiunErrors::ANALYTICS_INCORRECT_INPUT_TYPE);
					case "invalidInput":
						throw new APIException(VidiunErrors::ANALYTICS_INCORRECT_INPUT, $errorData->data);
					case "generalError":
						throw new APIException(VidiunErrors::ANALYTICS_QUERY_FAILURE);
					case "unsupportedDimension":
						throw new APIException(VidiunErrors::ANALYTICS_UNSUPPORTED_DIMENSION, $errorData->data);
					case "unsupportedQuery":
						throw new APIException(VidiunErrors::ANALYTICS_UNSUPPORTED_QUERY);
				}
			}
		}

		curl_close($curl);

		return $result;
	}
}
