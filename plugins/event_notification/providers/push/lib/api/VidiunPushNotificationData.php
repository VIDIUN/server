<?php
/**
 * @package plugins.pushNotification
 * @subpackage api.objects
 */
class VidiunPushNotificationData extends VidiunObject 
{
	/**
	 * @var string
	 * @readonly
	 */
	public $queueName;
	
	/**
	 * @var string
	 * @readonly
	 */
	public $queueKey;
	
	/**
	 * @var string
	 * @readonly
	 */
	public $url;

	private static $map_between_objects = array('queueName', 'queueKey', 'url');

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
			$dbObject = new vPushNotificationData();

		return parent::toObject($dbObject, $propertiesToSkip);
	}
}