<?php
/**
 * Subclass for representing a row from the 'flavor_asset' table, used for thumb_assets
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class thumbAsset extends exportableAsset
{
	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or
	 * equivalent initialization method).
	 * @see        __construct()
	 */
	public function applyDefaultValues()
	{
		parent::applyDefaultValues();
		$this->setFileExt('jpg');
		$this->setType(assetType::THUMBNAIL);
	}
	
	public function getFinalDownloadUrlPathWithoutVs()
	{
		$finalPath = '/api_v3/index.php/service/thumbAsset/action/serve';
		$finalPath .= '/thumbAssetId/' . $this->getId();
		if($this->getVersion() > 1)
		{
			$finalPath .= '/v/' . $this->getVersion();
		}
		
		$partner = PartnerPeer::retrieveByPK($this->getPartnerId());
		$entry = $this->getentry();
		
		$partnerVersion = $partner->getCacheThumbnailVersion();
		$entryVersion = $entry->getCacheThumbnailVersion();
		
		$finalPath .= ($partnerVersion ? "/pv/$partnerVersion" : '');
		$finalPath .= ($entryVersion ? "/ev/$entryVersion" : '');
						
		return $finalPath;
	}

	public function keepOnEntryReplacement()
	{
		if($this->getentry()->getReplacementOptions()->getKeepManualThumbnails() && !$this->getFlavorParamsId()) {
			return true;
		}

		return false;
	}
	
	public function getThumbnailUrl(VSecureEntryHelper $securyEntryHelper, $storageId = null, VidiunThumbParams $thumbParams = null)
	{
		if ($thumbParams)
		{
			$assetUrl = $this->getDownloadUrlWithExpiry(84600);
			$assetParameters = VidiunRequestParameterSerializer::serialize($thumbParams, "thumbParams");
			$thumbnailUrl = $assetUrl . "?thumbParams:objectType=VidiunThumbParams&".implode("&", $assetParameters);
		}
			
		if($storageId)
			$thumbnailUrl = $this->getExternalUrl($storageId);
			
		$thumbnailUrl = $this->getDownloadUrl(true);
		
		/* @var $serverNode EdgeServerNode */
		$serverNode = $securyEntryHelper->shouldServeFromServerNode();
		if(!$serverNode)
			return $thumbnailUrl;
		
		$urlParts = explode("://", $thumbnailUrl);
		return $urlParts[0] . "://" . $serverNode->buildEdgeFullPath('http', null, null, assetType::THUMBNAIL) . $urlParts[1];
	}

	protected function getSyncKeysForExporting()
	{
		return array($this->getSyncKey(thumbAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET));
	}
	
}