<?php
/**
 * Base class for all periodic workers.
 * 
 * @package Scheduler
 */
abstract class VPeriodicWorker extends VBatchBase
{
	/**
	 * @return filter by object class name
	 */
	protected function getAdvancedFilter($clsName)
	{
		if(!VBatchBase::$taskConfig->advancedFilter)
			throw new Exception("Advanced filter undefined");
		
		if(!VBatchBase::$taskConfig->advancedFilter->$clsName)
			throw new Exception("Trying to get undefined advanced-filter for filter of type [$clsName]");
		
		$filter = new $clsName();
		
		foreach (VBatchBase::$taskConfig->advancedFilter->$clsName as $key => $value)
		{
			$filter->$key = $value;
		}
	
		return $filter;
	}
}
