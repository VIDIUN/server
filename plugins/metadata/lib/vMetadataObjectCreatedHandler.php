<?php
/**
 * @package plugins.metadata
 * @subpackage lib
 */
class vMetadataObjectCreatedHandler implements vObjectCreatedEventConsumer
{
	/* (non-PHPdoc)
	 * @see vObjectCreatedEventConsumer::shouldConsumeCreatedEvent()
	 */
	public function shouldConsumeCreatedEvent(BaseObject $fromObject)
	{
		if($fromObject instanceof entry)
		{
			if ($fromObject->getIsRecordedEntry() == true)
				return true;
		}
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectCreatedEventConsumer::objectCreated()
	 */
	public function objectCreated(BaseObject $fromObject)
	{
		if($fromObject instanceof entry)
		{
				$liveEntryId = $fromObject->getRootEntryId();
				$this->copyLiveMetadata($fromObject , $liveEntryId);
		}			
		return true;
	}
	
	
	protected function copyLiveMetadata(baseEntry $object , $liveEntryId)
	{
		$recordedEntryId = $object->getId();
		$partnerId = $object->getPartnerId();
	
		$metadataProfiles = MetadataProfilePeer::retrieveAllActiveByPartnerId($partnerId , MetadataObjectType::ENTRY);
	
		foreach ($metadataProfiles as $metadataProfile)
		{
			$originMetadataObj = MetadataPeer::retrieveByObject($metadataProfile->getId() , MetadataObjectType::ENTRY , $liveEntryId);
			if ($originMetadataObj)
			{
					$metadataProfileId = $metadataProfile->getId();
					$metadataProfileVersion = $metadataProfile->getVersion();
	
					$destMetadataObj = new Metadata();
				
					$destMetadataObj->setPartnerId($partnerId);
					$destMetadataObj->setMetadataProfileId($metadataProfileId);
					$destMetadataObj->setMetadataProfileVersion($metadataProfileVersion);
					$destMetadataObj->setObjectType(MetadataObjectType::ENTRY);
					$destMetadataObj->setObjectId($recordedEntryId);
					$destMetadataObj->setStatus(VidiunMetadataStatus::VALID);
	
					$originMetadataKey = $originMetadataObj->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
					$originXml = vFileSyncUtils::file_get_contents($originMetadataKey, true, false);
	
					// validate object exists
					$object = vMetadataManager::getObjectFromPeer($destMetadataObj);
					if($object)
							$destMetadataObj->save();
					else
					{
							VidiunLog::err('invalid object type');
							continue;
					}
	
					$destMetadataKey = $destMetadataObj->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
					vFileSyncUtils::file_put_contents($destMetadataKey, $originXml);
			}
		}
	}
}

