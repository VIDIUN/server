<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskStorageExportEngine extends VObjectTaskEntryEngineBase
{

	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		/** @var VidiunStorageExportObjectTask $objectTask */
		$objectTask = $this->getObjectTask();
		if (is_null($objectTask))
			return;

		$entryId = $object->id;
		$storageId = $objectTask->storageId;
		if (!$storageId)
			throw new Exception('Storage profile was not configured');

		VidiunLog::info("Submitting entry export for entry $entryId to remote storage $storageId");

		$client = $this->getClient();
		$client->baseEntry->export($entryId, $storageId);
	}
}