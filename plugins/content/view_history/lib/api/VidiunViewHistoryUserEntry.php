<?php
/**
 * @package plugins.viewHistory
 * @subpackage api
 */
class VidiunViewHistoryUserEntry extends VidiunUserEntry
{
	/**
	 * Playback context
	 * @var string 
	 */
	public $playbackContext;
	
	/**
	 * Last playback time reached by user
	 * @var int
	 */
	public $lastTimeReached;
	
	/**
	 * @var time
	 */
	public $lastUpdateTime;

	/**
	 * mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array(
		'playbackContext',
		'lastTimeReached',
		'lastUpdateTime',
	);
		 
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}	
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if(is_null($dbObject))
			$dbObject = new ViewHistoryUserEntry();
			
		return parent::toObject($dbObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		$object_to_fill = parent::toInsertableObject($object_to_fill, $props_to_skip);
		if (vCurrentContext::getCurrentSessionType() == SessionType::USER)
		{
			if ($this->userId && (!vCurrentContext::getCurrentVsVuser() ||strtolower(vCurrentContext::getCurrentVsVuser()->getPuserId()) != strtolower($this->userId)))
			{
				throw new VidiunAPIException (VidiunErrors::INVALID_USER_ID);	
			}
		}
		
		return $object_to_fill;
	}
	
}
