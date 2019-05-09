<?php
/**
 * JobData representing the dynamic user receipient array
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class VidiunEmailNotificationUserRecipientJobData extends VidiunEmailNotificationRecipientJobData
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
	 * @see VidiunEmailNotificationRecipientJobData::setProviderType()
	 */
	protected function setProviderType() {
		$this->providerType = VidiunEmailNotificationRecipientProviderType::USER;	
		
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($source_object)
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vEmailNotificationStaticRecipientJobData */
		parent::doFromObject($dbObject, $responseProfile);
		$this->setProviderType();
		if ($dbObject->getFilter())
		{
			$this->filter = new VidiunUserFilter();
			$this->filter->fromObject($dbObject->getFilter());
		}
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vEmailNotificationUserRecipientJobData();
		
		return parent::toObject($dbObject, $propertiesToSkip);
	}
}