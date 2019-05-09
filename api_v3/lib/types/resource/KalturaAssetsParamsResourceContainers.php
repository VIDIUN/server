<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAssetsParamsResourceContainers extends VidiunResource 
{
	/**
	 * Array of resources associated with asset params ids
	 * @var VidiunAssetParamsResourceContainerArray
	 */
	public $resources;

	public function validateEntry(entry $dbEntry,$validateLocalExist = false)
	{
		parent::validateEntry($dbEntry,$validateLocalExist);
    	$this->validatePropertyNotNull('resources');
    	
		$dc = null;
    	foreach($this->resources as $resource)
    	{
    		$resource->validateEntry($dbEntry,$validateLocalExist);
    	
    		if(!($resource instanceof VidiunDataCenterContentResource))
    			continue;
    			
    		$theDc = $resource->getDc();
    		if(is_null($theDc))
    			continue;
    			
    		if(is_null($dc))
    		{
    			$dc = $theDc;
    		}
    		elseif($dc != $theDc)
    		{
				throw new VidiunAPIException(VidiunErrors::RESOURCES_MULTIPLE_DATA_CENTERS);
    		}
    	}
    	
    	if(!is_null($dc) && $dc != vDataCenterMgr::getCurrentDcId())
    	{
    		$remoteHost = vDataCenterMgr::getRemoteDcExternalUrlByDcId($dc);
    		vFileUtils::dumpApiRequest($remoteHost);
    	}
	}
	
	public function entryHandled(entry $dbEntry)
	{
		parent::entryHandled($dbEntry);
		
    	foreach($this->resources as $resource)
    		$resource->entryHandled($dbEntry);
	}

	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new vAssetsParamsResourceContainers();
			
		$resources = array();
		foreach($this->resources as $resource)
			$resources[] = $resource->toObject();
			
		$object_to_fill->setResources($resources);
		return $object_to_fill;
	}
}
