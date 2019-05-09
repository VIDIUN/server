<?php
/**
 * @package plugins.youTubeDistribution
 * @subpackage lib
 */
class YouTubeDistributionCsvFeedHelper
{
	protected $_csvMap = array();
	protected $_captionCsvMap = array();

	const METADATA_CUSTOME_USAGE_POLICY_FIELD = "CustomUsagePolicy";
	const METADATA_CUSTOME_MATCH_POLICY_FIELD = "CustomMatchPolicy";
	const METADATA_PROFILE_YOUTUBE_CUSTOM_MATCH_POLICY_SYSTEM_NAME = "YoutubeCustomMatchPolicy";
	const METADATA_PROFILE_YOUTUBE_CUSTOM_USAGE_POLICY_SYSTEM_NAME = "YoutubeCustomUsagePolicy";

	/**
	 * @var string
	 */
	protected $_directoryName;

	/**
	 * @var string
	 */
	protected $_metadataTempFileName;

	public function __construct(VidiunYouTubeDistributionProfile $distributionProfile)
	{
		$timestampName = date('Ymd-His') . '_' . time();
		$this->_directoryName = '/' . $timestampName;
		if ($distributionProfile->sftpBaseDir)
			$this->_directoryName = '/' . trim($distributionProfile->sftpBaseDir, '/') . $this->_directoryName;

		$this->_metadataTempFileName = 'youtube_csv20_' . $timestampName . '.csv';
	}

	public static function initializeDefaultSubmitFeed(VidiunYouTubeDistributionProfile $distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $captionAssetIds, $entryId = null)
	{
		$feed = new YouTubeDistributionCsvFeedHelper($distributionProfile);
		$feed->genericHandling($distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, null, $entryId);
		$feed->handleCaptions($captionAssetIds);

		return $feed;
	}

	public static function initializeDefaultUpdateFeed(VidiunYouTubeDistributionProfile $distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, YouTubeDistributionRemoteIdHandler $remoteIdHandler, $entryId = null)
	{
		$feed = new YouTubeDistributionCsvFeedHelper($distributionProfile);
		$feed->genericHandling($distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $remoteIdHandler->getVideoId(), $entryId);
		return $feed;
	}

	public function handleCaptions($captionAssetIds)
	{
		$captionAssetInfo = $this->getCaptionAssetInfoForCsv($captionAssetIds);
		foreach($captionAssetInfo as $captionInfo)
		{
			$captionData = array();
			if(file_exists($captionInfo['fileUrl']))
			{
				$captionData['language'] = $captionInfo['language'];
				$captionData['caption_file'] = pathinfo($captionInfo['fileUrl'], PATHINFO_BASENAME);
				$captionData['caption_file_ext'] = $captionExtension = $captionInfo['fileExt'];

				$this->_captionCsvMap[] = $captionData;
			}
		}
	}

	public function genericHandling(VidiunYouTubeDistributionProfile $distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $videoId = null, $entryId)
	{
		// thumbnail file
		if (file_exists($thumbnailFilePath))
			$this->setCsvFieldValue('custom_thumbnail', pathinfo($thumbnailFilePath, PATHINFO_BASENAME));

		$this->setDataByFieldValues($fieldValues, $distributionProfile, $videoId, $videoFilePath, $entryId);
		$this->setAdParamsByFieldValues($fieldValues, $distributionProfile);

	}

	public static function initializeDefaultDeleteFeed(VidiunYouTubeDistributionProfile $distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, YouTubeDistributionRemoteIdHandler $remoteIdHandler)
	{
		$feed = new YouTubeDistributionCsvFeedHelper($distributionProfile);
		$feed->setVideoToDelete($remoteIdHandler->getVideoId());

		return $feed;
	}

	public function setDataByFieldValues(array $fieldValues, VidiunYouTubeDistributionProfile $distributionProfile, $videoId = null, $videoFilePath = null, $entryId)
	{
		if ($videoId) // in case of update
			$this->setCsvFieldValue('video_id',$videoId);
		else if (file_exists($videoFilePath)) // in case of upload
		{
			$this->setCsvFieldValue("filename", pathinfo($videoFilePath, PATHINFO_BASENAME));
			$this->setCsvFieldValueIfHasValue('channel', $fieldValues, VidiunYouTubeDistributionField::VIDEO_CHANNEL);

			if ($this->isAllowedValue($distributionProfile->enableContentId))
			{
				$this->setCsvFieldValue('enable_content_id',"Yes");
				$this->appendRightsAdminByFieldValues($fieldValues, $distributionProfile, $entryId);
			}
			if ($this->isNotAllowedValue($distributionProfile->enableContentId))
				$this->setCsvFieldValue('enable_content_id',"No");
		}

		if ($this->isAllowedValue($distributionProfile->blockOutsideOwnership))
			$this->setCsvFieldValue('block_outside_ownership',"Yes");
		if ($this->isNotAllowedValue($distributionProfile->blockOutsideOwnership))
			$this->setCsvFieldValue('block_outside_ownership',"No");

		$this->setPrivacyStatus($fieldValues,$distributionProfile);
		$this->setDefaultCategory($fieldValues,$distributionProfile);
		$this->setNotifySubscribers($fieldValues,$distributionProfile);

		$this->setCsvFieldValueIfHasValue('custom_id', $fieldValues, VidiunYouTubeDistributionField::ASSET_CUSTOM_ID);
		$this->setCsvFieldValueIfHasValue('title', $fieldValues, VidiunYouTubeDistributionField::ASSET_TITLE);
		$this->setCsvFieldValueIfHasValue('spoken_language', $fieldValues, VidiunYouTubeDistributionField::ASSET_SPOKEN_LANGUAGE);
		$this->setCsvFieldValueIfHasValue('description', $fieldValues, VidiunYouTubeDistributionField::MEDIA_DESCRIPTION); //make this like privacy context
		$this->setCsvFieldValueIfHasValue('require_paid_subscription', $fieldValues, VidiunYouTubeDistributionField::REQUIRE_PAID_SUBSCRIPTION_TO_VIEW);

		$this->setTime('start_time', $fieldValues, VidiunYouTubeDistributionField::START_TIME);
		$this->setTime('end_time', $fieldValues, VidiunYouTubeDistributionField::END_TIME);

		$this->appendDelimitedValues('keywords', $fieldValues, VidiunYouTubeDistributionField::MEDIA_KEYWORDS, '|');
		$this->appendDelimitedValues('domain_whitelist', $fieldValues, VidiunYouTubeDistributionField::VIDEO_DOMAIN_WHITE_LIST, '|');
		$this->appendDelimitedValues('add_asset_labels', $fieldValues, VidiunYouTubeDistributionField::ASSET_LABLES, '|');
	}

	public function setVideoToDelete($videoId)
	{
		$this->_csvMap['video_id'] = $videoId;
	}

	public function getValueForField(array $fieldValues ,$key)
	{
		if (isset($fieldValues[$key])) {
			return $fieldValues[$key];
		}
		return null;
	}

	public function setCsvFieldValueIfHasValue($fieldName , array $fieldValues, $key)
	{
		$value = $this->getValueForField($fieldValues, $key);
		if (!$value)
			return;
		$this->_csvMap["$fieldName"] = $value;
	}

	public function setCsvFieldValue($key, $value)
	{
		$this->_csvMap["$key"] = $value;
	}

	public function setTime($fieldName, $fieldValues , $value)
	{
		$time = $this->getValueForField($fieldValues, $value);
		if ($time && intval($time))
			$this->_csvMap["$fieldName"] = date('c', intval($time));
	}

	/**
	 * @param VidiunYoutubeDistributionProfile $distributionProfile
	 * @return null|string
	 */
	protected function setNotifySubscribers(array $fieldValues , VidiunYoutubeDistributionProfile $distributionProfile)
	{
		$notifySubscribers = $this->getValueForField($fieldValues, VidiunYouTubeDistributionField::VIDEO_NOTIFY_SUBSCRIBERS);
		if ($notifySubscribers == "" || is_null($notifySubscribers))
			$notifySubscribers = $distributionProfile->notifySubscribers;

		if ($this->isAllowedValue($notifySubscribers))
			$this->_csvMap["notify_subscribers"] = "Yes";
		else if ($this->isNotAllowedValue($notifySubscribers))
			$this->_csvMap["notify_subscribers"] = "No";
	}

	/**
	 * @param VidiunYoutubeDistributionProfile $distributionProfile
	 * @return null|string
	 */
	protected function setPrivacyStatus(array $fieldValues , VidiunYoutubeDistributionProfile $distributionProfile)
	{
		$privacyStatus = $this->getValueForField($fieldValues, VidiunYouTubeDistributionField::ENTRY_PRIVACY_STATUS);
		if ($privacyStatus == "" || is_null($privacyStatus))
			$privacyStatus = $distributionProfile->privacyStatus;

		if ($privacyStatus)
		{
			$values = str_replace(',', '|', $privacyStatus);
			$this->_csvMap["privacy"] = $values;
		}
	}
	/**
	 * @param VidiunYoutubeDistributionProfile $distributionProfile
	 */
	protected function setDefaultCategory(array $fieldValues , VidiunYoutubeDistributionProfile $distributionProfile)
	{
		$category = $this->getValueForField($fieldValues, VidiunYouTubeApiDistributionField::MEDIA_CATEGORY);
		if ($category == "" || is_null($category))
			$category = $distributionProfile->defaultCategory;

		if ($category)
			$this->_csvMap["category"] = $category;
	}

	/**
	 * @param array $fieldValues
	 * @param $fieldName
	 * @param $defaultValue
	 */
	protected function getAdvertisingValue(array $fieldValues , $fieldName, $defaultValue )
	{
		$value = $this->getValueForField($fieldValues, $fieldName);
		if ($value == "" || is_null($value))
		{
			$value = $defaultValue;
		}

		return $value;
	}


	/**
	 * @param array $fieldValues
	 * @param $fieldName
	 * @param $defaultValue
	 */
	protected function getPolicyValue(array $fieldValues , $fieldName, $defaultValue )
	{
		$value = $this->getValueForField($fieldValues, $fieldName);
		if ($value == "" || is_null($value))
		{
			$value = $defaultValue;
		}

		return $value;
	}


	public function getCaptionAssetInfoForCsv($captionAssetIds)
	{
		$captionAssetInfo = array();
		
		$assetIdsArray = explode ( ',', $captionAssetIds );
			
		if (empty($assetIdsArray)) 
			return null;
				
		$assets = assetPeer::retrieveByIds($assetIdsArray);
			
		foreach ($assets as $asset)
		{
			$assetType = $asset->getType();
			if($assetType == CaptionPlugin::getAssetTypeCoreValue ( CaptionAssetType::CAPTION ))
			{
				/* @var $asset CaptionAsset */
				$syncKey = $asset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
				if(vFileSyncUtils::fileSync_exists($syncKey))
				{
					$captionInfo = array();
					$captionInfo['fileUrl'] = vFileSyncUtils::getLocalFilePathForKey ( $syncKey, false );
					$captionInfo['fileExt'] = $asset->getFileExt();
					$twoCodeLanguage = languageCodeManager::getTwoCodeFromVidiunName($asset->getLanguage());
					if (!$twoCodeLanguage)
						continue;
					$captionInfo['language'] = $twoCodeLanguage;
					$captionAssetInfo[$asset->getId()]= $captionInfo;
				}
			}
		}
		return $captionAssetInfo;
	}

	public function getCaptionLanguage($language)
	{
		$languageReflector = VidiunTypeReflectorCacher::get('VidiunLanguage');
		return $languageReflector->getConstantName($language);
	}

	public function appendDelimitedValues($csvFieldKey, array $fieldValues, $fieldName, $delimiter )
	{
		$valuesStr = $this->getValueForField($fieldValues, $fieldName);
		$values = str_replace(',' ,$delimiter, $valuesStr );
		if($values)
			$this->_csvMap["$csvFieldKey"] = $values ;
	}

	public function setAdParamsByFieldValues(array $fieldValues, VidiunYouTubeDistributionProfile $distributionProfile)
	{
		if (!$distributionProfile->enableAdServer)
			return;

		$adTypes = '';
		$delimiter = '|';
		$adValue = $this->getAdvertisingValue($fieldValues,VidiunYouTubeDistributionField::ADVERTISING_INSTREAM_STANDARD,$distributionProfile->instreamStandard);
		if ($this->isAllowedValue($adValue))
			$adTypes = "instream_standard";
		elseif($this->isNotAllowedValue($adValue))
			$adTypes = "!instream_standard";

		$adValue = $this->getAdvertisingValue($fieldValues,VidiunYouTubeDistributionField::ADVERTISING_INSTREAM_TRUEVIEW,$distributionProfile->instreamTrueview);
		if ($this->isAllowedValue($adValue))
			$adTypes .= $delimiter."instream_trueview";
		elseif($this->isNotAllowedValue($adValue))
			$adTypes .= $delimiter."!instream_trueview";

		$adValue = $this->getAdvertisingValue($fieldValues,VidiunYouTubeDistributionField::ADVERTISING_ALLOW_INVIDEO,$distributionProfile->allowInvideo);
		if ($this->isAllowedValue($adValue))
			$adTypes .= $delimiter."invideo_overlay";
		elseif($this->isNotAllowedValue($adValue))
			$adTypes .= $delimiter."!invideo_overlay";

		$adValue = $this->getAdvertisingValue($fieldValues,VidiunYouTubeDistributionField::PRODUCT_LISTING_ADS,$distributionProfile->productListingAds);
		if ($this->isAllowedValue($adValue))
			$adTypes .= $delimiter."product_listing";
		elseif($this->isNotAllowedValue($adValue))
			$adTypes .= $delimiter."!product_listing";

		$adValue = $this->getAdvertisingValue($fieldValues,VidiunYouTubeDistributionField::ADVERTISING_ALLOW_ADSENSE_FOR_VIDEO,$distributionProfile->allowAdsenseForVideo);
		if ($this->isAllowedValue($adValue))
			$adTypes .= $delimiter."display";
		elseif($this->isNotAllowedValue($adValue))
			$adTypes .= $delimiter."!display";

		$adValue = $this->getAdvertisingValue($fieldValues,VidiunYouTubeDistributionField::THIRD_PARTY_ADS,$distributionProfile->thirdPartyAds);
		if ($this->isAllowedValue($adValue))
		{
			$adTypes .= $delimiter."third_party_ads";
			$this->setCsvFieldValueIfHasValue('ad_server_video_id', $fieldValues, VidiunYouTubeDistributionField::THIRD_PARTY_AD_SERVER_VIDEO_ID);
		}
		elseif($this->isNotAllowedValue($adValue))
			$adTypes .= $delimiter."!third_party_ads";

		$this->setCsvFieldValue('ad_types', $adTypes);
	}

	private function getYouTubePolicyMetadataCustomValue($systemName, $fieldName, $partnerId, $entryId)
	{
		$metaDataProfile = MetadataProfilePeer::retrieveBySystemName($systemName, $partnerId);
		if (!$metaDataProfile)
			return null;

		$metadata = MetadataPeer::retrieveByObject($metaDataProfile->getId(), MetadataObjectType::ENTRY, $entryId);
		if (!$metadata)
			return null;

		$key = $metadata->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
		$xml = vFileSyncUtils::file_get_contents($key, true, false);
		$xmlObj = simplexml_load_string($xml);
		if (isset($xmlObj->$fieldName))
			return (string)$xmlObj->$fieldName;
		return null;
	}

	public function appendRightsAdminByFieldValues(array $fieldValues, VidiunYouTubeDistributionProfile $distributionProfile, $entryId)
	{
		$usagePolicy = $this->getPolicyValue($fieldValues, VidiunYouTubeDistributionField::POLICY_COMMERCIAL, $distributionProfile->commercialPolicy);
		if ($usagePolicy == self::METADATA_CUSTOME_USAGE_POLICY_FIELD)
			$usagePolicy = $this->getYouTubePolicyMetadataCustomValue(self::METADATA_PROFILE_YOUTUBE_CUSTOM_USAGE_POLICY_SYSTEM_NAME, self::METADATA_CUSTOME_USAGE_POLICY_FIELD, $distributionProfile->partnerId, $entryId);
		if ($usagePolicy)
			$this->setCsvFieldValue('usage_policy', $usagePolicy);

		$matchPolicy = $this->getPolicyValue($fieldValues, VidiunYouTubeDistributionField::POLICY_UGC, $distributionProfile->ugcPolicy);
		if ($matchPolicy == self::METADATA_CUSTOME_MATCH_POLICY_FIELD)
			$matchPolicy = $this->getYouTubePolicyMetadataCustomValue(self::METADATA_PROFILE_YOUTUBE_CUSTOM_MATCH_POLICY_SYSTEM_NAME, self::METADATA_CUSTOME_MATCH_POLICY_FIELD, $distributionProfile->partnerId, $entryId);
		if ($matchPolicy)
			$this->setCsvFieldValue('match_policy', $matchPolicy);
	}

	public function getCsvMap()
	{
		//get the csv as string to send
		return serialize($this->_csvMap);
	}

	public function getDeleteVideoIds()
	{
		return serialize($this->_csvMap);
	}

	public function getCaptionsCsvMap()
	{
		//get the csv as string to send
		return serialize($this->_captionCsvMap);
	}

	/**
	 * @return string
	 */
	public function getDirectoryName()
	{
		return $this->_directoryName;
	}

	/**
	 * @return string
	 */
	public function getMetadataTempFileName()
	{
		return $this->_metadataTempFileName;
	}

	private function isAllowedValue($value)
	{
		return in_array($value, array('true', 'True', '1'), true);
	}

	private function isNotAllowedValue($value)
	{
		return in_array($value, array('false', 'False', '0'), true);
	}
}