<?php
/**
 * API object which provides the recipients of category related notifications.
 *
 * @package plugins.emailNotification
 * @subpackage model.data
 */
class VidiunEmailNotificationCategoryRecipientProvider extends VidiunEmailNotificationRecipientProvider
{
	/**
	 * The ID of the category whose subscribers should receive the email notification.
	 * @var VidiunStringValue
	 */
	public $categoryId;

	/**
	 * The IDs of the categories whose subscribers should receive the email notification.
	 * @var VidiunStringValue
	 */
	public $categoryIds;
	
	/**
	 *
	 * @var VidiunCategoryUserProviderFilter
	 */
	public $categoryUserFilter;

	private static $map_between_objects = array(
		'categoryId',
		'categoryIds',
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
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		$this->validate();
		if (is_null($dbObject))
			$dbObject = new vEmailNotificationCategoryRecipientProvider();
			
		return parent::toObject($dbObject, $propertiesToSkip);
	}	
	
	/**
	 * Validation function
	 * @throws VidiunEmailNotificationErrors::INVALID_FILTER_PROPERTY
	 */
	protected function validate ()
	{
		if ($this->categoryUserFilter)
		{
			if (isset ($this->categoryUserFilter->categoryIdEqual))
			{
				throw new VidiunAPIException(VidiunEmailNotificationErrors::INVALID_FILTER_PROPERTY, 'categoryIdEqual');
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($source_object)
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbObject, $responseProfile);
		/* @var $dbObject vEmailNotificationCategoryRecipientProvider */
		$categoryIdFieldType = get_class($dbObject->getCategoryId());
		VidiunLog::info("Retrieving API object for categoryId field of type [$categoryIdFieldType]");
		switch ($categoryIdFieldType)
		{
			case 'vObjectIdField':
				$this->categoryId = new VidiunObjectIdField();
				break;
			case 'vEvalStringField':
				$this->categoryId = new VidiunEvalStringField();
				break;
			case 'vStringValue':
				$this->categoryId = new VidiunStringValue();
				break;
			default:
				$this->categoryId = VidiunPluginManager::loadObject('VidiunStringValue', $categoryIdFieldType);
				break;
		}
		
		if ($this->categoryId)
		{
			$this->categoryId->fromObject($dbObject->getCategoryId());
		}

		$categoryIdsFieldType = get_class($dbObject->getCategoryIds());
		VidiunLog::info("Retrieving API object for categoryIds field of type [$categoryIdsFieldType]");
		switch ($categoryIdsFieldType)
		{
			case 'vEvalStringField':
				$this->categoryIds = new VidiunEvalStringField();
				break;
			case 'vStringValue':
				$this->categoryIds = new VidiunStringValue();
				break;
			default:
				$this->categoryIds = VidiunPluginManager::loadObject('VidiunStringValue', $categoryIdFieldType);
				break;
		}

		if ($this->categoryIds)
		{
			$this->categoryIds->fromObject($dbObject->getCategoryIds());
		}

		if ($dbObject->getCategoryUserFilter())
		{
			$this->categoryUserFilter = new VidiunCategoryUserProviderFilter();
			$this->categoryUserFilter->fromObject($dbObject->getCategoryUserFilter());
		}

	}
} 