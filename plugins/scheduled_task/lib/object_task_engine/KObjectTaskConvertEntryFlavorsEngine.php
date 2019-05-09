<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskConvertEntryFlavorsEngine extends VObjectTaskEntryEngineBase
{
	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		/** @var VidiunConvertEntryFlavorsObjectTask $objectTask */
		$objectTask = $this->getObjectTask();
		$entryId = $object->id;
		$reconvert = $objectTask->reconvert;

		$client = $this->getClient();
		$flavorParamsIds = explode(',', $objectTask->flavorParamsIds);
		foreach($flavorParamsIds as $flavorParamsId)
		{
			try
			{
				$flavorAssetFilter = new VidiunFlavorAssetFilter();
				$flavorAssetFilter->entryIdEqual = $entryId;
				$flavorAssetFilter->flavorParamsIdEqual = $flavorParamsId;
				$flavorAssetFilter->statusEqual = VidiunFlavorAssetStatus::READY;
				$flavorAssetResponse = $client->flavorAsset->listAction($flavorAssetFilter);
				if (!count($flavorAssetResponse->objects) || $reconvert)
					$client->flavorAsset->convert($entryId, $flavorParamsId);

			}
			catch(Exception $ex)
			{
				VidiunLog::err(sprintf('Failed to convert entry id %s with flavor params id %s', $entryId, $flavorParamsId));
				VidiunLog::err($ex);
			}
		}
	}
}