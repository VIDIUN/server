<?php
/**
 * API class for recipient provider which constructs a dynamic list of recipients according to a user filter
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class VidiunEmailNotificationUserRecipientProvider extends VidiunEmailNotificationRecipientProvider
{
	/**
	 * @var VidiunUserFilter
	 */
	public $filter;
	
	private static $map_between_objects = array(
		'filter',
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
			$dbObject = new vEmailNotificationUserRecipientProvider();
			
		return parent::toObject($dbObject, $propertiesToSkip);
	}	
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($source_object)
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbObject, $responseProfile);
		if ($dbObject->getFilter())
		{
			$this->filter = new VidiunUserFilter();
			$this->filter->fromObject($dbObject->getFilter());
		}
	}
}