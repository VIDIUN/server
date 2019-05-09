<?php
/**
 * Enable event notifications on metadata objects
 * @package plugins.metadataEventNotifications
 */
class MetadataEventNotificationsPlugin extends VidiunPlugin implements IVidiunPending, IVidiunEnumerator, IVidiunObjectLoader, IVidiunEventNotificationContentEditor
{
	const PLUGIN_NAME = 'metadataEventNotifications';
	
	const METADATA_PLUGIN_NAME = 'metadata';
	
	const EVENT_NOTIFICATION_PLUGIN_NAME = 'eventNotification';
	const EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR = 1;
	const EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR = 0;
	const EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD = 0;
	
	const METADATA_EMAIL_NOTIFICATION_REGEX = '/\{metadata\:[^:]+\:[^}]+\}/';

	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
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
		$eventNotificationVersion = new VidiunVersion(self::EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR, self::EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR, self::EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD);
		
		$metadataDependency = new VidiunDependency(self::METADATA_PLUGIN_NAME);
		$eventNotificationDependency = new VidiunDependency(self::EVENT_NOTIFICATION_PLUGIN_NAME, $eventNotificationVersion);
		
		return array($metadataDependency, $eventNotificationDependency);
	}
			
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('MetadataEventNotificationEventObjectType');
	
		if($baseEnumName == 'EventNotificationEventObjectType')
			return array('MetadataEventNotificationEventObjectType');
			
		return array();
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		return null;
	}
		
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'EventNotificationEventObjectType' && $enumValue == self::getEventNotificationEventObjectTypeCoreValue(MetadataEventNotificationEventObjectType::METADATA))
		{
			return MetadataPeer::OM_CLASS;
		}
					
		return null;
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getEventNotificationEventObjectTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('EventNotificationEventObjectType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/**
	 * Function sweeps the given fields of the emailNotificationTemplate, and parses expressions of the type
	 * {metadata:[metadataProfileSystemName]:[metadataProfileFieldSystemName]}
	 */
	public static function editTemplateFields($sweepFieldValues, $scope, $objectType)
	{
		if (! ($scope instanceof vEventScope))
			return array();
		
		if (!method_exists($scope->getObject(), 'getPartnerId'))
			return array();
		
		$partnerId = $scope->getObject()->getPartnerId();
		/* @var $scope vEventScope */
		$metadataContentParameters = array();
		foreach ($sweepFieldValues as $sweepFieldValue)
		{
			//Obtain matches for the set structure {metadata:[profileSystemName][profileFieldSystemName]}
			preg_match_all(self::METADATA_EMAIL_NOTIFICATION_REGEX, $sweepFieldValue, $matches);
			foreach ($matches[0] as $match)
			{				
				$match = str_replace(array ('{', '}'), array ('', ''), $match);
				list ($metadata, $profileSystemName, $fieldSystemName, $format) = explode(':', $match, 4);
				$profile = MetadataProfilePeer::retrieveBySystemName($profileSystemName, $partnerId);
				if (!$profile)
				{
					VidiunLog::info("Metadata profile with system name $profileSystemName not found for this partner. Token will be replaced with empty string.");
					$metadataContentParameters[$match] = '';
					continue;
				}
				
				$objectId = null;
				$metadataObjectId = null;
				//If the metadataProfileobjectType matches the one on the emailNotification, we can proceed
				//If the objectType of the email template is 'asset' we can use the entryId
				//If the objectType of the email template is a metadata object we can use its id
				if (vMetadataManager::getObjectTypeName($profile->getObjectType()) == VidiunPluginManager::getObjectClass('EventNotificationEventObjectType', $objectType))
				{
					$objectId = $scope->getObject()->getId();
				}
				elseif (vMetadataManager::getObjectTypeName($profile->getObjectType()) == 'entry'
						&& ($scope->getObject() instanceof asset))
				{
					$objectId = $scope->getObject()->getEntryId();
				}
				elseif ($scope->getObject() instanceof categoryEntry)
				{
					$profileObject = vMetadataManager::getObjectTypeName($profile->getObjectType());
					$getter = "get{$profileObject}Id";
					VidiunLog::info ("Using $getter in order to retrieve the metadata object ID");
					$categoryEntry = $scope->getObject();
					$objectId = $categoryEntry->$getter();
				}
				elseif (VidiunPluginManager::getObjectClass('EventNotificationEventObjectType', $objectType) == MetadataPeer::OM_CLASS)
				{
					$metadataObjectId = $scope->getObject()->getId();
				}
				
				
				if ($objectId)
				{
					$result = MetadataPeer::retrieveByObject($profile->getId(), $profile->getObjectType(), $objectId);
				}
				elseif ($metadataObjectId)
				{
					$result = MetadataPeer::retrieveByPK($metadataObjectId);
				}
				else 
				{
					//There is not enough specification regarding the required metadataObject, abort.
					VidiunLog::info("The template does not contain an object Id for which custom metadata can be retrieved. Token will be replaced with empty string.");
					$metadataContentParameters[$match] = '';
					continue;	
				}
				
				if (!$result)
				{
					VidiunLog::info("Metadata object could not be retrieved. Token will be replaced with empty string.");
					$metadataContentParameters[$match] = '';
					continue;
				}
				
				$strvals = vMetadataManager::getMetadataValueForField($result, $fieldSystemName);
				foreach ($strvals as &$strval)
				{
					if ($format && is_numeric($strval))
					{
						$strval = date($format,$strval);
					}
				}
				
				$metadataContentParameters[$match] = implode(',', $strvals);
			}
		}

		return $metadataContentParameters;
	}
}
