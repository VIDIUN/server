<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects
 */
abstract class VidiunObjectTask extends VidiunObject
{
	/**
	 * @readonly
	 * @var VidiunObjectTaskType
	 */
	public $type;

	/**
	 * @var bool
	 */
	public $stopProcessingOnError;

	/*
	 */
	private static $map_between_objects = array(
		'type',
		'stopProcessingOnError',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vObjectTask();

		return parent::toObject($dbObject, $skip);
	}

	/**
	 * @param array $propertiesToSkip
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull('stopProcessingOnError');
	}

	static function getInstanceByDbObject(vObjectTask $dbObject)
	{
		switch($dbObject->getType())
		{
			case ObjectTaskType::DELETE_ENTRY:
				return new VidiunDeleteEntryObjectTask();
			case ObjectTaskType::MODIFY_CATEGORIES:
				return new VidiunModifyCategoriesObjectTask();
			case ObjectTaskType::DELETE_ENTRY_FLAVORS:
				return new VidiunDeleteEntryFlavorsObjectTask();
			case ObjectTaskType::CONVERT_ENTRY_FLAVORS:
				return new VidiunConvertEntryFlavorsObjectTask();
			case ObjectTaskType::DELETE_LOCAL_CONTENT:
				return new VidiunDeleteLocalContentObjectTask();
			case ObjectTaskType::STORAGE_EXPORT:
				return new VidiunStorageExportObjectTask();
			case ObjectTaskType::MODIFY_ENTRY:
				return new VidiunModifyEntryObjectTask();
			case ObjectTaskType::MAIL_NOTIFICATION:
				return new VidiunMailNotificationObjectTask();
			default:
				return VidiunPluginManager::loadObject('VidiunObjectTask', $dbObject->getType());
		}
	}
}
