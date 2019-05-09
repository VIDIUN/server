<?php
/**
 * @package plugins.pushNotification
 * @subpackage api.objects
 */
class VidiunPushEventNotificationParameter extends VidiunEventNotificationParameter
{
	/**
	 * @var string
	 */
	public $queueKeyToken;

	private static $map_between_objects = array('queueKeyToken');

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
			$dbObject = new vPushEventNotificationParameter();

		return parent::toObject($dbObject, $propertiesToSkip);
	}
}