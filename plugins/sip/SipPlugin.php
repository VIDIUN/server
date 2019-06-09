<?php
/**
 * @package plugins.sip
 */
class SipPlugin extends VidiunPlugin implements   IVidiunObjectLoader, IVidiunEnumerator, IVidiunServices, IVidiunEventConsumers, IVidiunSearchDataContributor
{
	const PLUGIN_NAME = 'sip';
	const SIP_EVENTS_CONSUMER = 'vSipEventsConsumer';
	const SEARCH_DATA_SUFFIX = 'sipend';

	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}

	/* (non-PHPdoc)
		 * @see IVidiunPermissions::isAllowedPartner()
		 */
	public static function isAllowedPartner($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(self::PLUGIN_NAME);
	}

	public static function getCoreValue($type, $valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore($type, $value);
	}

	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

	/* (non-PHPdoc)
    * @see IVidiunObjectLoader::loadObject()
    */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if ($baseClass === 'VidiunServerNode' && $enumValue == self::getCoreValue('serverNodeType', SipServerNodeType::SIP_SERVER))
		{
			return new VidiunSipServerNode();
		}
		if ($baseClass === 'VidiunEntryServerNode' && $enumValue == self::getCoreValue('EntryServerNodeType', SipEntryServerNodeType::SIP_ENTRY_SERVER))
		{
			return new VidiunSipEntryServerNode();
		}

	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if ($baseClass === 'ServerNode' && $enumValue == self::getCoreValue('serverNodeType', SipServerNodeType::SIP_SERVER))
		{
			return 'SipServerNode';
		}
		if ($baseClass === 'EntryServerNode' && $enumValue == self::getCoreValue('EntryServerNodeType', SipEntryServerNodeType::SIP_ENTRY_SERVER))
		{
			return 'SipEntryServerNode';
		}
	}

	/* (non-PHPdoc)
    * @see IVidiunEnumerator::getEnums()
    */
	public static function getEnums($baseEnumName = null)
	{
		if (is_null($baseEnumName))
		{
			return array('SipServerNodeType', 'SipEntryServerNodeType');
		}

		if ($baseEnumName === 'serverNodeType')
		{
			return array('SipServerNodeType');
		}

		if ($baseEnumName === 'entryType')
		{
			return array('SipEntryServerNodeType');
		}

		return array();
	}

	public static function getServicesMap()
	{
		$map = array(
			'pexip' => 'PexipService',
		);
		return $map;
	}

	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::SIP_EVENTS_CONSUMER,
		);
	}

	/**
	 * @param string $sipToken
	 * @return string
	 */
	public static function getSipTokenSearchData($sipToken)
	{
		return self::getPluginName() . $sipToken . self::SEARCH_DATA_SUFFIX;
	}

	/* (non-PHPdoc)
	 * @see IVidiunSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if ($object instanceof LiveStreamEntry)
		{
			$sipToken = $object->getSipToken();
			if ($sipToken)
			{
				return array('plugins_data' => self::getSipTokenSearchData($sipToken));
			}
		}
		return null;
	}
}