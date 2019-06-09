<?php
/**
 * Used to ingest media that is already ingested to Vidiun system as a different entry in the past, the new created flavor asset will be ready immediately using a file sync of link type that will point to the existing file sync of the existing entry.
 * 
 * @package api
 * @subpackage objects
 */
class VidiunEntryResource extends VidiunContentResource
{
	/**
	 * ID of the source entry 
	 * @var string
	 */
	public $entryId;
	
	/**
	 * ID of the source flavor params, set to null to use the source flavor
	 * @var int
	 */
	public $flavorParamsId;
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('entryId');
	
    	$srcEntry = entryPeer::retrieveByPK($this->entryId);
		if (!$srcEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryId);

		if($this->shouldValidateFileExistance($srcEntry) && !$this->checkIfFileExist())
		{
			throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
		}
	}

	protected function checkIfFileExist($local = false)
	{
		$fileSyncs = $this->getFileSyncsForSrcFlavor($local);
		foreach($fileSyncs as $fileSync)
		{
			$fileSync = vFileSyncUtils::resolve($fileSync);
			if($fileSync->getFileType() != FileSync::FILE_SYNC_FILE_TYPE_LINK)
			{
				if (!$local || file_exists($fileSync->getFullPath()))
				{
					return true;
				}
			}
		}
		return false;
	}

	protected function getFileSyncsForSrcFlavor($local = false)
	{
		$srcFlavorAsset = null;
		if(is_null($this->flavorParamsId))
		{
			$srcFlavorAsset = assetPeer::retrieveOriginalByEntryId($this->entryId);
			if (!$srcFlavorAsset)
			{
				throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_IS_MISSING);
			}
		}
		else
		{
			$srcFlavorAsset = assetPeer::retrieveByEntryIdAndParams($this->entryId, $this->flavorParamsId);
			if (!$srcFlavorAsset)
			{
				throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $this->assetId);
			}
		}

		$key = $srcFlavorAsset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$c = FileSyncPeer::getCriteriaForFileSyncKey($key);
		if ($local)
		{
			$c->add(FileSyncPeer::DC, vDataCenterMgr::getCurrentDcId());
		}
		return FileSyncPeer::doSelect($c);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunResource::validateEntry()
	 */
	public function validateEntry(entry $dbEntry, $validateLocalExist = false)
	{
		parent::validateEntry($dbEntry, $validateLocalExist);
		
		$srcEntry = entryPeer::retrieveByPK($this->entryId);
		if(!$srcEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryId);
		if ($this->shouldValidateFileExistance($srcEntry) && $validateLocalExist && !$this->checkIfFileExist(true))
		{
			throw new VidiunAPIException(VidiunErrors::SOURCE_FILE_NOT_FOUND);
		}
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		$this->validateForUsage($object_to_fill, $props_to_skip);
		
    	$srcEntry = entryPeer::retrieveByPK($this->entryId);
    	if(!$srcEntry)
    		throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryId);

    	if($srcEntry->getType() == entryType::LIVE_STREAM)
    	{
    		/* @var $srcEntry LiveEntry */
    		
    		if(!in_array($srcEntry->getSource(), array(EntrySourceType::LIVE_STREAM, EntrySourceType::LIVE_STREAM_ONTEXTDATA_CAPTIONS)))
    			throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($this));
    			
    		$mediaServer = $srcEntry->getMediaServer();
    		if($mediaServer && !is_null($mediaServer->getDc()) && $mediaServer->getDc() != vDataCenterMgr::getCurrentDcId())
    		{
				$remoteDCHost = vDataCenterMgr::getRemoteDcExternalUrlByDcId($mediaServer->getDc());
				if($remoteDCHost)
				{
					vFileUtils::dumpApiRequest($remoteDCHost);
				}
				else
				{
					throw new VidiunAPIException(VidiunErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN);
				}
    		}
    		
			if($object_to_fill && !($object_to_fill instanceof vLiveEntryResource))
				throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($object_to_fill));
				
			$object_to_fill = new vLiveEntryResource();
    		$object_to_fill->setEntry($srcEntry);
    		    		
    		return $object_to_fill;
    	}
    	
		if(!$object_to_fill)
			$object_to_fill = new vFileSyncResource();

		if (myEntryUtils::isLiveClippingEntry($srcEntry))
		{
			$object_to_fill->setOriginEntryId($this->entryId);
			return $object_to_fill;
		}
			
    	if($srcEntry->getMediaType() == VidiunMediaType::IMAGE)
    	{
			$object_to_fill->setFileSyncObjectType(FileSyncObjectType::ENTRY);
			$object_to_fill->setObjectSubType(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
			$object_to_fill->setObjectId($srcEntry->getId());
			
			return $object_to_fill;
    	}
    	
    	$srcFlavorAsset = null;
    	if(is_null($this->flavorParamsId))
    	{
			$srcFlavorAsset = assetPeer::retrieveOriginalByEntryId($this->entryId);
	    	if(!$srcFlavorAsset)
	    		throw new VidiunAPIException(VidiunErrors::ORIGINAL_FLAVOR_ASSET_IS_MISSING);
    	}
		else
		{
			$srcFlavorAsset = assetPeer::retrieveByEntryIdAndParams($this->entryId, $this->flavorParamsId);
	    	if(!$srcFlavorAsset)
	    		throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $this->flavorParamsId);
		}
		
    		
		$object_to_fill->setFileSyncObjectType(FileSyncObjectType::FLAVOR_ASSET);
		$object_to_fill->setObjectSubType(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$object_to_fill->setObjectId($srcFlavorAsset->getId());
		$object_to_fill->setOriginEntryId($this->entryId);

		return $object_to_fill;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunResource::entryHandled()
	 */
	public function entryHandled(entry $dbEntry)
	{
    	$srcEntry = entryPeer::retrieveByPK($this->entryId);
    	if(
    		$srcEntry->getType() == VidiunEntryType::MEDIA_CLIP 
    		&& 
    		$dbEntry->getType() == VidiunEntryType::MEDIA_CLIP 
    		&& 
    		$dbEntry->getMediaType() == VidiunMediaType::IMAGE
    	)
    	{
    		if($dbEntry->getMediaType() == VidiunMediaType::IMAGE)
    		{
    			$dbEntry->setDimensions($srcEntry->getWidth(), $srcEntry->getHeight());
    			$dbEntry->setMediaDate($srcEntry->getMediaDate(null));
    			$dbEntry->save();
    		}
    		else 
    		{
		    	$srcFlavorAsset = null;
		    	if(is_null($this->flavorParamsId))
					$srcFlavorAsset = assetPeer::retrieveOriginalByEntryId($this->entryId);
				else
					$srcFlavorAsset = assetPeer::retrieveByEntryIdAndParams($this->entryId, $this->flavorParamsId);
				
				if($srcFlavorAsset)
				{
	    			$dbEntry->setDimensions($srcFlavorAsset->getWidth(), $srcFlavorAsset->getHeight());
	    			$dbEntry->save();
				}
    		}
    	}
    	
    	return parent::entryHandled($dbEntry);
	}

	/**
	 * @param $srcEntry
	 * @return bool
	 */
	protected function shouldValidateFileExistance($srcEntry)
	{
		return ($srcEntry->getMediaType() != VidiunMediaType::IMAGE &&
			$srcEntry->getMediaType() != VidiunMediaType::LIVE_STREAM_FLASH &&
			!myEntryUtils::isLiveClippingEntry($srcEntry));
	}
}
