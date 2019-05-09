<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.objects
 */
class VidiunEventNotificationArrayParameter extends VidiunEventNotificationParameter
{
	/**
	 * @var VidiunStringArray
	 */
	public $values;
	
	/**
	 * Used to restrict the values to close list
	 * @var VidiunStringValueArray
	 */
	public $allowedValues;
	
	private static $map_between_objects = array
	(
		'values',
		'allowedValues',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vEventNotificationArrayParameter();
			
		return parent::toObject($dbObject, $skip);
	}
}