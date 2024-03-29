<?php
/**
 * @package plugins.youtubeApiDistribution
 * @subpackage api.objects
 */
class VidiunYoutubeApiDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $videoAssetFilePath;
	
	/**
	 * @var string
	 */
	public $thumbAssetFilePath;

	/**
	 * @var VidiunYouTubeApiCaptionDistributionInfoArray
	 */
	public $captionsInfo;

	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
	    
		if(!$distributionJobData)
			return;
		
		if(!($distributionJobData->distributionProfile instanceof VidiunYoutubeApiDistributionProfile))
			return;
			
		$flavorAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->flavorAssetIds));
		if(count($flavorAssets)) // if we have specific flavor assets for this distribution, grab the first one
			$flavorAsset = reset($flavorAssets);
		else // take the source asset
			$flavorAsset = assetPeer::retrieveOriginalReadyByEntryId($distributionJobData->entryDistribution->entryId);
		
		if($flavorAsset) 
		{
			$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			if(vFileSyncUtils::fileSync_exists($syncKey))
				$this->videoAssetFilePath = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
		}
		
		$thumbAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->thumbAssetIds));
		if(count($thumbAssets))
		{
			$syncKey = reset($thumbAssets)->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			if(vFileSyncUtils::fileSync_exists($syncKey))
				$this->thumbAssetFilePath = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
		}
		
		$this->addCaptionsData($distributionJobData);
	}
	
	private static $map_between_objects = array
	(
		"videoAssetFilePath",
		"thumbAssetFilePath",
		"captionsInfo",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	private function addCaptionsData(VidiunDistributionJobData $distributionJobData) {
		/* @var $mediaFile VidiunDistributionRemoteMediaFile */
		$assetIdsArray = explode ( ',', $distributionJobData->entryDistribution->assetIds );
		if (empty($assetIdsArray)) return;
		$assets = array ();
		$this->captionsInfo = new VidiunYouTubeApiCaptionDistributionInfoArray();
		
		foreach ( $assetIdsArray as $assetId ) {
			$asset = assetPeer::retrieveByIdNoFilter( $assetId );
			if (!$asset){
				VidiunLog::err("Asset [$assetId] not found");
				continue;
			}
			if ($asset->getStatus() == asset::ASSET_STATUS_READY) {
				$assets [] = $asset;
			}
			elseif($asset->getStatus()== asset::ASSET_STATUS_DELETED) {
				$captionInfo = new VidiunYouTubeApiCaptionDistributionInfo ();
				$captionInfo->action = VidiunYouTubeApiDistributionCaptionAction::DELETE_ACTION;
				$captionInfo->assetId = $assetId;
				//getting the asset's remote id
				foreach ( $distributionJobData->mediaFiles as $mediaFile ) {
					if ($mediaFile->assetId == $assetId) {
						$captionInfo->remoteId = $mediaFile->remoteId;
						$this->captionsInfo [] = $captionInfo;
						break;
					}
				}
			}
			else{
				VidiunLog::err("Asset [$assetId] has status [".$asset->getStatus()."]. not added to provider data");
			}
		}

		foreach ( $assets as $asset ) {
			$assetType = $asset->getType ();
			switch ($assetType) {
				case CaptionPlugin::getAssetTypeCoreValue ( CaptionAssetType::CAPTION ):
					$syncKey = $asset->getSyncKey ( asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET );
					if (vFileSyncUtils::fileSync_exists ( $syncKey )) {
						$captionInfo = $this->getCaptionInfo($asset, $syncKey, $distributionJobData);
						if ($captionInfo){
							$captionInfo->label = $asset->getLabel();
							if(!$captionInfo->label)
								$captionInfo->label = $asset->getLanguage();
							$captionInfo->language = $this->getLanguageCode($asset->getLanguage());
							if ($captionInfo->language)
								$this->captionsInfo [] = $captionInfo;
							else
								VidiunLog::err('The caption ['.$asset->getId().'] has unrecognized language ['.$asset->getLanguage().']'); 
						}
					}
					break;
				case AttachmentPlugin::getAssetTypeCoreValue ( AttachmentAssetType::ATTACHMENT ) :
					/* @var $asset AttachmentAsset */
					$syncKey = $asset->getSyncKey ( asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET );
					if (vFileSyncUtils::fileSync_exists ( $syncKey )) {
						$captionInfo = $this->getCaptionInfo($asset, $syncKey, $distributionJobData);
						if ($captionInfo){
							//language code should be set in the attachments title
							$captionInfo->label = $asset->getTitle();
							$captionInfo->language = $asset->getTitle();
							
							$languageCodeReflector = VidiunTypeReflectorCacher::get('VidiunLanguageCode');
							//check if the language code exists 
						    if($languageCodeReflector && $languageCodeReflector->getConstantName($captionInfo->language))
								$this->captionsInfo [] = $captionInfo;
							else
								VidiunLog::err('The attachment ['.$asset->getId().'] has unrecognized language ['.$asset->getTitle().']'); 		    
						}
					}
					break;
			}
		}
	}
	
	private function getLanguageCode($language = null){
		$languageReflector = VidiunTypeReflectorCacher::get('VidiunLanguage');
		$languageCodeReflector = VidiunTypeReflectorCacher::get('VidiunLanguageCode');
		if($languageReflector && $languageCodeReflector)
		{
			$languageCode = $languageReflector->getConstantName($language);
			if($languageCode)
				return $languageCodeReflector->getConstantValue($languageCode);
		}
		return null;
	}
	
	private function getCaptionInfo($asset, $syncKey, VidiunDistributionJobData $distributionJobData) {
		$captionInfo = new VidiunYouTubeApiCaptionDistributionInfo ();
		$fileSync = vFileSyncUtils::getResolveLocalFileSyncForKey($syncKey);
		$captionInfo->filePath = $fileSync->getFullPath();
		$captionInfo->assetId = $asset->getId();
		$captionInfo->version = $asset->getVersion();
		/* @var $mediaFile VidiunDistributionRemoteMediaFile */
		$distributed = false;
		foreach ( $distributionJobData->mediaFiles as $mediaFile ) {
			if ($mediaFile->assetId == $asset->getId ()) {
				$distributed = true;
				if ($asset->getVersion () > $mediaFile->version) {
					$captionInfo->action = VidiunYouTubeApiDistributionCaptionAction::UPDATE_ACTION;
				}
				break;
			}
		}
		if (! $distributed)
			$captionInfo->action = VidiunYouTubeApiDistributionCaptionAction::SUBMIT_ACTION;
		elseif ($captionInfo->action != VidiunYouTubeApiDistributionCaptionAction::UPDATE_ACTION) {
			return;
		}
		return $captionInfo;
	}
}
