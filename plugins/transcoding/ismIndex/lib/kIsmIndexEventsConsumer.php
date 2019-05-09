<?php
class vIsmIndexEventsConsumer implements vObjectChangedEventConsumer
{	
	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::shouldConsumeChangedEvent()
	 */
	public function shouldConsumeChangedEvent(BaseObject $object, array $modifiedColumns)
	{
		if(
			$object instanceof flavorAsset
			&&	in_array(assetPeer::STATUS, $modifiedColumns)
			&&  $object->isLocalReadyStatus()
			&&  $object->hasTag(assetParams::TAG_ISM_MANIFEST)
			&&  $object->getentry()->getStatus() != entryStatus::DELETED
			&& 	!($object->getentry()->getReplacingEntryId())
		)
			return true;
			
		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::objectChanged()
	 */
	public function objectChanged(BaseObject $object, array $modifiedColumns)
	{	
		// replacing the ismc file name in the ism file
		$ismPrevVersionFileSyncKey = $object->getSyncKey(flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$ismContents = vFileSyncUtils::file_get_contents($ismPrevVersionFileSyncKey);
		
		$ismcPrevVersionFileSyncKey = $object->getSyncKey(flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ISMC);
		$ismcContents = vFileSyncUtils::file_get_contents($ismcPrevVersionFileSyncKey);
		$ismcPrevVersionFilePath = vFileSyncUtils::getLocalFilePathForKey($ismcPrevVersionFileSyncKey);
		
		$object->incrementVersion();
		$object->save();
		
		$ismcFileSyncKey = $object->getSyncKey(flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ISMC);
		vFileSyncUtils::moveFromFile($ismcPrevVersionFilePath, $ismcFileSyncKey);			
		$ismcNewName = basename(vFileSyncUtils::getLocalFilePathForKey($ismcFileSyncKey));
		
		VidiunLog::info("Editing ISM set content to [$ismcNewName]");
			
		$ismXml = new SimpleXMLElement($ismContents);
		$ismXml->head->meta['content'] = $ismcNewName;
		
		$tmpPath = vFileSyncUtils::getLocalFilePathForKey($ismPrevVersionFileSyncKey).'.tmp';
		file_put_contents($tmpPath, $ismXml->asXML());
		
		vFileSyncUtils::moveFromFile($tmpPath, $object->getSyncKey(flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET));
					
		return true;
	}

}