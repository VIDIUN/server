<?php
/**
 * Job Data representing the provider of recipients for a single categoryId
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class VidiunEmailNotificationCategoryRecipientJobData extends VidiunEmailNotificationRecipientJobData
{
	/**
	 * @var VidiunCategoryUserFilter
	 */
	public $categoryUserFilter;
	
	private static $map_between_objects = array(
		'categoryUserFilter',
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
		$this->providerType = VidiunEmailNotificationRecipientProviderType::CATEGORY;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($source_object)
	 */
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);
		$this->setProviderType();
		if ($source_object->getCategoryUserFilter())
		{
			$this->categoryUserFilter = new VidiunCategoryUserFilter();
			$this->categoryUserFilter->fromObject($source_object->getCategoryUserFilter());
		}
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vEmailNotificationCategoryRecipientJobData();
		
		return parent::toObject($dbObject, $propertiesToSkip);
	}
}