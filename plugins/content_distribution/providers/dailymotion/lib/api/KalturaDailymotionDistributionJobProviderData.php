<?php
/**
 * @package plugins.dailymotionDistribution
 * @subpackage api.objects
 */
class VidiunDailymotionDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $videoAssetFilePath;

	/**
	 * @var string
	 */
	public $accessControlGeoBlockingOperation;

	/**
	 * @var string
	 */
	public $accessControlGeoBlockingCountryList;
	
	/**
	 * @var VidiunDailymotionDistributionCaptionInfoArray
	 */
	public $captionsInfo;
	
	
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
	    parent::__construct($distributionJobData);
	    
		if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunDailymotionDistributionProfile))
			return;
			
		$flavorAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->flavorAssetIds));
		if(count($flavorAssets)) // if we have specific flavor assets for this distribution, grab the first one
			$flavorAsset = reset($flavorAssets);
		else // take the source asset
			$flavorAsset = assetPeer::retrieveOriginalReadyByEntryId($distributionJobData->entryDistribution->entryId);
		
		if($flavorAsset) 
		{
			$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			$this->videoAssetFilePath = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
		}

		// look for vrule with action block and condition of country
		$entry = entryPeer::retrieveByPK($distributionJobData->entryDistribution->entryId);
		if ($entry && $entry->getAccessControl())
			$this->setGeoBlocking($entry->getAccessControl());
			
		$this->addCaptionsData($distributionJobData);
	}


	
	private static $map_between_objects = array
	(
		"videoAssetFilePath",
		"captionsInfo",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/**
	 * @return string $videoAssetFilePath
	 */
	public function getVideoAssetFilePath()
	{
		return $this->videoAssetFilePath;
	}

	/**
	 * @param string $videoAssetFilePath
	 */
	public function setVideoAssetFilePath($videoAssetFilePath)
	{
		$this->videoAssetFilePath = $videoAssetFilePath;
	}

	protected function setGeoBlocking(accessControl $accessControl)
	{
		$rules = $accessControl->getRulesArray();
		foreach($rules as $rule)
		{
			$hasBlockAction = false;
			/* @var $rule vRule */
			foreach($rule->getActions() as $action)
			{
				/* @var $action vAccessControlAction */
				if($action->getType() == RuleActionType::BLOCK)
				{
					$hasBlockAction = true;
					break;
				}
			}

			if (!$hasBlockAction)
				continue;

			foreach($rule->getConditions() as $condition)
			{
				if ($condition instanceof vCountryCondition)
				{
					/* @var $condition vCountryCondition */
					$this->accessControlGeoBlockingCountryList = implode(',', $condition->getStringValues());
					if ($condition->getNot() === true)
						$this->accessControlGeoBlockingOperation = 'allow';
					else
						$this->accessControlGeoBlockingOperation = 'deny';

					break;
				}
			}
		}
	}
	
	private function addCaptionsData(VidiunDistributionJobData $distributionJobData) {
		/* @var $mediaFile VidiunDistributionRemoteMediaFile */
		$assetIdsArray = explode ( ',', $distributionJobData->entryDistribution->assetIds );
		if (empty($assetIdsArray)) return;
		$assets = array ();
		$this->captionsInfo = new VidiunDailymotionDistributionCaptionInfoArray();
		
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
				$captionInfo = new VidiunDailymotionDistributionCaptionInfo ();
				$captionInfo->action = VidiunDailymotionDistributionCaptionAction::DELETE_ACTION;
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
							$captionInfo->language = $this->getLanguageCode($asset->getLanguage());
							$captionInfo->format = $this->getCaptionFormat($asset);
							if ($captionInfo->language)
								$this->captionsInfo [] = $captionInfo;
							else
								VidiunLog::err('The caption ['.$asset->getId().'] has unrecognized language ['.$asset->getLanguage().']'); 
						}
					}
					break;
				case AttachmentPlugin::getAssetTypeCoreValue ( AttachmentAssetType::ATTACHMENT ) :
					$syncKey = $asset->getSyncKey ( asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET );
					if (vFileSyncUtils::fileSync_exists ( $syncKey )) {
						$captionInfo = $this->getCaptionInfo($asset, $syncKey, $distributionJobData);
						if ($captionInfo){
							//language code should be set in the attachments title
							$captionInfo->language = $asset->getTitle();
							$captionInfo->format = $this->getCaptionFormat($asset);
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
		$captionInfo = new VidiunDailymotionDistributionCaptionInfo ();
		$captionInfo->filePath = vFileSyncUtils::getLocalFilePathForKey ( $syncKey, false );
		$captionInfo->assetId = $asset->getId();
		$captionInfo->version = $asset->getVersion();
		/* @var $mediaFile VidiunDistributionRemoteMediaFile */
		$distributed = false;
		foreach ( $distributionJobData->mediaFiles as $mediaFile ) {
			if ($mediaFile->assetId == $asset->getId ()) {
				$distributed = true;
				if ($asset->getVersion () > $mediaFile->version) {
					$captionInfo->action = VidiunDailymotionDistributionCaptionAction::UPDATE_ACTION;
				}
				break;
			}
		}
		if (! $distributed)
			$captionInfo->action = VidiunDailymotionDistributionCaptionAction::SUBMIT_ACTION;
		elseif ($captionInfo->action != VidiunDailymotionDistributionCaptionAction::UPDATE_ACTION) {
			return;
		}
		return $captionInfo;
	}
	
	private function getCaptionFormat($asset){		
		if ($asset instanceof  AttachmentAsset && ($asset->getPartnerDescription() == 'smpte-tt'))
			return VidiunDailymotionDistributionCaptionFormat::TT;
			
		if ($asset instanceof  captionAsset){
			switch ($asset->getContainerFormat()){
				case VidiunCaptionType::SRT:
					return VidiunDailymotionDistributionCaptionFormat::SRT;
				case VidiunCaptionType::DFXP:
					return VidiunDailymotionDistributionCaptionFormat::TT;	
			}
		}
		VidiunLog::err("caption [".$asset->getId()."] has an unknow format.");
		return null;
	}
}
