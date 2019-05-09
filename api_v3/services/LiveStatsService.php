<?php

/**
 * Stats Service
 *
 * @service liveStats
 * @package api
 * @subpackage services
 */
class LiveStatsService extends VidiunBaseService 
{
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'collect') {
			return false;
		}
		
		return parent::partnerRequired($actionName);
	}
	
	
	/**
	 * Will write to the event log a single line representing the event
	 * 
	 * 
 	* 
 
	 * VidiunStatsEvent $event
	 * 
	 * @action collect
	 * @return bool
	 */
	function collectAction( VidiunLiveStatsEvent $event )
	{
		return true;
	}

	
	
}
