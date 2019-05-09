<?php
/**
 * @package plugins.contentDistributionBulkUploadXml
 */
class ContentDistributionBulkUploadXmlEnginePlugin extends VidiunPlugin implements IVidiunPending, IVidiunBulkUploadXmlHandler, IVidiunConfigurator
{
	const PLUGIN_NAME = 'contentDistributionBulkUploadXmlEngine';
	
	const BULK_UPLOAD_XML_VERSION_MAJOR = 1;
	const BULK_UPLOAD_XML_VERSION_MINOR = 0;
	const BULK_UPLOAD_XML_VERSION_BUILD = 0;
	
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 1;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 1;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;
	
	/**
	 * @var array<string, int> of distribution profiles by their system name
	 */
	private $distributionProfilesNames = null;
	
	/**
	 * @var array<string, int> of distribution profiles by their provider name
	 */
	private $distributionProfilesProviders = null;
	
	/**
	 * @var BulkUploadEngineXml
	 */
	private $xmlBulkUploadEngine = null;
	
	/**
	 * @return string
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$bulkUploadXmlVersion = new VidiunVersion(
			self::BULK_UPLOAD_XML_VERSION_MAJOR,
			self::BULK_UPLOAD_XML_VERSION_MINOR,
			self::BULK_UPLOAD_XML_VERSION_BUILD);
			
		$contentDistributionVersion = new VidiunVersion(
			self::CONTENT_DSTRIBUTION_VERSION_MAJOR,
			self::CONTENT_DSTRIBUTION_VERSION_MINOR,
			self::CONTENT_DSTRIBUTION_VERSION_BUILD);
			
		$bulkUploadXmlDependency = new VidiunDependency(BulkUploadXmlPlugin::getPluginName(), $bulkUploadXmlVersion);
		$contentDistributionDependency = new VidiunDependency(ContentDistributionPlugin::getPluginName(), $contentDistributionVersion);
		
		return array($bulkUploadXmlDependency, $contentDistributionDependency);
	}
	
	public function getDistributionProfileId($name, $providerName)
	{
		if(is_null($this->distributionProfilesNames))
		{
			$distributionPlugin = VidiunContentDistributionClientPlugin::get(VBatchBase::$vClient);
			$distributionProfileListResponse = $distributionPlugin->distributionProfile->listAction();
			if(!is_array($distributionProfileListResponse->objects))
				return null;
				
			$this->distributionProfilesNames = array();
			$this->distributionProfilesProviders = array();
			
			foreach($distributionProfileListResponse->objects as $distributionProfile)
			{
				if(!is_null($distributionProfile->name))
					$this->distributionProfilesNames[$distributionProfile->name] = $distributionProfile->id;
					
				if(!is_null($distributionProfile->providerType))
					$this->distributionProfilesProviders[$distributionProfile->providerType] = $distributionProfile->id;
			}
		}
		$distributionProfileName = (string)$name;
		if(!empty($distributionProfileName) && isset($this->distributionProfilesNames[$distributionProfileName]))
			return $this->distributionProfilesNames[$distributionProfileName];

		$distributionProviderName = (string)$providerName;
		if(!empty($distributionProviderName) && isset($this->distributionProfilesProviders[$distributionProviderName]))
			return $this->distributionProfilesProviders[$distributionProviderName];
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::configureBulkUploadXmlHandler()
	 */
	public function configureBulkUploadXmlHandler(BulkUploadEngineXml $xmlBulkUploadEngine)
	{
		$this->xmlBulkUploadEngine = $xmlBulkUploadEngine;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemAdded()
	 */
	public function handleItemAdded(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		if(!($object instanceof VidiunBaseEntry))
			return;
			
		if(empty($item->distributions))
			return;
			
		VBatchBase::impersonate($this->xmlBulkUploadEngine->getCurrentPartnerId());
		foreach($item->distributions->distribution as $distribution)
			$this->handleDistribution($object->id, $distribution);
		VBatchBase::unimpersonate();
	}
	
	protected function handleDistribution($entryId, SimpleXMLElement $distribution)
	{
		$distributionProfileId = null;
		if(!empty($distribution->distributionProfileId))
			$distributionProfileId = (int)$distribution->distributionProfileId;

		if(!$distributionProfileId && (!empty($distribution->distributionProfile) || !empty($distribution->distributionProvider)))
			$distributionProfileId = $this->getDistributionProfileId($distribution->distributionProfile, $distribution->distributionProvider);
				
		if(!$distributionProfileId)
			throw new VidiunBatchException("Unable to retrieve distributionProfileId value", VidiunBatchJobAppErrors::BULK_MISSING_MANDATORY_PARAMETER);
		
		$distributionPlugin = VidiunContentDistributionClientPlugin::get(VBatchBase::$vClient);
		
		$entryDistributionFilter = new VidiunEntryDistributionFilter();
		$entryDistributionFilter->distributionProfileIdEqual = $distributionProfileId;
		$entryDistributionFilter->entryIdEqual = $entryId;
		
		$pager = new VidiunFilterPager();
		$pager->pageSize = 1;
		
		$entryDistributionResponse = $distributionPlugin->entryDistribution->listAction($entryDistributionFilter, $pager);
		
		$entryDistribution = new VidiunEntryDistribution();
		$entryDistributionId = null;
		if(is_array($entryDistributionResponse->objects) && count($entryDistributionResponse->objects) > 0)
		{
			$existingEntryDistribution = reset($entryDistributionResponse->objects);
			$entryDistributionId = $existingEntryDistribution->id;
		}
		else
		{
			$entryDistribution->entryId = $entryId;
			$entryDistribution->distributionProfileId = $distributionProfileId;
		}
		
		if(!empty($distribution->sunrise) && VBulkUploadEngine::isFormatedDate($distribution->sunrise))
			$entryDistribution->sunrise = VBulkUploadEngine::parseFormatedDate($distribution->sunrise);
			
		if(!empty($distribution->sunset) && VBulkUploadEngine::isFormatedDate($distribution->sunset))
			$entryDistribution->sunset = VBulkUploadEngine::parseFormatedDate($distribution->sunset);
		
		if(!empty($distribution->flavorAssetIds))
			$entryDistribution->flavorAssetIds = $distribution->flavorAssetIds;
		
		if(!empty($distribution->thumbAssetIds))
			$entryDistribution->thumbAssetIds = $distribution->thumbAssetIds;
			
		$submitWhenReady = false;
		if($distribution['submitWhenReady'])
			$submitWhenReady = true;
			
		if($entryDistributionId)
		{
			$updatedEntryDistribution = $distributionPlugin->entryDistribution->update($entryDistributionId, $entryDistribution);
			if($submitWhenReady && $updatedEntryDistribution->dirtyStatus == VidiunEntryDistributionFlag::UPDATE_REQUIRED)
				$distributionPlugin->entryDistribution->submitUpdate($entryDistributionId);
		}
		else
		{
			$createdEntryDistribution = $distributionPlugin->entryDistribution->add($entryDistribution);
			$distributionPlugin->entryDistribution->submitAdd($createdEntryDistribution->id, $submitWhenReady);
		}
	}

	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemUpdated()
	 */
	public function handleItemUpdated(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		$this->handleItemAdded($object, $item);
	}

	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemDeleted()
	 */
	public function handleItemDeleted(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		// No handling required
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunConfigurator::getConfig()
	 */
	public static function getConfig($configName)
	{
		if($configName == 'generator')
			return new Zend_Config_Ini(dirname(__FILE__) . '/config/contentDistributionBulkUploadXml.generator.ini');
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunConfigurator::getContainerName()
	*/
	public function getContainerName()
	{
		return 'distribution';
	}
}
