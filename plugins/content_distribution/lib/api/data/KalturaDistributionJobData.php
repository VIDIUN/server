<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunDistributionJobData extends VidiunJobData
{
	/**
	 * @var int
	 */
	public $distributionProfileId;
	
	/**
	 * @var VidiunDistributionProfile
	 */
	public $distributionProfile;
	
	/**
	 * @var int
	 */
	public $entryDistributionId;
	
	/**
	 * @var VidiunEntryDistribution
	 */
	public $entryDistribution;

	/**
	 * Id of the media in the remote system
	 * @var string
	 */
	public $remoteId;

	/**
	 * @var VidiunDistributionProviderType
	 */
	public $providerType;

	/**
	 * Additional data that relevant for the provider only
	 * @var VidiunDistributionJobProviderData
	 */
	public $providerData;

	/**
	 * The results as returned from the remote destination
	 * @var string
	 */
	public $results;

	/**
	 * The data as sent to the remote destination
	 * @var string
	 */
	public $sentData;
	
	/**
	 * Stores array of media files that submitted to the destination site
	 * Could be used later for media update 
	 * @var VidiunDistributionRemoteMediaFileArray
	 */
	public $mediaFiles;
	
	
	private static $map_between_objects = array
	(
		"distributionProfileId" ,
		"entryDistributionId" ,
		"remoteId" ,
		"providerType" ,
		"results" ,
		"sentData" ,
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function doFromObject($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($sourceObject, $responseProfile);
		
		$this->mediaFiles = VidiunDistributionRemoteMediaFileArray::fromDbArray($sourceObject->getMediaFiles());
		
		if(!$this->distributionProfileId)
			return;
			
		if(!$this->entryDistributionId)
			return;
			
		$distributionProfile = DistributionProfilePeer::retrieveByPK($this->distributionProfileId);
		if(!$distributionProfile || $distributionProfile->getStatus() != DistributionProfileStatus::ENABLED)
			return;
			
		$this->distributionProfile = VidiunDistributionProfileFactory::createVidiunDistributionProfile($distributionProfile->getProviderType());
		$this->distributionProfile->fromObject($distributionProfile);
		
		$entryDistribution = EntryDistributionPeer::retrieveByPK($this->entryDistributionId);
		if($entryDistribution)
		{
			$this->entryDistribution = new VidiunEntryDistribution();
			$this->entryDistribution->fromObject($entryDistribution);
		}
		
		$providerType = $sourceObject->getProviderType();
		if($providerType)
		{
			if($providerType == VidiunDistributionProviderType::GENERIC)
			{
				$this->providerData = new VidiunGenericDistributionJobProviderData($this);
			}
			else 
			{
				$this->providerData = VidiunPluginManager::loadObject('VidiunDistributionJobProviderData', $providerType, array($this));
			}
			
			$providerData = $sourceObject->getProviderData();
			if($this->providerData && $providerData && $providerData instanceof vDistributionJobProviderData)
				$this->providerData->fromObject($providerData);
		}
	}
	
	public function toObject($object = null, $skip = array())
	{
		$object = parent::toObject($object, $skip);
				
		if($this->mediaFiles)
		{
			$mediaFiles = array();
			foreach($this->mediaFiles as $mediaFile)
				$mediaFiles[] = $mediaFile->toObject();
				
			$object->setMediaFiles($mediaFiles);
		}
		
		if($this->providerType && $this->providerData && $this->providerData instanceof VidiunDistributionJobProviderData)
		{
			$providerData = null;
			if($this->providerType == VidiunDistributionProviderType::GENERIC)
			{
				$providerData = new vGenericDistributionJobProviderData($object);
			}
			else 
			{
				$providerData = VidiunPluginManager::loadObject('vDistributionJobProviderData', $this->providerType, array($object));
			}
			
			if($providerData)
			{
				$providerData = $this->providerData->toObject($providerData);
				$object->setProviderData($providerData);
			}
		}
		
		return $object;
	}
	
	/**
	 * @param string $subType is the provider type
	 * @return int
	 */
	public function toSubType($subType)
	{
		return vPluginableEnumsManager::apiToCore('DistributionProviderType', $subType);
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		return vPluginableEnumsManager::coreToApi('DistributionProviderType', $subType);
	}
}
