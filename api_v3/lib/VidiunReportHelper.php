<?php
/**
 * @package api
 * @subpackage v3
 */
class VidiunReportHelper
{
	public static function getValidateExecutionParameters(Report $report, VidiunKeyValueArray $params = null)
	{
		if (is_null($params))
			$params = new VidiunKeyValueArray();
			
		$execParams = array();
		$currentParams = $report->getParameters();
		foreach($currentParams as $currentParam)
		{
			$found = false;
			foreach($params as $param)
			{
				/* @var $param VidiunKeyValue */
				if ((strtolower($param->key) == strtolower($currentParam)))
				{
					$execParams[':'.$currentParam] = $param->value;
					$found = true;
				}
			}
			
			if (!$found)
				throw new VidiunAPIException(VidiunErrors::REPORT_PARAMETER_MISSING, $currentParam);
		}
		return $execParams;
	}
}
