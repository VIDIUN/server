<?php

/**
 * @package plugins.scheduledTaskMetadata
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskExecuteMetadataXsltEngine extends VObjectTaskEntryEngineBase
{
	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		/** @var VidiunExecuteMetadataXsltObjectTask $objectTask */
		$objectTask = $this->getObjectTask();
		if (is_null($objectTask))
			return;

		$entryId = $object->id;
		$metadataProfileId = $objectTask->metadataProfileId;
		$metadataObjectType = $objectTask->metadataObjectType;
		$xslt = $objectTask->xslt;
		$client = $this->getClient();
		$metadataPlugin = VidiunMetadataClientPlugin::get($client);

		$filter = new VidiunMetadataFilter();
		$filter->objectIdEqual = $entryId;
		$filter->metadataProfileIdEqual = $metadataProfileId;
		$filter->metadataObjectTypeEqual = $metadataObjectType;
		$metadataResult = $metadataPlugin->metadata->listAction($filter);

		if (!count($metadataResult->objects))
		{
			VidiunLog::info(sprintf('Metadata object was not found for entry %s, profile id %s and object type %s', $entryId, $metadataProfileId, $metadataObjectType));
			return;
		}

		$xsltFilePath = sys_get_temp_dir().'/xslt_'.time(true).'.xslt';
		file_put_contents($xsltFilePath, $xslt);
		$metadataId = $metadataResult->objects[0]->id;
		$metadataPlugin->metadata->updateFromXSL($metadataId, $xsltFilePath);
		unlink($xsltFilePath);
	}
}