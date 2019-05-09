<?php
/**
 * API class for recipient provider containing a static list of email recipients.
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class VidiunEmailNotificationStaticRecipientProvider extends VidiunEmailNotificationRecipientProvider
{	
	/**
	 * Email to emails and names
	 * @var VidiunEmailNotificationRecipientArray
	 */
	public $emailRecipients;
	
	private static $map_between_objects = array(
		'emailRecipients',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vEmailNotificationStaticRecipientProvider();
			
		return parent::toObject($dbObject, $propertiesToSkip);
	}	
}