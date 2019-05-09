<?php
/**
 * @package plugins.pushNotification
 */

class PushNotificationPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunPending, IVidiunObjectLoader, IVidiunEnumerator, IVidiunApplicationTranslations, IVidiunServices
{
    const PLUGIN_NAME = 'pushNotification';

    const EVENT_NOTIFICATION_PLUGIN_NAME = 'eventNotification';
    const EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR = 1;
    const EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR = 0;
    const EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD = 0;

    /* (non-PHPdoc)
     * @see IVidiunPlugin::getPluginName()
     */
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
        if ($partner)
        {
            // check that both the push plugin and the event notification plugin are enabled
            return $partner->getPluginEnabled(self::PLUGIN_NAME) && EventNotificationPlugin::isAllowedPartner($partnerId);
        }
        return false;
    }
    
    /* (non-PHPdoc)
     * @see IVidiunEnumerator::getEnums()
     */
    public static function getEnums($baseEnumName = null)
    {
        if(is_null($baseEnumName))
            return array('PushNotificationTemplateType');
    
        if($baseEnumName == 'EventNotificationTemplateType')
            return array('PushNotificationTemplateType');
        	
        return array();
    }   
    
    /* (non-PHPdoc)
     * @see IVidiunObjectLoader::loadObject()
     */
    public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
    {

        $class = self::getObjectClass($baseClass, $enumValue);
        if($class)
        {
            if(is_array($constructorArgs))
            {
                $reflect = new ReflectionClass($class);
                return $reflect->newInstanceArgs($constructorArgs);
            }
            	
            return new $class();
        }

        return null;
    }
        
    /* (non-PHPdoc)
     * @see IVidiunObjectLoader::getObjectClass()
     */
    public static function getObjectClass($baseClass, $enumValue)
    {
        if ($baseClass == 'EventNotificationTemplate' && $enumValue == self::getPushNotificationTemplateTypeCoreValue(PushNotificationTemplateType::PUSH))
            return 'PushNotificationTemplate';
    
        if ($baseClass == 'VidiunEventNotificationTemplate' && $enumValue == self::getPushNotificationTemplateTypeCoreValue(PushNotificationTemplateType::PUSH))
            return 'VidiunPushNotificationTemplate';
                          
        if($baseClass == 'Vidiun_Client_EventNotification_Type_EventNotificationTemplate' && $enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::PUSH)
            return 'Vidiun_Client_PushNotification_Type_PushNotificationTemplate';
        
        if($baseClass == 'Form_EventNotificationTemplateConfiguration' && $enumValue == Vidiun_Client_EventNotification_Enum_EventNotificationTemplateType::PUSH)
            return 'Form_PushNotificationTemplateConfiguration';     
           
        return null;
    }  
    

    /* (non-PHPdoc)
     * @see IVidiunPending::dependsOn()
     */
    public static function dependsOn()
    {
        $minVersion = new VidiunVersion(
            self::EVENT_NOTIFICATION_PLUGIN_VERSION_MAJOR,
            self::EVENT_NOTIFICATION_PLUGIN_VERSION_MINOR,
            self::EVENT_NOTIFICATION_PLUGIN_VERSION_BUILD
        );
        $dependency = new VidiunDependency(self::EVENT_NOTIFICATION_PLUGIN_NAME, $minVersion);
    
        return array($dependency);
    }
    
    /**
     * @return int id of dynamic enum in the DB.
     */
    public static function getPushNotificationTemplateTypeCoreValue($valueName)
    {
        $value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
        return vPluginableEnumsManager::apiToCore('EventNotificationTemplateType', $value);
    }
    
    /**
     * @return string external API value of dynamic enum.
     */
    public static function getApiValue($valueName)
    {
        return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
    }    
    
    /* (non-PHPdoc)
     * @see IVidiunApplicationTranslations::getTranslations()
     */
    public static function getTranslations($locale)
    {
        $array = array();
    
        $langFilePath = __DIR__ . "/config/lang/$locale.php";
        if(!file_exists($langFilePath))
        {
            $default = 'en';
            $langFilePath = __DIR__ . "/config/lang/$default.php";
        }
    
        $array = include($langFilePath);
    
        return array($locale => $array);
    }
    
    /* (non-PHPdoc)
     * @see IVidiunServices::getServicesMap()
     */
    public static function getServicesMap()
    {
        return array(
            'pushNotificationTemplate' => 'PushNotificationTemplateService',
        );
    }    
}