<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskDeleteLocalContentEngine extends VObjectTaskEntryEngineBase
{

	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		$client = $this->getClient();
		$entryId = $object->id;
		VidiunLog::info("Deleting local content for entry [$entryId]");
		$flavors = $this->getEntryFlavors($object, $client);
		if (!count($flavors))
			return;

		foreach ($flavors as $flavor)
		{
			$this->deleteFlavor($flavor->id, $flavor->partnerId);
		}
	}

	protected function getEntryFlavors($object){
		$client = $this->getClient();
		$pager = new VidiunFilterPager();
		$pager->pageSize = 500; // use max size, throw exception in case we got more than 500 flavors where pagination is not supported
		$filter = new VidiunFlavorAssetFilter();
		$filter->entryIdEqual = $object->id;
		$flavorsResponse = $client->flavorAsset->listAction($filter);

		if ($flavorsResponse->totalCount > $pager->pageSize)
			throw new Exception('Too many flavors were found where pagination is not supported');

		$flavors = $flavorsResponse->objects;
		VidiunLog::info('Found '.count($flavors). ' flavors');
		return $flavors;
	}


	/**
	 * @param $id
	 * @param $partnerId
	 */
	protected function deleteFlavor($id, $partnerId)
	{
		$client = $this->getClient();
		try
		{
			$client->flavorAsset->deleteLocalContent($id);
			VidiunLog::info("Local content of flavor id [$id] was deleted");
		}
		catch(Exception $ex)
		{
			VidiunLog::err($ex->getMessage());
			VidiunLog::err("Failed to delete local content of flavor id [$id]");
		}
	}
}