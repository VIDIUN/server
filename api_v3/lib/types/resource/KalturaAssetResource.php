<?php
/**
 * Used to ingest media that is already ingested to Vidiun system as a different flavor asset in the past, the new created flavor asset will be ready immediately using a file sync of link type that will point to the existing file sync of the existing flavor asset.
 * 
 * @package api
 * @subpackage objects
 */
class VidiunAssetResource extends VidiunContentResource
{
	/**
	 * ID of the source asset 
	 * @var string
	 */
	public $assetId;
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('assetId');
		
		$srcFlavorAsset = assetPeer::retrieveById($this->assetId);
		if(!$srcFlavorAsset)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $this->assetId);
		
		$key = $srcFlavorAsset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$c = FileSyncPeer::getCriteriaForFileSyncKey($key);
		$c->addAnd(FileSyncPeer::FILE_TYPE, array(FileSync::FILE_SYNC_FILE_TYPE_FILE, FileSync::FILE_SYNC_FILE_TYPE_LINK), Criteria::IN);
		
		$fileSyncs = FileSyncPeer::doSelect($c);
		foreach($fileSyncs as $fileSync)
		{
			$fileSync = vFileSyncUtils::resolve($fileSync);
			if($fileSync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_FILE)
				return;
		}
		throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		$this->validateForUsage($object_to_fill, $props_to_skip);
		
		if(!$object_to_fill)
			$object_to_fill = new vFileSyncResource();
		
		$srcFlavorAsset = assetPeer::retrieveById($this->assetId);
		if(!$srcFlavorAsset)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $this->assetId);
		
		$object_to_fill->setFileSyncObjectType(FileSyncObjectType::FLAVOR_ASSET);
		$object_to_fill->setObjectSubType(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$object_to_fill->setObjectId($srcFlavorAsset->getId());
		
		return $object_to_fill;
	}
}