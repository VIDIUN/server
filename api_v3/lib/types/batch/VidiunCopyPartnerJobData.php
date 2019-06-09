<?php
/**
 * @package api
 * @subpackage objects
 * 
 * @property int fromPartnerId
 * @property int toPartnerId
 */
class VidiunCopyPartnerJobData extends VidiunJobData
{
	/**
	 * Id of the partner to copy from
	 * @var int
	 */
	public $fromPartnerId;

	/**
	 * Id of the partner to copy to
	 * @var int
	 */
	public $toPartnerId;
	
	private static $mapBetweenObjects = array
	(
		"fromPartnerId",
		"toPartnerId",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	*/
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vCopyPartnerJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
}
