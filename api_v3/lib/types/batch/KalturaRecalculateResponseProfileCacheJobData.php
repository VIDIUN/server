<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunRecalculateResponseProfileCacheJobData extends VidiunRecalculateCacheJobData
{
	/**
	 * http / https
	 * @var string
	 */
	public $protocol;

	/**
	 * @var VidiunSessionType
	 */
	public $vsType;

	/**
	 * @var VidiunIntegerValueArray
	 */
	public $userRoles;

	/**
	 * Class name
	 * @var string
	 */
	public $cachedObjectType;

	/**
	 * @var string
	 */
	public $objectId;

	/**
	 * @var string
	 */
	public $startObjectKey;

	/**
	 * @var string
	 */
	public $endObjectKey;
    
	private static $map_between_objects = array
	(
		'protocol',
		'vsType',
		'userRoles',
		'cachedObjectType' => 'objectType',
		'objectId',
		'startObjectKey',
		'endObjectKey',
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vRecalculateResponseProfileCacheJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
}
