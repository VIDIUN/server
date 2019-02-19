<?php
/**
 * @package plugins.booleanNotification
 * @subpackage api.objects
 */
class KalturaBooleanNotificationTemplate extends KalturaEventNotificationTemplate
{
	public function __construct()
	{
		$this->type = BooleanNotificationPlugin::getApiValue(BooleanNotificationTemplateType::BOOLEAN);
	}

	/* (non-PHPdoc)
	 * @see KalturaObject::toObject()
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if(is_null($dbObject))
			$dbObject = new BooleanNotificationTemplate();

		return parent::toObject($dbObject, $propertiesToSkip);
	}

	/* (non-PHPdoc)
 	* @see KalturaObject::validateForInsert()
 	*/
	public function validateForInsert($propertiesToSkip = array())
	{
		return parent::validateForInsert($propertiesToSkip);
	}

	/* (non-PHPdoc)
 	* @see KalturaObject::validateForUpdate()
 	*/
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$propertiesToSkip[] = 'type';
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}

	/* (non-PHPdoc)
	 * @see KalturaObject::fromObject()
 	*/
	public function doFromObject($dbObject, KalturaDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject BooleanNotificationTemplate */
		parent::doFromObject($dbObject, $responseProfile);

	}

}