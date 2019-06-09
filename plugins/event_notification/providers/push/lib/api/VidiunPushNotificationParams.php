<?php
/**
 * Object which contains contextual entry-related data.
 * @package plugins.pushNotification
 * @subpackage api.objects
 */
class VidiunPushNotificationParams extends VidiunObject
{	
	/**
	 * User params
	 * @var VidiunPushEventNotificationParameterArray
	 */
	public $userParams;

	private static $map_between_objects = array('userParams');

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
			$dbObject = new vPushNotificationParams();

		return parent::toObject($dbObject, $propertiesToSkip);
	}
	
}