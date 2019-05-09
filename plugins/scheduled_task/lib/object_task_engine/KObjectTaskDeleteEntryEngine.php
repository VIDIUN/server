<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskDeleteEntryEngine extends VObjectTaskEntryEngineBase
{
	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		$client = $this->getClient();
		$entryId = $object->id;
		VidiunLog::info('Deleting entry '. $entryId);
		$client->baseEntry->delete($entryId);
	}
}