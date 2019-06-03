<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects.objectTasks
 */
class VidiunModifyCategoriesObjectTask extends VidiunObjectTask
{
	/**
	 * Should the object task add or remove categories?
	 *
	 * @var VidiunScheduledTaskAddOrRemoveType
	 */
	public $addRemoveType;

	/**
	 * The list of category ids to add or remove
	 *
	 * @var VidiunIntegerValueArray
	 */
	public $categoryIds;

	public function __construct()
	{
		$this->type = ObjectTaskType::MODIFY_CATEGORIES;
	}

	public function toObject($dbObject = null, $skip = array())
	{
		/** @var vObjectTask $dbObject */
		$dbObject = parent::toObject($dbObject, $skip);
		$dbObject->setDataValue('addRemoveType', $this->addRemoveType);
		$dbObject->setDataValue('categoryIds', $this->categoryIds);
		return $dbObject;
	}

	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);

		/** @var vObjectTask $srcObj */
		$this->addRemoveType = $srcObj->getDataValue('addRemoveType');
		$this->categoryIds = $srcObj->getDataValue('categoryIds');
	}
}