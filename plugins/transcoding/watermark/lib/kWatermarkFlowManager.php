<?php

/**
 * @package plugins.watermark
 * @subpackage lib
 */
class vWatermarkFlowManager implements vObjectAddedEventConsumer
{
	/* (non-PHPdoc)
 	* @see vObjectAddedEventConsumer::shouldConsumeAddedEvent()
 	*/
	public function shouldConsumeAddedEvent(BaseObject $object)
	{
		if($object instanceof entry && $object->getReplacedEntryId() && $object->getIsTemporary())
			return true;
		
		return false;
	}
	
	/* (non-PHPdoc)
 	* @see vObjectAddedEventConsumer::objectAdded()
 	*/
	public function objectAdded(BaseObject $object, BatchJob $raisedJob = null)
	{
		if($object instanceof entry && $object->getReplacedEntryId() && $object->getIsTemporary())
		{
			$this->copyWatermarkData($object);
		}
		
		return true;
	}
	
	protected function copyWatermarkData(entry $entry)
	{
		$originalEntryId = $entry->getReplacedEntryId();
		$originalEntry = entryPeer::retrieveByPK($originalEntryId);
		if(!$originalEntry)
		{
			VidiunLog::debug("Original entry with id [$originalEntryId], not found");
			return;
		}
		
		VidiunLog::debug("Original entry id $originalEntryId");
		VidiunLog::debug("Replacing entry id [{$entry->getId()}]");
		
		$watermarkMetadata = vWatermarkManager::getWatermarkMetadata($originalEntry);
		if(!$watermarkMetadata)
		{
			VidiunLog::debug("Watermark data not found for entry [$originalEntryId]");
			return true;
		}
		
		vWatermarkManager::copyWatermarkData($watermarkMetadata, $originalEntry, $entry);
	}
}