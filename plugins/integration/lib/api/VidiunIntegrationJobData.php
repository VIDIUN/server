<?php
/**
 * @package plugins.integration
 * @subpackage api.objects
 */
class VidiunIntegrationJobData extends VidiunJobData
{
	/**
	 * @var string
	 * @readonly
	 */
	public $callbackNotificationUrl;
	
	/**
	 * @var VidiunIntegrationProviderType
	 */
	public $providerType;

	/**
	 * Additional data that relevant for the provider only
	 * @var VidiunIntegrationJobProviderData
	 */
	public $providerData;

	/**
	 * @var VidiunIntegrationTriggerType
	 */
	public $triggerType;

	/**
	 * Additional data that relevant for the trigger only
	 * @var VidiunIntegrationJobTriggerData
	 */
	public $triggerData;
	
	private static $map_between_objects = array
	(
		"callbackNotificationUrl",
		"providerType" ,
		"triggerType" ,
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($srcObj)
	 */
	public function doFromObject($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($sourceObject, $responseProfile);
		
		$providerType = $sourceObject->getProviderType();
		$this->providerData = VidiunPluginManager::loadObject('VidiunIntegrationJobProviderData', $providerType);
		$providerData = $sourceObject->getProviderData();
		if($this->providerData && $providerData && $providerData instanceof vIntegrationJobProviderData)
			$this->providerData->fromObject($providerData);
			
		$triggerType = $sourceObject->getTriggerType();
		$this->triggerData = VidiunPluginManager::loadObject('VidiunIntegrationJobTriggerData', $triggerType);
		$triggerData = $sourceObject->getTriggerData();
		if($this->triggerData && $triggerData && $triggerData instanceof vIntegrationJobTriggerData)
			$this->triggerData->fromObject($triggerData);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object = null, $skip = array())
	{
		if(is_null($object))
		{
			$object = new vIntegrationJobData();
		} 
		$object = parent::toObject($object, $skip);
				
		if($this->providerType && $this->providerData && $this->providerData instanceof VidiunIntegrationJobProviderData)
		{
			$providerData = VidiunPluginManager::loadObject('vIntegrationJobProviderData', $this->providerType);
			if($providerData)
			{
				$providerData = $this->providerData->toObject($providerData);
				$object->setProviderData($providerData);
			}
		}
		
		if($this->triggerType && $this->triggerData && $this->triggerData instanceof VidiunIntegrationJobTriggerData)
		{
			$triggerData = VidiunPluginManager::loadObject('vIntegrationJobTriggerData', $this->triggerType);
			if($triggerData)
			{
				$triggerData = $this->triggerData->toObject($triggerData);
				$object->setTriggerData($triggerData);
			}
		}
		
		return $object;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('providerType');
		$this->validatePropertyNotNull('providerData');
		$this->validatePropertyNotNull('triggerType');
		
		if ($this->triggerType != VidiunIntegrationTriggerType::MANUAL)
			$this->validatePropertyNotNull('triggerData');
	}
	
	/* (non-PHPdoc)
	 * @see VidiunJobData::toSubType()
	 */
	public function toSubType($subType)
	{
		return vPluginableEnumsManager::apiToCore('IntegrationProviderType', $subType);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunJobData::fromSubType()
	 */
	public function fromSubType($subType)
	{
		return vPluginableEnumsManager::coreToApi('IntegrationProviderType', $subType);
	}
}
