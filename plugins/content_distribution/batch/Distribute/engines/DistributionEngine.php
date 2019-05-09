<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
abstract class DistributionEngine implements IDistributionEngine
{	
	/**
	 * @var int
	 */
	protected $partnerId;
	
	/**
	 * @var string
	 */
	protected $tempDirectory = null;
	
	/**
	 * @param string $interface
	 * @param VidiunDistributionProviderType $providerType
	 * @param VidiunDistributionJobData $data
	 * @return DistributionEngine
	 */
	public static function getEngine($interface, $providerType, VidiunDistributionJobData $data)
	{
		$engine = null;
		if($providerType == VidiunDistributionProviderType::GENERIC)
		{
			$engine = new GenericDistributionEngine();
		}
		else
		{
			$engine = VidiunPluginManager::loadObject($interface, $providerType);
		}
		
		if($engine)
		{
			$engine->setClient();
			$engine->configure($data);
		}
		
		return $engine;
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngine::setClient()
	 */
	public function setClient()
	{
		$this->partnerId = VBatchBase::$vClient->getPartnerId();
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngine::setClient()
	 */
	public function configure()
	{
		$this->tempDirectory = isset(VBatchBase::$taskConfig->params->tempDirectoryPath) ? VBatchBase::$taskConfig->params->tempDirectoryPath : sys_get_temp_dir();
		if (!is_dir($this->tempDirectory)) 
			vFile::fullMkfileDir($this->tempDirectory, 0700, true);
	}

	/**
	 * @param string $entryId
	 * @return VidiunMediaEntry
	 */
	protected function getEntry($partnerId, $entryId)
	{
		VBatchBase::impersonate($partnerId);
		$entry = VBatchBase::$vClient->baseEntry->get($entryId);
		VBatchBase::unimpersonate();
		
		return $entry;
	}

	/**
	 * @param string $flavorAssetIds comma seperated
	 * @return array<VidiunFlavorAsset>
	 */
	protected function getFlavorAssets($partnerId, $flavorAssetIds)
	{
		$filter = new VidiunAssetFilter();
		$filter->idIn = $flavorAssetIds;
		
		try
		{
			VBatchBase::impersonate($entryDistribution->partnerId);
			$flavorAssetsList = VBatchBase::$vClient->flavorAsset->listAction($filter);
			VBatchBase::unimpersonate();
		}
		catch (Exception $e)
		{
			VBatchBase::unimpersonate();
			throw $e;
		}
		
		return $flavorAssetsList->objects;
	}

	/**
	 * @param string $thumbAssetIds comma seperated
	 * @return array<VidiunThumbAsset>
	 */
	protected function getThumbAssets($partnerId, $thumbAssetIds)
	{
		$filter = new VidiunAssetFilter();
		$filter->idIn = $thumbAssetIds;
		
		try
		{
			VBatchBase::impersonate($partnerId);
			$thumbAssetsList = VBatchBase::$vClient->thumbAsset->listAction($filter);
			VBatchBase::unimpersonate();
		}
		catch (Exception $e)
		{
			VBatchBase::unimpersonate();
			throw $e;
		}
		
		return $thumbAssetsList->objects;
	}

	/**
	 * @param string $assetId
	 * @return string url
	 */
	protected function getAssetUrl($assetId)
	{
		$contentDistributionPlugin = VidiunContentDistributionClientPlugin::get(VBatchBase::$vClient);
		return $contentDistributionPlugin->contentDistributionBatch->getAssetUrl($assetId);
	
//		$domain = $this->vidiunClient->getConfig()->serviceUrl;
//		return "$domain/api_v3/service/thumbAsset/action/serve/thumbAssetId/$thumbAssetId";
	}

	/**
	 * @param array<VidiunMetadata> $metadataObjects
	 * @param string $field
	 * @return array|string
	 */
	protected function findMetadataValue(array $metadataObjects, $field, $asArray = false)
	{
		$results = array();
		foreach($metadataObjects as $metadata)
		{
			$xml = new DOMDocument();
			$xml->loadXML($metadata->xml);
			$nodes = $xml->getElementsByTagName($field);
			foreach($nodes as $node)
				$results[] = $node->textContent;
		}
		
		if(!$asArray)
		{
			if(!count($results))
				return null;
				
			if(count($results) == 1)
				return reset($results);
		}
			
		return $results;
	}

	/**
	 * @param string $objectId
	 * @param VidiunMetadataObjectType $objectType
	 * @return array<VidiunMetadata>
	 */
	protected function getMetadataObjects($partnerId, $objectId, $objectType = VidiunMetadataObjectType::ENTRY, $metadataProfileId = null)
	{
		if(!class_exists('VidiunMetadata'))
			return null;
			
		VBatchBase::impersonate($partnerId);
		
		$metadataFilter = new VidiunMetadataFilter();
		$metadataFilter->objectIdEqual = $objectId;
		$metadataFilter->metadataObjectTypeEqual = $objectType;
		$metadataFilter->orderBy = VidiunMetadataOrderBy::CREATED_AT_DESC;
		
		if($metadataProfileId)
			$metadataFilter->metadataProfileIdEqual = $metadataProfileId;
		
		$metadataPager = new VidiunFilterPager();
		$metadataPager->pageSize = 1;
		$metadataListResponse = VBatchBase::$vClient->metadata->listAction($metadataFilter, $metadataPager);
		
		VBatchBase::unimpersonate();
		
		if(!$metadataListResponse->totalCount)
			throw new Exception("No metadata objects found");

		return $metadataListResponse->objects;
	}

	protected function getCaptionContent($captionAssetId)
	{
		VidiunLog::info("Retrieve caption assets content for captionAssetId: [$captionAssetId]");
		try
		{
			$captionClientPlugin = VidiunCaptionClientPlugin::get(VBatchBase::$vClient);
			$captionAssetContentUrl= $captionClientPlugin->captionAsset->serve($captionAssetId);
			return VCurlWrapper::getContent($captionAssetContentUrl);
		}
		catch(Exception $e)
		{
			VidiunLog::info("Can't serve caption asset id [$captionAssetId] " . $e->getMessage());
		}
	}

	protected function getAssetFile($assetId, $directory, $fileName = null)
	{
		VidiunLog::info("Retrieve asset content for assetId: [$assetId]");
		try
		{
			$filePath = $directory;
			if ($fileName)
				 $filePath .= '/'.$fileName;
			else
				$filePath .= '/asset_'.$assetId;

			$assetContentUrl = $this->getAssetUrl($assetId);
			$res = VCurlWrapper::getDataFromFile($assetContentUrl, $filePath, null, true);
			if ($res)
				return $filePath;
		}
		catch(Exception $e)
		{
			VidiunLog::info("Can't serve asset id [$assetId] " . $e->getMessage());
		}
		return null;
	}
}
