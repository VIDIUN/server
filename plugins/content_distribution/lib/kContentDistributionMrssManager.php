<?php
/**
 * @package plugins.contentDistribution
 * @subpackage lib
 */
class vContentDistributionMrssManager implements IVidiunMrssContributor
{
	/**
	 * @var vContentDistributionMrssManager
	 */
	protected static $instance;
	
	protected function __construct()
	{
	}
	
	/**
	 * @return vContentDistributionMrssManager
	 */
	public static function get()
	{
		if(!self::$instance)
			self::$instance = new vContentDistributionMrssManager();
			
		return self::$instance;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunMrssContributor::contribute()
	 */
	public function contribute(BaseObject $object, SimpleXMLElement $mrss, vMrssParameters $mrssParams = null)
	{
		if(!($object instanceof entry))
			return;
			
		$entryDistributions = EntryDistributionPeer::retrieveByEntryId($object->getId());
		foreach($entryDistributions as $entryDistribution)
			$this->contributeDistribution($entryDistribution, $mrss);
	}
	
	/**
	 * @param EntryDistribution $entryDistribution
	 * @param SimpleXMLElement $mrss
	 * @return SimpleXMLElement
	 */
	public function contributeDistribution(EntryDistribution $entryDistribution, SimpleXMLElement $mrss)
	{
		$distributionsProvider = null;
		$distributionsProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
		if($distributionsProfile)
			$distributionsProvider = $distributionsProfile->getProvider();
		
		$distribution = $mrss->addChild('distribution');
		$distribution->addAttribute('entryDistributionId', $entryDistribution->getId());
		$distribution->addAttribute('distributionProfileId', $entryDistribution->getDistributionProfileId());
		if($distributionsProfile)
			$distribution->addAttribute('distributionProfileName', $distributionsProfile->getName());
		
		if($distributionsProvider)
		{
			$distribution->addAttribute('provider', $distributionsProvider->getName());
			if($distributionsProvider->getType() == DistributionProviderType::GENERIC)
			{
				$distribution->addAttribute('distributionProviderId', $distributionsProvider->getId());
			}
			elseif($distributionsProvider->getType() == DistributionProviderType::SYNDICATION)
			{
				if($distributionsProfile instanceof SyndicationDistributionProfile)
					$distribution->addAttribute('feedId', $distributionsProfile->getFeedId());
			}
			else
			{
				$pluginInstances = VidiunPluginManager::getPluginInstances('IVidiunContentDistributionProvider');
				foreach($pluginInstances as $pluginInstance)
					if($pluginInstance->getProvider() === $distributionsProvider)
						$pluginInstance->contributeMRSS($entryDistribution, $distribution);
			}
		}
			
		if($entryDistribution->getRemoteId())
			$distribution->addChild('remoteId', $entryDistribution->getRemoteId());
			
		if($entryDistribution->getSunrise(null))
			$distribution->addChild('sunrise', $entryDistribution->getSunrise(null));
			
		if($entryDistribution->getSunset(null))
			$distribution->addChild('sunset', $entryDistribution->getSunset(null));
			
		$flavorAssetIds = explode(',', $entryDistribution->getFlavorAssetIds());
		$flavorAssetIdsNode = $distribution->addChild('flavorAssetIds');
		foreach($flavorAssetIds as $flavorAssetId)
			$flavorAssetIdsNode->addChild('flavorAssetId', $flavorAssetId);
			
		$thumbAssetIds = explode(',', $entryDistribution->getThumbAssetIds());
		$thumbAssetIdsNode = $distribution->addChild('thumbAssetIds');
		foreach($thumbAssetIds as $thumbAssetId)
			$thumbAssetIdsNode->addChild('thumbAssetId', $thumbAssetId);
		
		$assetIds = explode(',', $entryDistribution->getAssetIds());
		$assetIdsNode = $distribution->addChild('assetIds');
		foreach($assetIds as $assetId)
			$assetIdsNode->addChild('assetId', $assetId);
			
		if($entryDistribution->getErrorDescription())
			$distribution->addChild('errorDescription', vMrssManager::stringToSafeXml($entryDistribution->getErrorDescription()));
			
		$distribution->addChild('createdAt', $entryDistribution->getCreatedAt(vMrssManager::FORMAT_DATETIME));	
		$distribution->addChild('updatedAt', $entryDistribution->getUpdatedAt(vMrssManager::FORMAT_DATETIME));	
		if($entryDistribution->getSubmittedAt(null))
			$distribution->addChild('submittedAt', $entryDistribution->getSubmittedAt(vMrssManager::FORMAT_DATETIME));
		if($entryDistribution->getLastReport(null))
			$distribution->addChild('lastReport', $entryDistribution->getLastReport(vMrssManager::FORMAT_DATETIME));
		if($entryDistribution->getDirtyStatus())
			$distribution->addChild('dirtyStatus', $entryDistribution->getDirtyStatus());
		$distribution->addChild('status', $entryDistribution->getStatus());
		$distribution->addChild('sunStatus', $entryDistribution->getSunStatus());
		if($entryDistribution->getPlays())
			$distribution->addChild('plays', $entryDistribution->getPlays());
		if($entryDistribution->getViews())
			$distribution->addChild('views', $entryDistribution->getViews());
		if($entryDistribution->getErrorNumber())
			$distribution->addChild('errorNumber', $entryDistribution->getErrorNumber());
		if($entryDistribution->getErrorType())
			$distribution->addChild('errorType', $entryDistribution->getErrorType());
	}

	/* (non-PHPdoc)
	 * @see IVidiunBase::getInstance()
	 */
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		$plugin = VidiunPluginManager::getPluginInstance(ContentDistributionPlugin::getPluginName());
		if($plugin)
			return $plugin->getInstance($interface);
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunMrssContributor::getObjectFeatureType()
	 */
	public function getObjectFeatureType()
	{
		return ContentDistributionPlugin::getObjectFeatureTypeCoreValue(ContentDistributionObjectFeatureType::CONTENT_DISTRIBUTION);
	}
	
}