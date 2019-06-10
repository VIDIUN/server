<?php
/**
 * JobData representing the static receipient array
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class VidiunEmailNotificationStaticRecipientJobData extends VidiunEmailNotificationRecipientJobData
{
	/**
	 * Email to emails and names
	 * @var VidiunKeyValueArray
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
	 * @see VidiunEmailNotificationRecipientJobData::setProviderType()
	 */
	protected function setProviderType() 
	{
		$this->providerType = VidiunEmailNotificationRecipientProviderType::STATIC_LIST;	
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($source_object)
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vEmailNotificationStaticRecipientJobData */
		parent::doFromObject($dbObject, $responseProfile);
		$this->setProviderType();
		
		$this->emailRecipients = VidiunKeyValueArray::fromKeyValueArray($dbObject->getEmailRecipients());
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vEmailNotificationStaticRecipientJobData();
		
		return parent::toObject($dbObject, $propertiesToSkip);
	}
}