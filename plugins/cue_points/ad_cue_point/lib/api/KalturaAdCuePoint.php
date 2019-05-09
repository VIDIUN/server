<?php
/**
 * @package plugins.adCuePoint
 * @subpackage api.objects
 */
class VidiunAdCuePoint extends VidiunCuePoint
{
	/**
	 * @var VidiunAdProtocolType
	 * @filter eq,in
	 * @insertonly
	 * @requiresPermission insert,update
	 */
	public $protocolType;
	
	/**
	 * @var string
	 * @requiresPermission insert,update
	 */
	public $sourceUrl;
	
	/**
	 * @var VidiunAdType 
	 * @requiresPermission insert,update
	 */
	public $adType;
	
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 * @requiresPermission insert,update
	 */
	public $title;
	
	/**
	 * @var int 
	 * @filter gte,lte,order
	 * @requiresPermission insert,update
	 */
	public $endTime;
	
	/**
	 * Duration in milliseconds
	 * @var int 
	 * @filter gte,lte,order
	 */
	public $duration;

	public function __construct()
	{
		$this->cuePointType = AdCuePointPlugin::getApiValue(AdCuePointType::AD);
	}
	
	private static $map_between_objects = array
	(
		"protocolType" => "subType",
		"sourceUrl",
		"adType",
		"title" => "name",
		"endTime",
		"duration",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if(is_null($object_to_fill))
			$object_to_fill = new AdCuePoint();
			
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		parent::validateForInsert($propertiesToSkip);
		
		$this->validateEndTime();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$this->validateEndTime($sourceObject);
			
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
}
