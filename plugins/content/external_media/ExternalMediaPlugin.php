<?php
/**
 * @package plugins.externalMedia
 */
class ExternalMediaPlugin extends VidiunPlugin implements IVidiunServices, IVidiunObjectLoader, IVidiunEnumerator, IVidiunTypeExtender, IVidiunSearchDataContributor, IVidiunEventConsumers, IVidiunMrssContributor
{
	const PLUGIN_NAME = 'externalMedia';
	const EXTERNAL_MEDIA_CREATED_HANDLER = 'ExternalMediaCreatedHandler';
	const SEARCH_DATA_SUFFIX = 's';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::EXTERNAL_MEDIA_CREATED_HANDLER,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunTypeExtender::getExtendedTypes()
	 */
	public static function getExtendedTypes($baseClass, $enumValue)
	{
		if($baseClass == entryPeer::OM_CLASS && $enumValue == entryType::MEDIA_CLIP)
		{
			return array(
				ExternalMediaPlugin::getEntryTypeCoreValue(ExternalMediaEntryType::EXTERNAL_MEDIA),
			);
		}
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		$class = self::getObjectClass($baseClass, $enumValue);
		if($class)
			return new $class();
		
		return null;
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'entry' && $enumValue == ExternalMediaPlugin::getEntryTypeCoreValue(ExternalMediaEntryType::EXTERNAL_MEDIA))
		{
			return 'ExternalMediaEntry';
		}
		
		if($baseClass == 'VidiunBaseEntry' && $enumValue == ExternalMediaPlugin::getEntryTypeCoreValue(ExternalMediaEntryType::EXTERNAL_MEDIA))
		{
			return 'VidiunExternalMediaEntry';
		}
		
		return null;
	}

	/**
	 * @return array<string,string> in the form array[serviceName] = serviceClass
	 */
	public static function getServicesMap()
	{
		$map = array(
			'externalMedia' => 'ExternalMediaService',
		);
		return $map;
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getEntryTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('entryType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('ExternalMediaEntryType');
	
		if($baseEnumName == 'entryType')
			return array('ExternalMediaEntryType');
			
		return array();
	}

	public static function getExternalSourceSearchData($externalSourceType)
	{
		return self::getPluginName() . $externalSourceType . self::SEARCH_DATA_SUFFIX;
	}

	/* (non-PHPdoc)
	 * @see IVidiunSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if($object instanceof ExternalMediaEntry)
		{
			return array('plugins_data' => self::getExternalSourceSearchData($object->getExternalSourceType()));
		}
			
		return null;
	}
	
		/* (non-PHPdoc)
         * @see IVidiunMrssContributor::contribute()
         */
        public function contribute(BaseObject $object, SimpleXMLElement $mrss, vMrssParameters $mrssParams = null)
        {
                if(!($object instanceof entry) || $object->getType() != self::getEntryTypeCoreValue(ExternalMediaEntryType::EXTERNAL_MEDIA))
                        return;

                $externalEntry = $mrss->addChild('externalEntry');
                $externalEntry->addChild('duration', $object->getDuration());
        }

        /* (non-PHPdoc)
         * @see IVidiunMrssContributor::getObjectFeatureType()
         */
        public function getObjectFeatureType()
        {
                return null;
        }

}
