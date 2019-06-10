<?php
/**
 * @package plugins.youTubeDistribution
 * @subpackage api.objects
 */
class VidiunYouTubeDistributionJobProviderData extends VidiunConfigurableDistributionJobProviderData
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
	 * @var string
	 */
	public $thumbAssetId;

	/**
	 * @var string
	 */
	public $captionAssetIds;
	
	/**
	 * @var string
	 */
	public $sftpDirectory;
	
	/**
	 * @var string
	 */
	public $sftpMetadataFilename;
	
	/**
	 * @var string
	 */
	public $currentPlaylists;

	/**
	 * @var string
	 */
	public $newPlaylists;

	/**
	 * @var string
	 */
	public $submitXml;

	/**
	 * @var string
	 */
	public $updateXml;

	/**
	 * @var string
	 */
	public $deleteXml;

	/**
	 * @var string
	 */
	public $googleClientId;

	/**
	 * @var string
	 */
	public $googleClientSecret;

	/**
	 * @var string
	 */
	public $googleTokenData;

	/**
	 * @var string
	 */
	public $captionsCsvMap;

	/**
	 * @var string
	 */
	public $submitCsvMap;

	/**
	 * @var string
	 */
	public $updateCsvMap;

	/**
	 * @var string
	 */
	public $deleteVideoIds;

	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
	    parent::__construct($distributionJobData);
	    
		if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunYouTubeDistributionProfile))
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
			$thumbAsset = reset($thumbAssets);
			$syncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			if(vFileSyncUtils::fileSync_exists($syncKey))
			{
				$this->thumbAssetFilePath = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
				$this->thumbAssetId = $thumbAsset->getId();
			}
		}
		
		//Add caption Asset id's
		$this->captionAssetIds = $distributionJobData->entryDistribution->assetIds;
		
		$entryDistributionDb = EntryDistributionPeer::retrieveByPK($distributionJobData->entryDistributionId);
		if ($entryDistributionDb)
			$this->currentPlaylists = $entryDistributionDb->getFromCustomData('currentPlaylists');
		else
			VidiunLog::err('Entry distribution ['.$distributionJobData->entryDistributionId.'] not found');  

		if ($distributionJobData->distributionProfile->feedSpecVersion == YouTubeDistributionFeedSpecVersion::VERSION_1)
			return;
			
		if (is_null($this->fieldValues))
			return;
			//23.5.13 this return is a hack because of bad inheritance of vYouTubeDistributionJobProviderData causing some YouTube distribution 
			//batch jobs to not have fieldValues. it can be removed at some point. 
			
		$videoFilePath = $this->videoAssetFilePath;
		$thumbnailFilePath = $this->thumbAssetFilePath;
		$captionAssetIds = $this->captionAssetIds;

		$feed = null;
		$fieldValues = unserialize($this->fieldValues);
		if ($distributionJobData instanceof VidiunDistributionSubmitJobData)
		{
			if ($distributionJobData->distributionProfile->feedSpecVersion == YouTubeDistributionFeedSpecVersion::VERSION_2)
			{
				$feed = YouTubeDistributionRightsFeedHelper::initializeDefaultSubmitFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $captionAssetIds);
				$this->submitXml = $feed->getXml();
			}
			else
			{
				$feed = YouTubeDistributionCsvFeedHelper::initializeDefaultSubmitFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $captionAssetIds, $distributionJobData->entryDistribution->entryId);
				$this->submitCsvMap = $feed->getCsvMap();
				$this->captionsCsvMap = $feed->getCaptionsCsvMap();
			}

		}
		elseif ($distributionJobData instanceof VidiunDistributionUpdateJobData)
		{
			$remoteIdHandler = YouTubeDistributionRemoteIdHandler::initialize($distributionJobData->remoteId);
			if ($distributionJobData->distributionProfile->feedSpecVersion == YouTubeDistributionFeedSpecVersion::VERSION_2)
			{
				$feed = YouTubeDistributionRightsFeedHelper::initializeDefaultUpdateFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $remoteIdHandler);
				$this->updateXml = $feed->getXml();
			}
			else
			{
				$feed = YouTubeDistributionCsvFeedHelper::initializeDefaultUpdateFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $remoteIdHandler, $distributionJobData->entryDistribution->entryId);
				$this->updateCsvMap = $feed->getCsvMap();
			}

		}
		elseif ($distributionJobData instanceof VidiunDistributionDeleteJobData)
		{
			$remoteIdHandler = YouTubeDistributionRemoteIdHandler::initialize($distributionJobData->remoteId);
			if ($distributionJobData->distributionProfile->feedSpecVersion == YouTubeDistributionFeedSpecVersion::VERSION_2)
			{
				$feed = YouTubeDistributionRightsFeedHelper::initializeDefaultDeleteFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $remoteIdHandler);
				$this->deleteXml = $feed->getXml();
			}
			else
			{
				$feed = YouTubeDistributionCsvFeedHelper::initializeDefaultDeleteFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $remoteIdHandler);
				$this->deleteVideoIds = $feed->getDeleteVideoIds();
			}
		}

		$this->newPlaylists = isset($fieldValues[VidiunYouTubeDistributionField::PLAYLISTS]) ? $fieldValues[VidiunYouTubeDistributionField::PLAYLISTS] : null;
		if ($feed)
		{
			$this->sftpDirectory = $feed->getDirectoryName();
			$this->sftpMetadataFilename = $feed->getMetadataTempFileName();
		}

		$distributionProfileId = $distributionJobData->distributionProfile->id;
		$this->loadGoogleConfig($distributionProfileId);
	}
		
	private static $map_between_objects = array
	(
		"sftpDirectory",
		"sftpMetadataFilename",
		"currentPlaylists",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/**
	 * @param int $distributionProfileId
	 */
	protected function loadGoogleConfig($distributionProfileId)
	{
		$appConfigId = 'youtubepartner'; // config section for configuration/google_auth.ini
		$authConfig = vConf::get($appConfigId, 'google_auth', null);

		$this->googleClientId = isset($authConfig['clientId']) ? $authConfig['clientId'] : null;
		$this->googleClientSecret = isset($authConfig['clientSecret']) ? $authConfig['clientSecret'] : null;
	
		$distributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
		/* @var $distributionProfile YoutubeApiDistributionProfile */

		$tokenData = $distributionProfile->getGoogleOAuth2Data();
		if ($tokenData)
		{
			$this->googleTokenData = json_encode($tokenData);
		}
	}
}
