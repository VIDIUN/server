<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects.objectTasks
 */
class VidiunStorageExportObjectTask extends VidiunObjectTask
{
	/**
	 * Storage profile id
	 *
	 * @var string
	 */
	public $storageId;

	public function __construct()
	{
		$this->type = ObjectTaskType::STORAGE_EXPORT;
	}

	public function toObject($dbObject = null, $skip = array())
	{
		/** @var vObjectTask $dbObject */
		$dbObject = parent::toObject($dbObject, $skip);

		$dbObject->setDataValue('storageId', $this->storageId);
		return $dbObject;
	}

	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);

		/** @var vObjectTask $srcObj */
		$this->storageId = $srcObj->getDataValue('storageId');
	}
}