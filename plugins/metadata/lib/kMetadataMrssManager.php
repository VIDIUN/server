<?php
/**
 * @package plugins.metadata
 * @subpackage lib
 */
class vMetadataMrssManager implements IVidiunMrssContributor
{
	/**
	 * @var vMetadataMrssManager
	 */
	protected static $instance;
	
	protected function __construct()
	{
	}
	
	/**
	 * @return vMetadataMrssManager
	 */
	public static function get()
	{
		if(!self::$instance)
			self::$instance = new vMetadataMrssManager();
			
		return self::$instance;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunMrssContributor::contributeToSchema()
	 */
	public function contribute(BaseObject $object, SimpleXMLElement $mrss, vMrssParameters $mrssParams = null)
	{
		$objectType = vMetadataManager::getTypeNameFromObject($object);
		$metadatas = MetadataPeer::retrieveAllByObject($objectType, $object->getId());
		foreach($metadatas as $metadata)
			$this->contributeMetadata($metadata, $mrss, $mrssParams);
	}
	
	/**
	 * @param Metadata $metadata
	 * @param SimpleXMLElement $mrss
	 * @param vMrssParameters $mrssParams
	 * @return SimpleXMLElement
	 */
	public function contributeMetadata(Metadata $metadata, SimpleXMLElement $mrss, vMrssParameters $mrssParams = null)
	{
		$key = $metadata->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
		$xml = vFileSyncUtils::file_get_contents($key, true, false);
		if (is_null($xml)){
			VidiunLog::alert("ready file sync was not found for key[$key]");
			return;
		}
		$metadataXml = new SimpleXMLElement($xml);
		
		$customData = $mrss->addChild('customData');
		$customData->addAttribute('metadataId', $metadata->getId());
		$customData->addAttribute('metadataVersion', $metadata->getVersion());
		$customData->addAttribute('metadataProfileId', $metadata->getMetadataProfileId());
		$customData->addAttribute('metadataProfileVersion', $metadata->getMetadataProfileVersion());
		
		$this->contributeMetadataObject($customData, $metadataXml, $mrssParams, '');
	}
	
	/**
	 * @param SimpleXMLElement $mrss
	 * @param SimpleXMLElement $metadata
	 * @param vMrssParameters $mrssParams
	 * @return SimpleXMLElement
	 */
	public function contributeMetadataObject(SimpleXMLElement $mrss, SimpleXMLElement $metadata, vMrssParameters $mrssParams = null, $currentXPath)
	{
		$currentXPath .= "/*[local-name()='" . $metadata->getName() . "']";
		
		$metadataObject = $mrss->addChild($metadata->getName());
		foreach($metadata->attributes() as $attributeField => $attributeValue)
			$metadataObject->addAttribute($attributeField, $attributeValue);

		foreach($metadata as $metadataField => $metadataValue)
		{
			if($metadataValue instanceof SimpleXMLElement && count($metadataValue))
			{
				$this->contributeMetadataObject($metadataObject, $metadataValue, $mrssParams, $currentXPath);
			}
			else
			{
				$metadataObject->addChild($metadataField, vString::stringToSafeXml($metadataValue));
			}					
		}				
	}

	/* (non-PHPdoc)
	 * @see IVidiunBase::getInstance()
	 */
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		$plugin = VidiunPluginManager::getPluginInstance(MetadataPlugin::getPluginName());		
		if($plugin)
			return $plugin->getInstance($interface);
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunMrssContributor::returnObjectFeatureType()
	 */
	public function getObjectFeatureType() 
	{
		return MetadataPlugin::getObjectFeaturetTypeCoreValue(MetadataObjectFeatureType::CUSTOM_DATA);
	}

	
}