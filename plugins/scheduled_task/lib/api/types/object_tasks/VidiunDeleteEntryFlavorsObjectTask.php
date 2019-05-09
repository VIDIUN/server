<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects.objectTasks
 */
class VidiunDeleteEntryFlavorsObjectTask extends VidiunObjectTask
{
	/**
	 * The logic to use to choose the flavors for deletion
	 *
	 * @var VidiunDeleteFlavorsLogicType
	 */
	public $deleteType;

	/**
	 * Comma separated list of flavor param ids to delete or keep
	 *
	 * @var string
	 */
	public $flavorParamsIds;

	public function __construct()
	{
		$this->type = ObjectTaskType::DELETE_ENTRY_FLAVORS;
	}

	public function toObject($dbObject = null, $skip = array())
	{
		/** @var vObjectTask $dbObject */
		$dbObject = parent::toObject($dbObject, $skip);

		$flavorParamsIds = array_unique(vString::fromCommaSeparatedToArray($this->flavorParamsIds));
		$dbObject->setDataValue('deleteType', $this->deleteType);
		$dbObject->setDataValue('flavorParamsIds', $flavorParamsIds);
		return $dbObject;
	}

	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);

		/** @var vObjectTask $srcObj */
		$this->deleteType = $srcObj->getDataValue('deleteType');
		$this->flavorParamsIds = implode(',', $srcObj->getDataValue('flavorParamsIds'));
	}
}