<?php
/**
 * @package plugins.virusScan
 * @subpackage api.objects
 */
class VidiunVirusScanProfile extends VidiunObject implements IFilterable
{
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $id;

	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;

	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;

	/**
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $partnerId;

	/**
	 * @var string
	 * @filter eq,like
	 */
	public $name;

	/**
	 * @var VidiunVirusScanProfileStatus
	 * @filter eq,in
	 */
	public $status;

	/**
	 * @var VidiunVirusScanEngineType
	 * @filter eq,in
	 */
	public $engineType;

	/**
	 * @var VidiunBaseEntryFilter
	 */
	public $entryFilter;

	/**
	 * @var VidiunVirusFoundAction
	 */
	public $actionIfInfected;
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array(
		"id",
		"createdAt",
		"updatedAt",
		"partnerId",
		"name",
		"status",
		"engineType",
		"actionIfInfected",
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new VirusScanProfile();
			
		parent::toObject($dbObject, $skip);
		
		if($this->entryFilter)
		{
			$entryFilter = new entryFilter();
			$this->entryFilter->toObject($entryFilter);
			$dbObject->setEntryFilterObject($entryFilter);
		}
			
		return $dbObject;
	}
	
	public function doFromObject($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		if(!$sourceObject)
			return;
			
		parent::doFromObject($sourceObject, $responseProfile);
		
		if($this->shouldGet('entryFilter', $responseProfile))
		{
			$entryFilter = $sourceObject->getEntryFilterObject();
			if($entryFilter)
			{
				$this->entryFilter = new VidiunBaseEntryFilter();
				$this->entryFilter->fromObject($entryFilter);
			}
		}
	}
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
}