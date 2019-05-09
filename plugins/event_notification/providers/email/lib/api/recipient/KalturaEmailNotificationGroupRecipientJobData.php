<?php
/**
 * JobData representing the dynamic user receipient array
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class VidiunEmailNotificationGroupRecipientJobData extends VidiunEmailNotificationRecipientJobData
{
	/**
	 * @var string
	 */
	public $groupId;
	
	private static $map_between_objects = array(
		'groupId',
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
		$this->providerType = VidiunEmailNotificationRecipientProviderType::GROUP;	
		
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($source_object)
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vEmailNotificationStaticRecipientJobData */
		parent::doFromObject($dbObject, $responseProfile);
		$this->setProviderType();
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vEmailNotificationGroupRecipientJobData();
		
		return parent::toObject($dbObject, $propertiesToSkip);
	}
}