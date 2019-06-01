<?php
/**
 * Retrieve information and invoke actions on all asset types
 * Used as base class for all asset services
 *
 * @package api
 * @subpackage services
 */
abstract class VidiunAssetService extends VidiunBaseService
{
	/**
	 * @var array of all entry types that extend from media
	 */
	
	protected function getEnabledMediaTypes()
	{
		$mediaTypes = VidiunPluginManager::getExtendedTypes(entryPeer::OM_CLASS, VidiunEntryType::MEDIA_CLIP);
		return $mediaTypes;
	}
		
	/* (non-PHPdoc)
	 * @see VidiunBaseService::setPartnerFilters()
	 */
	protected function setPartnerFilters($partnerId)
	{
		parent::setPartnerFilters($partnerId);
		
		$this->applyPartnerFilterForClass('conversionProfile2');
		$this->applyPartnerFilterForClass('assetParamsOutput');
		$fullActionName = "{$this->serviceName}.$this->actionName";
		if (!in_array($fullActionName, array('flavorAsset.getByEntryId', 'flavorAsset.getWebPlayableByEntryId', 'flavorAsset.list')))
			$this->applyPartnerFilterForClass('asset');
		$this->applyPartnerFilterForClass('assetParams');
	}
	
	/**
	 * Get asset by ID
	 * 
	 * @action get
	 * @param string $id
	 * @return VidiunAsset
	 */
	abstract public function getAction($id);
	
	/**
	* @param asset $asset
	* @param string $fileName
	* @param bool $forceProxy
	* @param int $version
	* @throws VidiunErrors::FILE_DOESNT_EXIST
	*/
	protected function serveAsset(asset $asset, $fileName, $forceProxy = false, $version = null)
	{
		$syncKey = $asset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET, $version);
		
		$fileSync = null;
		$serveRemote = false;
		$partner = PartnerPeer::retrieveByPK($asset->getPartnerId());
		
		switch($partner->getStorageServePriority())
		{
			case StorageProfile::STORAGE_SERVE_PRIORITY_EXTERNAL_ONLY:
				$serveRemote = true;
				$fileSync = vFileSyncUtils::getReadyExternalFileSyncForKey($syncKey);
				if(!$fileSync || $fileSync->getStatus() != FileSync::FILE_SYNC_STATUS_READY)
					throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
				
				break;
			
			case StorageProfile::STORAGE_SERVE_PRIORITY_EXTERNAL_FIRST:
				$fileSync = vFileSyncUtils::getReadyExternalFileSyncForKey($syncKey);
				if($fileSync && $fileSync->getStatus() == FileSync::FILE_SYNC_STATUS_READY)
					$serveRemote = true;
				
				break;
			
			case StorageProfile::STORAGE_SERVE_PRIORITY_VIDIUN_FIRST:
				$fileSync = vFileSyncUtils::getReadyInternalFileSyncForKey($syncKey);
				if($fileSync)
					break;
					
				$fileSync = vFileSyncUtils::getReadyExternalFileSyncForKey($syncKey);
				if(!$fileSync || $fileSync->getStatus() != FileSync::FILE_SYNC_STATUS_READY)
					throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
				
				$serveRemote = true;
				break;
			
			case StorageProfile::STORAGE_SERVE_PRIORITY_VIDIUN_ONLY:
				$fileSync = vFileSyncUtils::getReadyInternalFileSyncForKey($syncKey);
				if(!$fileSync)
					throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);
				
				break;
		}
		
		if($serveRemote && $fileSync)
		{
			header("Location: " . $fileSync->getExternalUrl($asset->getEntryId()));
			die;
		}
		
		return $this->serveFile($asset, asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET, $fileName, $asset->getEntryId(), $forceProxy);
	}
	
	/**
	 * Action for manually exporting an asset
	 * @param string $assetId
	 * @param int $storageProfileId
	 * @throws VidiunErrors::INVALID_FLAVOR_ASSET_ID
	 * @throws VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::INTERNAL_SERVERL_ERROR
	 * @return VidiunFlavorAsset The exported asset
	 */
	protected function exportAction ( $assetId , $storageProfileId )
	{	    
	    $dbAsset = assetPeer::retrieveById($assetId);
	    if (!$dbAsset)
	    {
	        throw new VidiunAPIException(VidiunErrors::INVALID_FLAVOR_ASSET_ID, $assetId);
	    }
	    
	    $dbStorageProfile = StorageProfilePeer::retrieveByPK($storageProfileId);	    
	    if (!$dbStorageProfile)
	    {
	        throw new VidiunAPIException(VidiunErrors::STORAGE_PROFILE_ID_NOT_FOUND, $storageProfileId);
	    }
	    
	   	$scope = $dbStorageProfile->getScope();
	    $scope->setEntryId($dbAsset->getEntryId());
	    if(!$dbStorageProfile->fulfillsRules($scope))
	    {
	    	throw new VidiunAPIException(VidiunErrors::STORAGE_PROFILE_RULES_NOT_FULFILLED, $storageProfileId);
	    }
	    
	    $exported = vStorageExporter::exportFlavorAsset($dbAsset, $dbStorageProfile);
	    
	    if ($exported !== true)
	    {
	        //TODO: implement export errors
	        throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR);
	    }
	    
	    return $this->getAction($assetId);
	}
	
	protected function validateEntryEntitlement($entryId, $assetId)
	{
		if(vEntitlementUtils::getEntitlementEnforcement())
		{
			$entry = entryPeer::retrieveByPK($entryId);
			if(!$entry)
			{
				//we will throw asset not found, as the user is not entitled, and should not know that the entry exists.
				throw new VidiunAPIException(VidiunErrors::ASSET_ID_NOT_FOUND, $assetId);
			}	
		}		
	}
}
