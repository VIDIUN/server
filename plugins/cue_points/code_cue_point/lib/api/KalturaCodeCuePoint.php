<?php
/**
 * @package plugins.codeCuePoint
 * @subpackage api.objects
 */
class VidiunCodeCuePoint extends VidiunCuePoint
{
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand,eq,in
	 */
	public $code;
	
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $description;
	
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
	 * @readonly
	 */
	public $duration;

	public function __construct()
	{
		$this->cuePointType = CodeCuePointPlugin::getApiValue(CodeCuePointType::CODE);
	}
	
	private static $map_between_objects = array
	(
		"code" => "name",
		"description" => "text",
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
			$object_to_fill = new CodeCuePoint();
			
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		parent::validateForInsert($propertiesToSkip);
		
		$this->validatePropertyNotNull("code");
			
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
