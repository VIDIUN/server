<?php
/**
 * @package plugins.crossVidiunDistribution
 * @subpackage lib.batch
 */
class CrossVidiunDistributionEngine extends DistributionEngine implements
	IDistributionEngineSubmit,
	IDistributionEngineUpdate,
	IDistributionEngineDelete,
	IVidiunLogger
{

	const DISTRIBUTED_INFO_SOURCE_ID = 'sourceId';
	const DISTRIBUTED_INFO_TARGET_ID = 'targetId';
	const DISTRIBUTED_INFO_SOURCE_VERSION = 'sourceVersion';
	const DISTRIBUTED_INFO_SOURCE_UPDATED_AT = 'sourceUpdatedAt';


	/**
	 * @var VidiunClient
	 */
	protected $targetClient = null;

	/**
	 * @var VidiunClient
	 */
	protected $sourceClient = null;

	/**
	 * @var VidiunCrossVidiunDistributionProfile
	 */
	protected $distributionProfile = null;

	/**
	 * Should distribute caption assets ?
	 * @var bool
	 */
	protected $distributeCaptions = false;

	/**
	 * Should distribute cue points ?
	 * @var bool
	 */
	protected $distributeCuePoints = false;


	/**
	 * Will hold the target entry ID once created
	 * @var string
	 */
	protected $targetEntryId;

	protected $fieldValues = array();


	protected $mapAccessControlIds = array();
	protected $mapConversionProfileIds = array();
	protected $mapMetadataProfileIds = array();
	protected $mapStorageProfileIds = array();
	protected $mapFlavorParamsIds = array();
	protected $mapThumbParamsIds = array();
	protected $mapCaptionParamsIds = array();

	/**
	 * @var CrossVidiunEntryObjectsContainer
	 */
	protected $sourceObjects = null;

	// ------------------------------
	//  initialization methods
	// ------------------------------


	public function __construct()
	{
		$this->targetClient = null;
		$this->distributeCaptions = false;
		$this->distributeCuePoints = false;
		$this->mapAccessControlIds = array();
		$this->mapConversionProfileIds = array();
		$this->mapMetadataProfileIds = array();
		$this->mapStorageProfileIds = array();
		$this->mapFlavorParamsIds = array();
		$this->mapThumbParamsIds = array();
		$this->mapCaptionParamsIds = array();
		$this->fieldValues = array();
		$this->sourceObjects = null;
	}

	/**
	 * Initialize
	 * @param VidiunDistributionJobData $data
	 * @throws Exception
	 */
	protected function init(VidiunDistributionJobData $data)
	{
		// validate objects
		if(!$data->distributionProfile instanceof VidiunCrossVidiunDistributionProfile)
			throw new Exception('Distribution profile must be of type VidiunCrossVidiunDistributionProfile');

		if (!$data->providerData instanceof VidiunCrossVidiunDistributionJobProviderData)
			throw new Exception('Provider data must be of type VidiunCrossVidiunDistributionJobProviderData');

		$this->distributionProfile = $data->distributionProfile;

		// init target vidiun client
		$this->initClients($this->distributionProfile);

		// check for plugins availability
		$this->initPlugins($this->distributionProfile);

		// init mapping arrays
		$this->initMapArrays($this->distributionProfile);

		// init field values
		$this->fieldValues = unserialize($data->providerData->fieldValues);
		if (!$this->fieldValues) {
			$this->fieldValues = array();
		}
	}

	/**
	 * Init a VidiunClient object for the target account
	 * @param VidiunCrossVidiunDistributionProfile $distributionProfile
	 * @throws Exception
	 */
	protected function initClients(VidiunCrossVidiunDistributionProfile $distributionProfile)
	{
		// init source client
		$sourceClientConfig = new VidiunConfiguration($distributionProfile->partnerId);
		$sourceClientConfig->serviceUrl = VBatchBase::$vClient->getConfig()->serviceUrl; // copy from static batch client
		$sourceClientConfig->setLogger($this);
		$this->sourceClient = new VidiunClient($sourceClientConfig);
		$this->sourceClient->setVs(VBatchBase::$vClient->getVs()); // copy from static batch client

		// init target client
		$targetClientConfig = new VidiunConfiguration($distributionProfile->targetAccountId);
		$targetClientConfig->serviceUrl = $distributionProfile->targetServiceUrl;
		$targetClientConfig->setLogger($this);
		$this->targetClient = new VidiunClient($targetClientConfig);
		$vs = $this->targetClient->user->loginByLoginId($distributionProfile->targetLoginId, $distributionProfile->targetLoginPassword, $distributionProfile->targetAccountId, 86400, 'disableentitlement');
		$this->targetClient->setVs($vs);
	}

	/**
	 * Check which server plugins should be used
	 * @param VidiunCrossVidiunDistributionProfile $distributionProfile
	 * @throws Exception
	 */
	protected function initPlugins(VidiunCrossVidiunDistributionProfile $distributionProfile)
	{
		// check if should distribute caption assets
		$this->distributeCaptions = false;
		if ($distributionProfile->distributeCaptions == true)
		{
			if (class_exists('CaptionPlugin') && class_exists('VidiunCaptionClientPlugin') && VidiunPluginManager::getPluginInstance(CaptionPlugin::getPluginName()))
			{
				$this->distributeCaptions = true;
			}
			else
			{
				throw new Exception('Missing CaptionPlugin');
			}
		}

		// check if should distribute cue points
		$this->distributeCuePoints = false;
		if ($distributionProfile->distributeCuePoints == true)
		{
			if (class_exists('CuePointPlugin') && class_exists('VidiunCuePointClientPlugin') && VidiunPluginManager::getPluginInstance(CuePointPlugin::getPluginName()))
			{
				$this->distributeCuePoints = true;
			}
			else
			{
				throw new Exception('Missing CuePointPlugin');
			}
		}
	}


	protected function initMapArrays(VidiunCrossVidiunDistributionProfile $distributionProfile)
	{
		$this->mapAccessControlIds = $this->toKeyValueArray($distributionProfile->mapAccessControlProfileIds);
		$this->mapConversionProfileIds = $this->toKeyValueArray($distributionProfile->mapConversionProfileIds);
		$this->mapMetadataProfileIds = $this->toKeyValueArray($distributionProfile->mapMetadataProfileIds);
		$this->mapStorageProfileIds = $this->toKeyValueArray($distributionProfile->mapStorageProfileIds);
		$this->mapFlavorParamsIds = $this->toKeyValueArray($distributionProfile->mapFlavorParamsIds);
		$this->mapThumbParamsIds = $this->toKeyValueArray($distributionProfile->mapThumbParamsIds);
		$this->mapCaptionParamsIds = $this->toKeyValueArray($distributionProfile->mapCaptionParamsIds);
	}


	// ------------------------------
	//  get existing objects via api
	// ------------------------------



	/**
	 * @param VidiunDistributionJobData $data
	 * @return CrossVidiunEntryObjectsContainer
	 */
	protected function getSourceObjects(VidiunDistributionJobData $data)
	{
		$sourceEntryId = $data->entryDistribution->entryId;
		VBatchBase::impersonate($this->distributionProfile->partnerId);
		$sourceObjects = $this->getEntryObjects(VBatchBase::$vClient, $sourceEntryId, $data);
		VBatchBase::unimpersonate();
		return $sourceObjects;
	}

	/**
	 * Get entry objects for distribution
	 * @param VidiunClient $client
	 * @param string $entryId
	 * @param VidiunDistributionJobData $data
	 * @return CrossVidiunEntryObjectsContainer
	 */
	protected function getEntryObjects(VidiunClient $client, $entryId, VidiunDistributionJobData $data)
	{
		$remoteFlavorAssetContent = $data->distributionProfile->distributeRemoteFlavorAssetContent;
		$remoteThumbAssetContent = $data->distributionProfile->distributeRemoteThumbAssetContent;
		$remoteCaptionAssetContent = $data->distributionProfile->distributeRemoteCaptionAssetContent;

		// get entry
		$entry = $client->baseEntry->get($entryId);

		// get entry's flavor assets chosen for distribution
		$flavorAssets = array();
		if (!empty($data->entryDistribution->flavorAssetIds))
		{
			$flavorAssetFilter = new VidiunFlavorAssetFilter();
			$flavorAssetFilter->idIn = $data->entryDistribution->flavorAssetIds;
			$flavorAssetFilter->entryIdEqual = $entryId;
			try {
				$flavorAssetsList = $client->flavorAsset->listAction($flavorAssetFilter);
				foreach ($flavorAssetsList->objects as $asset)
				{
					$twoLetterCode = languageCodeManager::getLanguageKey($asset->language);
					$obj = languageCodeManager::getObjectFromTwoCode($twoLetterCode);
					$asset->language = !is_null($obj) ? $obj[languageCodeManager::VIDIUN_NAME] : null;

					$flavorAssets[$asset->id] = $asset;
				}
			}
			catch (Exception $e) {
				VidiunLog::err('Cannot get list of flavor assets - '.$e->getMessage());
				throw $e;
			}
		}
		else
		{
			VidiunLog::log('No flavor assets set for distribution!');
		}

		// get flavor assets content
		$flavorAssetsContent = array();
		foreach ($flavorAssets as $flavorAsset)
		{
			$flavorAssetsContent[$flavorAsset->id] = $this->getAssetContentResource($flavorAsset->id, $client->flavorAsset, $remoteFlavorAssetContent);
		}


		// get entry's thumbnail assets chosen for distribution
		$thumbAssets = array();
		$timedThumbAssets = array();
		if (!empty($data->entryDistribution->thumbAssetIds))
		{
			$thumbAssetFilter = new VidiunThumbAssetFilter();
			$thumbAssetFilter->idIn = $data->entryDistribution->thumbAssetIds;
			$thumbAssetFilter->entryIdEqual = $entryId;
			try {
				$thumbAssetsList = $client->thumbAsset->listAction($thumbAssetFilter);
				foreach ($thumbAssetsList->objects as $asset)
				{
					if (isset($asset->cuePointId))
					{
						$timedThumbAssets[$asset->id] = $asset;
					}
					else
					{
						$thumbAssets[$asset->id] = $asset;
					}
				}
			}
			catch (Exception $e) {
				VidiunLog::err('Cannot get list of thumbnail assets - '.$e->getMessage());
				throw $e;
			}
		}
		else
		{
			VidiunLog::log('No thumb assets set for distribution!');
		}

		// get thumb assets content
		$thumbAssetsContent = array();
		foreach ($thumbAssets as $thumbAsset)
		{
			$thumbAssetsContent[$thumbAsset->id] = $this->getAssetContentResource($thumbAsset->id, $client->thumbAsset, $remoteThumbAssetContent);
		}
		foreach ($timedThumbAssets as $thumbAsset)
		{
			$thumbAssetsContent[$thumbAsset->id] = $this->getAssetContentResource($thumbAsset->id, $client->thumbAsset, $remoteThumbAssetContent);
		}

		// get entry's custom metadata objects
		$metadataObjects = array();
		$metadataFilter = new VidiunMetadataFilter();
		$metadataFilter->metadataObjectTypeEqual = VidiunMetadataObjectType::ENTRY;
		$metadataFilter->objectIdEqual = $entryId;
		try {
			$metadataClient = VidiunMetadataClientPlugin::get($client);
			$metadataObjectsList = $metadataClient->metadata->listAction($metadataFilter);
			foreach ($metadataObjectsList->objects as $metadata)
			{
				$metadataObjects[$metadata->id] = $metadata;
			}
		}
		catch (Exception $e) {
			VidiunLog::err('Cannot get list of metadata objects - '.$e->getMessage());
			throw $e;
		}

		// get entry's caption assets
		$captionAssetClient = VidiunCaptionClientPlugin::get($client);
		$captionAssets = array();
		if ($this->distributeCaptions == true)
		{
			$captionAssetFilter = new VidiunCaptionAssetFilter();
			$captionAssetFilter->entryIdEqual = $entryId;
			try {
				$captionAssetsList = $captionAssetClient->captionAsset->listAction($captionAssetFilter);
				foreach ($captionAssetsList->objects as $asset)
				{
					$captionAssets[$asset->id] = $asset;
				}
			}
			catch (Exception $e) {
				VidiunLog::err('Cannot get list of caption assets - '.$e->getMessage());
				throw $e;
			}
		}


		// get caption assets content
		$captionAssetsContent = array();
		foreach ($captionAssets as $captionAsset)
		{
			$captionAssetsContent[$captionAsset->id] = $this->getAssetContentResource($captionAsset->id, $captionAssetClient->captionAsset, $remoteCaptionAssetContent);
		}


		// get entry's cue points
		$cuePoints = array();
		$thumbCuePoints = array();
		if ($this->distributeCuePoints == true)
		{
			$cuePointFilter = new VidiunCuePointFilter();
			$cuePointFilter->entryIdEqual = $entryId;
			try {
				$cuePointClient = VidiunCuePointClientPlugin::get($client);
				$cuePointsList = $cuePointClient->cuePoint->listAction($cuePointFilter);
				foreach ($cuePointsList->objects as $cuePoint)
				{
					/**
					 * @var $cuePoint VidiunCuePoint
					 */
					if ($cuePoint->cuePointType != VidiunCuePointType::THUMB)
						$cuePoints[$cuePoint->id] = $cuePoint;
					else
						$thumbCuePoints[$cuePoint->id] = $cuePoint;
				}
			}
			catch (Exception $e) {
				VidiunLog::err('Cannot get list of cue points - '.$e->getMessage());
				throw $e;
			}
		}

		$entryObjects = new CrossVidiunEntryObjectsContainer();
		$entryObjects->entry = $entry;
		$entryObjects->metadataObjects = $metadataObjects;
		$entryObjects->flavorAssets = $flavorAssets;
		$entryObjects->flavorAssetsContent = $flavorAssetsContent;
		$entryObjects->thumbAssets = $thumbAssets;
		$entryObjects->timedThumbAssets = $timedThumbAssets;
		$entryObjects->thumbAssetsContent = $thumbAssetsContent;
		$entryObjects->captionAssets = $captionAssets;
		$entryObjects->captionAssetsContent = $captionAssetsContent;
		$entryObjects->cuePoints = $cuePoints;
		$entryObjects->thumbCuePoints = $thumbCuePoints;

		return $entryObjects;
	}


	/**
	 * @return VidiunContentResource content resource for the given asset in the target account
	 * @param string $assetId
	 * @param VidiunServiceBase $assetService
	 * @param bool $remote
	 */
	protected function getAssetContentResource($assetId, VidiunServiceBase $assetService, $remote)
	{
		$contentResource = null;

		if ($remote)
		{
			// get remote resource

			$contentResource = new VidiunRemoteStorageResources();
			$contentResource->resources = array();

			$remotePaths = $assetService->getRemotePaths($assetId);
			$remotePaths = $remotePaths->objects;
			foreach ($remotePaths as $remotePath)
			{
				/* @var $remotePath VidiunRemotePath */
				$res = new VidiunRemoteStorageResource();
				if (!isset($this->mapStorageProfileIds[$remotePath->storageProfileId]))
				{
					throw new Exception('Cannot map storage profile ID ['.$remotePath->storageProfileId.']');
				}
				$res->storageProfileId = $this->mapStorageProfileIds[$remotePath->storageProfileId];
				$res->url = $remotePath->uri;

				$contentResource->resources[] = $res;
			}
		}
		else
		{
			// get local resource
			$contentResource = new VidiunUrlResource();
			$contentResource->url = $this->getAssetUrlByAssetId($assetId, $assetService);
		}
		return $contentResource;
	}
	
	protected function getAssetUrlByAssetId($assetId, $assetService)
	{
		if ( $assetService instanceof VidiunFlavorAssetService ) {
			$options = new VidiunFlavorAssetUrlOptions();
			$options->fileName = $assetId;
			return $assetService->getUrl($assetId, null, false, $options);
		}
		
		return $assetService->getUrl($assetId);
	}

	// -----------------------------------------------
	//  methods to transform source to target objects
	// -----------------------------------------------

	/**
	 * Transform source entry object to a target object ready for insert/update
	 * @param VidiunBaseEntry $sourceEntry
	 * @param bool $forUpdate
	 * @return VidiunBaseEntry
	 */
	protected function transformEntry(VidiunBaseEntry $sourceEntry, $forUpdate = false)
	{
		// remove readonly/insertonly parameters
		/* @var $targetEntry VidiunBaseEntry */
		$targetEntry = $this->copyObjectForInsertUpdate($sourceEntry);

		// switch to target account's object ids
		if ($forUpdate)
		{
			$targetEntry = $this->removeInsertOnly($targetEntry);
			$targetEntry->conversionProfileId = null;
		}
		else
		{
			if (!is_null($sourceEntry->conversionProfileId))
			{
				if (!isset($this->mapConversionProfileIds[$sourceEntry->conversionProfileId]))
				{
					throw new Exception('Cannot map conversion profile ID ['.$sourceEntry->conversionProfileId.']');
				}
				$targetEntry->conversionProfileId = $this->mapConversionProfileIds[$sourceEntry->conversionProfileId];
			}
		}

		if (!is_null($sourceEntry->accessControlId))
		{
			if (!isset($this->mapAccessControlIds[$sourceEntry->accessControlId]))
			{
				throw new Exception('Cannot map access control ID ['.$sourceEntry->accessControlId.']');
			}
			$targetEntry->accessControlId = $this->mapAccessControlIds[$sourceEntry->accessControlId];
		}

		// transform metadata according to fields configuration
		$targetEntry->name = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_NAME);
		$targetEntry->description = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_DESCRIPTION);
		$targetEntry->userId = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_USER_ID);
		$targetEntry->tags = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_TAGS);
		$targetEntry->categories = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_CATEGORIES);
		$targetEntry->categoriesIds = null;
		$targetEntry->partnerData = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_PARTNER_DATA);
		$targetEntry->startDate = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_START_DATE);
		$targetEntry->endDate = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_END_DATE);
		$targetEntry->referenceId = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_REFERENCE_ID);
		$targetEntry->licenseType = $this->getValueForField(VidiunCrossVidiunDistributionField::BASE_ENTRY_LICENSE_TYPE);
		if (isset($targetEntry->conversionQuality)) {
			$targetEntry->conversionQuality = null;
		}

		// turn problematic empty fields to null
		if (!$targetEntry->startDate) { $targetEntry->startDate = null; }
		if (!$targetEntry->endDate) { $targetEntry->endDate = null; }
		if (!$targetEntry->referenceId) { $targetEntry->referenceId = null; }

		// return transformed entry object
		return $targetEntry;
	}


	/**
	 * Transform source metadata objects to target objects ready for insert/update
	 * @param array<VidiunMetadata> $sourceMetadatas
	 * @return array<VidiunMetadata>
	 */
	protected function transformMetadatas(array $sourceMetadatas)
	{
		if (!count($sourceMetadatas)) {
			return array();
		}

		$targetMetadatas = array();
		foreach ($sourceMetadatas as $sourceMetadata)
		{
			/* @var $sourceMetadata VidiunMetadata */

			if (!isset($this->mapMetadataProfileIds[$sourceMetadata->metadataProfileId]))
			{
				throw new Exception('Cannot map metadata profile ID ['.$sourceMetadata->metadataProfileId.']');
			}
			if($this->mapMetadataProfileIds[$sourceMetadata->metadataProfileId] == 0)
			{
				continue;
			}

			$targetMetadata = new VidiunMetadata();
			$targetMetadata->metadataProfileId = $this->mapMetadataProfileIds[$sourceMetadata->metadataProfileId];

			$xsltStr = $this->distributionProfile->metadataXslt;
			if (!is_null($xsltStr) && strlen($xsltStr) > 0)
			{
				$targetMetadata->xml = $this->transformXml($sourceMetadata->xml, $xsltStr);
			}
			else
			{
				$targetMetadata->xml = $sourceMetadata->xml;
			}

			$targetMetadatas[$sourceMetadata->id] = $targetMetadata;
		}

		return $targetMetadatas;
	}


	/**
	 * Transform source flavor assets to target objects ready for insert/update
	 * @param array<VidiunFlavorAsset> $sourceFlavorAssets
	 * @return array<VidiunFlavorAsset>
	 */
	protected function transformFlavorAssets(array $sourceFlavorAssets)
	{
		return $this->transformAssets($sourceFlavorAssets, $this->mapFlavorParamsIds, 'flavorParamsId');
	}

	/**
	 * Transform source thumbnail assets to target objects ready for insert/update
	 * @param array<VidiunThumbAsset> $sourceThumbAssets
	 * @return array<VidiunThumbAsset>
	 */
	protected function transformThumbAssets(array $sourceThumbAssets)
	{
		return $this->transformAssets($sourceThumbAssets, $this->mapThumbParamsIds, 'thumbParamsId');
	}

	/**
	 * Transform source caption assets to target objects ready for insert/update
	 * @param array<VidiunCaptionAsset> $sourceCaptionAssets
	 * @return array<VidiunCaptionAsset>
	 */
	protected function transformCaptionAssets(array $sourceCaptionAssets)
	{
		return $this->transformAssets($sourceCaptionAssets, $this->mapCaptionParamsIds, 'captionParamsId');
	}

	/**
	 *
	 * Transform source assets to target assets ready for insert/update
	 * @param array<VidiunAsset> $sourceAssets
	 * @param array $mapParams
	 * @param string $paramsFieldName
	 * @return array<VidiunAsset>
	 */
	protected function transformAssets(array $sourceAssets, array $mapParams, $paramsFieldName)
	{
		if (!count($sourceAssets)) {
			return array();
		}

		$targetAssets = array();
		foreach ($sourceAssets as $sourceAsset)
		{
			// remove readonly/insertonly parameters
			$targetAsset = $this->copyObjectForInsertUpdate($sourceAsset);

			// switch to target params id if defined, else leave same as source
			if (isset($mapParams[$sourceAsset->{$paramsFieldName}]))
			{
				$targetAsset->{$paramsFieldName} = $mapParams[$sourceAsset->{$paramsFieldName}];
			}
			else
			{
				$targetAsset->{$paramsFieldName} = $sourceAsset->{$paramsFieldName};
			}

			$targetAssets[$sourceAsset->id] = $targetAsset;
		}

		return $targetAssets;
	}


	/**
	 * Transform source cue points to target objects ready for insert/update
	 * @param array<VidiunCuePoint> $sourceCuePoints
	 * @return array<VidiunCuePoint>
	 */
	protected function transformCuePoints(array $sourceCuePoints)
	{
		if (!count($sourceCuePoints)) {
			return array();
		}

		$targetCuePoints = array();
		foreach ($sourceCuePoints as $sourceCuePoint)
		{
			// remove readonly/insertonly parameters
			$targetCuePoint = $this->copyObjectForInsertUpdate($sourceCuePoint);
			$targetCuePoints[$sourceCuePoint->id] = $targetCuePoint;
		}

		return $targetCuePoints;
	}


	protected function transformAssetContent(array $assetContent)
	{
		if (!count($assetContent)) {
			return array();
		}

		$targetAssetContent = null;

	}


	/**
	 * Transform source objects to target objects ready for insert/update
	 * @param CrossVidiunEntryObjectsContainer $sourceObjects
	 * @param bool $forUpdate
	 * @return CrossVidiunEntryObjectsContainer target objects
	 */
	protected function transformSourceToTarget(CrossVidiunEntryObjectsContainer $sourceObjects, $forUpdate = false)
	{
		$targetObjects = new CrossVidiunEntryObjectsContainer();
		$targetObjects->entry = $this->transformEntry($sourceObjects->entry, $forUpdate); // basic entry object
		$targetObjects->metadataObjects = $this->transformMetadatas($sourceObjects->metadataObjects); // metadata objects
		$targetObjects->flavorAssets = $this->transformFlavorAssets($sourceObjects->flavorAssets); // flavor assets
		$targetObjects->flavorAssetsContent = $sourceObjects->flavorAssetsContent; // flavor assets content - already transformed
		$targetObjects->thumbAssets = $this->transformThumbAssets($sourceObjects->thumbAssets); // thumb assets
		$targetObjects->timedThumbAssets = $this->transformThumbAssets($sourceObjects->timedThumbAssets); // timed thumb assets
		$targetObjects->thumbAssetsContent = $sourceObjects->thumbAssetsContent; // thumb assets content - already transformed
		if ($this->distributeCaptions)
		{
			$targetObjects->captionAssets = $this->transformCaptionAssets($sourceObjects->captionAssets); // caption assets
			$targetObjects->captionAssetsContent = $sourceObjects->captionAssetsContent; // caption assets content - already transformed
		}
		if ($this->distributeCuePoints)
		{
			$targetObjects->cuePoints = $this->transformCuePoints($sourceObjects->cuePoints); // cue points
			$targetObjects->thumbCuePoints  = $this->transformCuePoints($sourceObjects->thumbCuePoints); // thumb cue points
		}
		return $targetObjects;
	}



	// ------------------------------------------------------------
	//  special methods to extract object arguments for add/update
	// ------------------------------------------------------------

	/**
	 * @return array of arguments that should be passed to metadata->update api action
	 * @param string $existingObjId
	 * @param VidiunMetadata $newObj
	 */
	protected function getMetadataUpdateArgs($existingObjId, VidiunMetadata $newObj)
	{
		return array(
			$existingObjId,
			$newObj->xml
		);
	}

	/**
	 * @return array of arguments that should be passed to metadata->add api action
	 * @param VidiunMetadata $newObj
	 */
	protected function getMetadataAddArgs(VidiunMetadata $newObj)
	{
		return array(
			$newObj->metadataProfileId,
			VidiunMetadataObjectType::ENTRY,
			$newObj->objectId,
			$newObj->xml
		);
	}

	/**
	 * @return array of arguments that should be passed to cuepoint->add api action
	 * @param VidiunCuePoint $newObj
	 */
	protected function getCuePointAddArgs(VidiunCuePoint $newObj)
	{
		return array(
			$newObj
		);
	}


	// ----------------------
	//  distribution actions
	// ----------------------

	/**
	 * Fill provider data with map of distributed objects
	 * @param VidiunDistributionJobData $data
	 * @param CrossVidiunEntryObjectsContainer $syncedObjects
	 */
	protected function getDistributedMap(VidiunDistributionJobData $data, CrossVidiunEntryObjectsContainer $syncedObjects)
	{
		$data->providerData->distributedFlavorAssets = $this->getDistributedMapForObjects($this->sourceObjects->flavorAssets, $syncedObjects->flavorAssets);

		$data->providerData->distributedThumbAssets = $this->getDistributedMapForObjects($this->sourceObjects->thumbAssets, $syncedObjects->thumbAssets);
		$data->providerData->distributedTimedThumbAssets = $this->getDistributedMapForObjects($this->sourceObjects->timedThumbAssets, $syncedObjects->timedThumbAssets);

		$data->providerData->distributedMetadata = $this->getDistributedMapForObjects($this->sourceObjects->metadataObjects, $syncedObjects->metadataObjects);
		$data->providerData->distributedCaptionAssets = $this->getDistributedMapForObjects($this->sourceObjects->captionAssets, $syncedObjects->captionAssets);

		$data->providerData->distributedCuePoints = $this->getDistributedMapForObjects($this->sourceObjects->cuePoints, $syncedObjects->cuePoints);
		$data->providerData->distributedThumbCuePoints = $this->getDistributedMapForObjects($this->sourceObjects->thumbCuePoints, $syncedObjects->thumbCuePoints);

		return $data;
	}


	/**
	 * Get distributed map for the given objects
	 * @param unknown_type $sourceObjects
	 * @param unknown_type $syncedObjects
	 */
	protected function getDistributedMapForObjects($sourceObjects, $syncedObjects)
	{
		$info = array();
		foreach ($syncedObjects as $sourceId => $targetObj)
		{
			$sourceObj = $sourceObjects[$sourceId];
			$objInfo = array();
			$objInfo[self::DISTRIBUTED_INFO_SOURCE_ID] = $sourceId;
			$objInfo[self::DISTRIBUTED_INFO_TARGET_ID] = $targetObj->id;
			$objInfo[self::DISTRIBUTED_INFO_SOURCE_VERSION] = $sourceObj->version;
			$objInfo[self::DISTRIBUTED_INFO_SOURCE_UPDATED_AT] = $sourceObj->updatedAt;

			$info[$sourceId] = $objInfo;
		}
		return serialize($info);
	}



	/**
	 * Sync objects between the source and target accounts
	 * @param VidiunServiceBase $targetClientService API service for the current object type
	 * @param array $newObjects array of target objects that should be added/updated
	 * @param array $sourceObjects array of source objects
	 * @param array $distributedMap array of information about previously distributed objects
	 * @param string $targetEntryId
	 * @param string $addArgsFunc special function to extract arguments for the ADD api action
	 * @param string $updateArgsFunc special function to extract arguments for the UPDATE api action
	 * @return array of the synced objects
	 */
	protected function syncTargetEntryObjects(VidiunServiceBase $targetClientService, $newObjects, $sourceObjects, $distributedMap, $targetEntryId, $addArgsFunc = null, $updateArgsFunc = null)
	{
		$syncedObjects = array();
		$distributedMap = empty($distributedMap) ? array() : unserialize($distributedMap);

		// walk through all new target objects and add/update on target as necessary
		if (count($newObjects))
		{
			VidiunLog::info('Syncing target objects for source IDs ['.implode(',', array_keys($newObjects)).']');
			foreach ($newObjects as $sourceObjectId => $targetObject)
			{
				if (is_array($distributedMap) && array_key_exists($sourceObjectId, $distributedMap))
				{
					// this object was previously distributed
					VidiunLog::info('Source object id ['.$sourceObjectId.'] was previously distributed');

					$lastDistributedUpdatedAt = isset($distributedMap[$sourceObjectId][self::DISTRIBUTED_INFO_SOURCE_UPDATED_AT]) ? $distributedMap[$sourceObjectId][self::DISTRIBUTED_INFO_SOURCE_UPDATED_AT] : null;
					$currentSourceUpdatedAt = isset($sourceObjects[$sourceObjectId]->updatedAt)	? $sourceObjects[$sourceObjectId]->updatedAt : null;

					$targetObjectId = isset($distributedMap[$sourceObjectId][self::DISTRIBUTED_INFO_TARGET_ID]) ? $distributedMap[$sourceObjectId][self::DISTRIBUTED_INFO_TARGET_ID] : null;
					if (is_null($targetObjectId))
					{
						throw new Exception('Missing previously distributed target object id for source id ['.$sourceObjectId.']');
					}

					if (!is_null($lastDistributedUpdatedAt) && !is_null($currentSourceUpdatedAt) && $currentSourceUpdatedAt <= $lastDistributedUpdatedAt)
					{
						// object wasn't updated since last distributed - just return existing info
						VidiunLog::info('No need to re-distributed object since it was not updated since last distribution - returning dummy object with target id ['.$targetObjectId.']');
						$targetObject->id = $targetObjectId;
						$syncedObjects[$sourceObjectId] = $targetObject;
					}
					else
					{
						// should update existing target object
						$targetObjectForUpdate = $this->removeInsertOnly($targetObject);
						$updateArgs = null;
						if (is_null($updateArgsFunc)) {
							$updateArgs = array($targetObjectId, $targetObjectForUpdate);
						}
						else {
							$updateArgs = call_user_func_array(array($this, $updateArgsFunc), array($targetObjectId, $targetObjectForUpdate));
						}
						$syncedObjects[$sourceObjectId] = call_user_func_array(array($targetClientService, 'update'), $updateArgs);
					}

					unset($distributedMap[$sourceObjectId]);
				}
				else
				{
					// this object was not previously distributed - should add new target object
					$addArgs = null;
					if (is_null($addArgsFunc)) {
						$addArgs = array($targetEntryId, $targetObject);
					}
					else {
						$addArgs = call_user_func_array(array($this, $addArgsFunc), array($targetObject));
					}

					$syncedObjects[$sourceObjectId] = call_user_func_array(array($targetClientService, 'add'), $addArgs);
				}
			}
		}

		// check if previously distributed objects should be deleted from the target account
		if (count($distributedMap))
		{
			VidiunLog::info('Deleting target objects that were deleted in source with IDs ['.implode(',', array_keys($distributedMap)).']');
			foreach ($distributedMap as $sourceId => $objInfo)
			{
				// delete from target account
				$targetId = isset($objInfo[self::DISTRIBUTED_INFO_TARGET_ID]) ? $objInfo[self::DISTRIBUTED_INFO_TARGET_ID] : null;
				VidiunLog::info('Deleting previously distributed source object id ['.$sourceId.'] target object id ['.$targetId.']');
				if (is_null($targetId))
				{
					throw new Exception('Missing previously distributed target object id for source id ['.$sourceId.']');
				}
				try {
					$targetClientService->delete($targetId);
				}
				catch (Exception $e)
				{
					$acceptableErrorCodes = array(
						'FLAVOR_ASSET_ID_NOT_FOUND',
						'THUMB_ASSET_ID_NOT_FOUND',
						'INVALID_OBJECT_ID',
						'CAPTION_ASSET_ID_NOT_FOUND',
						'INVALID_CUE_POINT_ID',
					);
					if (in_array($e->getCode(), $acceptableErrorCodes))
					{
						VidiunLog::warning('Object with id ['.$targetId.'] is already deleted - ignoring exception');
					}
					else
					{
						throw $e;
					}
				}

			}
		}

		return $syncedObjects;
	}


	protected function syncAssetsContent(VidiunServiceBase $targetClientService, $targetAssetsContent, $targetAssets, $distributedMap, $sourceAssets)
	{
		$distributedMap = empty($distributedMap) ? array() : unserialize($distributedMap);

		foreach ($targetAssetsContent as $sourceAssetId => $targetAssetContent)
		{
			$targetAssetId = isset($targetAssets[$sourceAssetId]->id) ? $targetAssets[$sourceAssetId]->id : null;
			if (is_null($targetAssetId))
			{
				throw new Exception('Missing target id of source asset id ['.$sourceAssetId.']');
			}

			$currentSourceVersion = isset($sourceAssets[$sourceAssetId]->version) ? $sourceAssets[$sourceAssetId]->version : null;
			$lastDistributedSourceVersion = isset($distributedMap[$sourceAssetId][self::DISTRIBUTED_INFO_SOURCE_VERSION]) ? $distributedMap[$sourceAssetId][self::DISTRIBUTED_INFO_SOURCE_VERSION] : null;

			if (!is_null($currentSourceVersion) && !is_null($lastDistributedSourceVersion) && $currentSourceVersion <= $lastDistributedSourceVersion)
			{
				VidiunLog::info('No need to update content of source asset id ['.$sourceAssetId.'] target id ['.$targetAssetId.'] since it was not updated since last distribution');
			}
			else
			{
				VidiunLog::info('Updating content for source asset id ['.$sourceAssetId.'] target id ['.$targetAssetId.']');
				$targetClientService->setContent($targetAssetId, $targetAssetContent);
			}
		}
	}

	/**
	 * Sync target objects
	 * @param VidiunDistributionJobData $jobData
	 * @param CrossVidiunEntryObjectsContainer $targetObjects
	 */
	protected function sync(VidiunDistributionJobData $jobData, CrossVidiunEntryObjectsContainer $targetObjects)
	{
		$syncedObjects = new CrossVidiunEntryObjectsContainer();

		$targetEntryId = $jobData->remoteId;

		// add/update entry
		if ($targetEntryId)
		{
			// update entry
			VidiunLog::info('Updating target entry id ['.$targetEntryId.']');
			$syncedObjects->entry = $this->targetClient->baseEntry->update($targetEntryId, $targetObjects->entry);
		}
		else
		{
			// add entry
			$syncedObjects->entry = $this->targetClient->baseEntry->add($targetObjects->entry);
			$targetEntryId = $syncedObjects->entry->id;
			VidiunLog::info('New target entry added with id ['.$targetEntryId.']');
		}
		$this->targetEntryId = $targetEntryId;

		// sync metadata objects
		foreach ($targetObjects->metadataObjects as $metadataObj)
		{
			/* @var $metadataObj VidiunMetadata */
			$metadataObj->objectId = $targetEntryId;
		}
		$targetMetadataClient = VidiunMetadataClientPlugin::get($this->targetClient);
		$syncedObjects->metadataObjects = $this->syncTargetEntryObjects(
			$targetMetadataClient->metadata,
			$targetObjects->metadataObjects,
			$this->sourceObjects->metadataObjects,
			$jobData->providerData->distributedMetadata,
			$targetEntryId,
			'getMetadataAddArgs',
			'getMetadataUpdateArgs'
		);


		// sync flavor assets
		$syncedObjects->flavorAssets = $this->syncTargetEntryObjects(
			$this->targetClient->flavorAsset,
			$targetObjects->flavorAssets,
			$this->sourceObjects->flavorAssets,
			$jobData->providerData->distributedFlavorAssets,
			$targetEntryId
		);


		// sync flavor content
		$this->syncAssetsContent(
			$this->targetClient->flavorAsset,
			$targetObjects->flavorAssetsContent,
			$syncedObjects->flavorAssets,
			$jobData->providerData->distributedFlavorAssets,
			$this->sourceObjects->flavorAssets
		);

		// sync thumbnail assets
		$syncedObjects->thumbAssets = $this->syncTargetEntryObjects(
			$this->targetClient->thumbAsset,
			$targetObjects->thumbAssets,
			$this->sourceObjects->thumbAssets,
			$jobData->providerData->distributedThumbAssets,
			$targetEntryId
		);

		// sync caption assets
		if ($this->distributeCaptions)
		{
			$targetCaptionClient = VidiunCaptionClientPlugin::get($this->targetClient);
			$syncedObjects->captionAssets = $this->syncTargetEntryObjects(
				$targetCaptionClient->captionAsset,
				$targetObjects->captionAssets,
				$this->sourceObjects->captionAssets,
				$jobData->providerData->distributedCaptionAssets,
				$targetEntryId
			);


			// sync caption content
			$this->syncAssetsContent(
				$targetCaptionClient->captionAsset,
				$targetObjects->captionAssetsContent,
				$syncedObjects->captionAssets,
				$jobData->providerData->distributedCaptionAssets,
				$this->sourceObjects->captionAssets
			);
		}


		// sync cue points
		if ($this->distributeCuePoints)
		{
			foreach ($targetObjects->cuePoints as $cuePoint)
			{
				/* @var $cuePoint VidiunCuePoint */
				$cuePoint->entryId = $targetEntryId;
			}
			$targetCuePointClient = VidiunCuePointClientPlugin::get($this->targetClient);
			$syncedObjects->cuePoints = $this->syncTargetEntryObjects(
				$targetCuePointClient->cuePoint,
				$targetObjects->cuePoints,
				$this->sourceObjects->cuePoints,
				$jobData->providerData->distributedCuePoints,
				$targetEntryId,
				'getCuePointAddArgs'
			);

			$distributedThumbCuePointsMap = empty($jobData->providerData->distributedThumbCuePoints) ? array() : unserialize($jobData->providerData->distributedThumbCuePoints);
			$distributedTimedThumbAssetsMap = empty($jobData->providerData->distributedTimedThumbAssets) ? array() : unserialize($jobData->providerData->distributedTimedThumbAssets);
			foreach ($targetObjects->thumbCuePoints as $id => $thumbCuePoint)
			{
				$thumbCuePoint->entryId = $targetEntryId;
				
				//Clear cuePoint assetId only if it was previously distributed but its associated timedThumbASset was not.  
				if(isset($distributedThumbCuePointsMap[$id])
						&& !isset($distributedTimedThumbAssetsMap[$thumbCuePoint->assetId]))
					$thumbCuePoint->assetId = "";
				else
					$thumbCuePoint->assetId = null;
			}
			
			$targetCuePointClient = VidiunCuePointClientPlugin::get($this->targetClient);
			$syncedObjects->thumbCuePoints = $this->syncTargetEntryObjects(
				$targetCuePointClient->cuePoint,
				$targetObjects->thumbCuePoints,
				$this->sourceObjects->thumbCuePoints,
				$jobData->providerData->distributedThumbCuePoints,
				$targetEntryId,
				'getCuePointAddArgs'
			);

			foreach ($targetObjects->timedThumbAssets as $timedThumbAsset)
			{
				$timedThumbAsset->cuePointId = $syncedObjects->thumbCuePoints[$timedThumbAsset->cuePointId]->id;
			}

			// sync Timed thumbnail assets
			$syncedObjects->timedThumbAssets = $this->syncTargetEntryObjects(
				$this->targetClient->thumbAsset,
				$targetObjects->timedThumbAssets,
				$this->sourceObjects->timedThumbAssets,
				$jobData->providerData->distributedTimedThumbAssets,
				$targetEntryId
			);
		}

		// sync thumbnail content
		$this->syncAssetsContent(
			$this->targetClient->thumbAsset,
			$targetObjects->thumbAssetsContent,
			array_merge($syncedObjects->thumbAssets,$syncedObjects->timedThumbAssets),
			$jobData->providerData->distributedThumbAssets,
			$this->sourceObjects->thumbAssets
		);

		return $syncedObjects;
	}


	/* (non-PHPdoc)
     * @see IDistributionEngineSubmit::submit()
     */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		// initialize
		$this->init($data);

		try {
			// get source entry objects
			$this->sourceObjects = $this->getSourceObjects($data);

			// transform source objects to target objects ready for insert
			$targetObjects = $this->transformSourceToTarget($this->sourceObjects, false);

			// add objects to target account
			$addedTargetObjects = $this->sync($data, $targetObjects);

			// save target entry id
			$data->remoteId = $addedTargetObjects->entry->id;

			// get info about distributed objects
			$data = $this->getDistributedMap($data, $addedTargetObjects);

			// all done - no need for closer
		}
		catch (Exception $e)
		{
			// if a new target entry was created - delete it before failing distribution
			if ($this->targetEntryId)
			{
				VidiunLog::info('Deleting partial new target entry ['.$this->targetEntryId.']');
				// delete entry from target account - may throw an exception
				try {
					$deleteResult = $this->targetClient->baseEntry->delete($this->targetEntryId);
				}
				catch (Exception $ignoredException)
				{
					VidiunLog::err('Failed deleting partial entry ['.$this->targetEntryId.'] - '.$ignoredException->getMessage());
				}
			}

			// delete original exception
			throw $e;
		}

		return true;
	}



	/* (non-PHPdoc)
     * @see IDistributionEngineUpdate::update()
     */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		// initialize
		$this->init($data);

		// cannot update if remoteId is missing
		$targetEntryId = $data->remoteId;
		if (!$targetEntryId) {
			throw new Exception('Cannot delete remote entry - remote entry ID is empty');
		}

		// get source entry objects
		$this->sourceObjects = $this->getSourceObjects($data);

		// transform source objects to target objects ready for update
		$targetObjects = $this->transformSourceToTarget($this->sourceObjects, true);

		// update objects on the target account
		$updatedTargetObjects = $this->sync($data, $targetObjects);

		// get info about distributed objects
		$data = $this->getDistributedMap($data, $updatedTargetObjects);

		// all done - no need for closer
		return true;
	}



	/* (non-PHPdoc)
     * @see IDistributionEngineDelete::delete()
     */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		// initialize
		$this->init($data);

		// cannot delete if remoteId is missing
		$targetEntryId = $data->remoteId;
		if (!$targetEntryId) {
			throw new Exception('Cannot delete remote entry - remote entry ID is empty');
		}

		// delete entry from target account - may throw an exception
		$deleteResult = $this->targetClient->baseEntry->delete($targetEntryId);

		// all done - no need for closer
		return true;
	}



	// ----------------
	//  helper methods
	// ----------------

	/**
	 * Copy an object for later inserting/updating through the API
	 * @param unknown_type $sourceObject
	 */
	protected function copyObjectForInsertUpdate ($sourceObject)
	{
		$reflect = new ReflectionClass($sourceObject);
		$props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		$newObjectClass = get_class($sourceObject);
		$newObject = new $newObjectClass;
		foreach ($props as $prop)
		{
			$docComment = $prop->getDocComment();
			$propReadOnly = preg_match("/\\@readonly/i", $docComment);
			$deprecated = preg_match("/\\DEPRECATED/i", $docComment);

			$copyProperty = !$deprecated && !$propReadOnly && $prop->name!="displayInSearch";

			if ($copyProperty) {
				$propertyName = $prop->name;
				$newObject->{$propertyName} = $sourceObject->{$propertyName};
			}
		}
		return $newObject;
	}


	/**
	 * Set to 'null' parameters marked as @insertonly
	 * @param $object
	 */
	protected function removeInsertOnly($object)
	{
		$reflect = new ReflectionClass($object);
		$props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop)
		{
			$docComment = $prop->getDocComment();
			$propInsertOnly = preg_match("/\\@insertonly/i", $docComment);

			if ($propInsertOnly) {
				$propertyName = $prop->name;
				$object->{$propertyName} = null;
			}
		}
		return $object;
	}

	/**
	 * @param string $fieldName
	 * @return value for field from $this->fieldValues, or null if no value defined
	 */
	protected function getValueForField($fieldName)
	{
		if (isset($this->fieldValues[$fieldName])) {
			return $this->fieldValues[$fieldName];
		}
		return null;
	}


	protected function toKeyValueArray($apiKeyValueArray)
	{
		$keyValueArray = array();
		if (count($apiKeyValueArray))
		{
			foreach($apiKeyValueArray as $keyValueObj)
			{
				/* @var $keyValueObj VidiunKeyValue */
				$keyValueArray[$keyValueObj->key] = $keyValueObj->value;
			}
		}
		return $keyValueArray;
	}


	/**
	 * Transform XML using XSLT
	 * @param string $xmlStr
	 * @param string $xslStr
	 * @return string the result XML
	 */
	protected function transformXml($xmlStr, $xslStr)
	{
		$xmlObj = new DOMDocument();
		if (!$xmlObj->loadXML($xmlStr))
		{
			throw new Exception('Error loading source XML');
		}

		$xslObj = new DOMDocument();
		if(!$xslObj->loadXML($xslStr))
		{
			throw new Exception('Error loading XSLT');
		}

		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions(vXml::getXslEnabledPhpFunctions());
		$proc->importStyleSheet($xslObj);

		$resultXmlObj = $proc->transformToDoc($xmlObj);
		if (!$resultXmlObj)
		{
			throw new Exception('Error transforming XML');
			return null;
		}

		$resultXmlStr = $resultXmlObj->saveXML();
		return $resultXmlStr;
	}

	function log($message)
	{
		VidiunLog::log($message);
	}

}