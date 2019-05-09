<?php


class WSLiveStatsListResponse extends WSBaseObject
{				
	function getVidiunObject() {
		return new VidiunLiveStatsListResponse();
	}
	
	protected function getAttributeType($attributeName)
	{
		switch($attributeName)
		{	
			case 'objects':
				return 'WSLiveStatsArray';
			default:
				return parent::getAttributeType($attributeName);
		}
	}
					
	/**
	 * @var array
	 **/
	public $objects;
	
	/**
	 * @var int
	 **/
	public $totalCount;
	
}


