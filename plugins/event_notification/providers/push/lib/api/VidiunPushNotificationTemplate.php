<?php
/**
 * @package plugins.pushNotification
 * @subpackage api.objects
*/
class VidiunPushNotificationTemplate extends VidiunEventNotificationTemplate
{
	/**
	 * Define the content dynamic parameters
	 * @var VidiunPushEventNotificationParameterArray
	 * @requiresPermission update
	 */
	public $queueNameParameters;
	
	/**
	 * Define the content dynamic parameters
	 * @var VidiunPushEventNotificationParameterArray
	 * @requiresPermission update
	 */
	public $queueKeyParameters;
	
    /**
     * Vidiun API object type
     * @var string
     */
    public $apiObjectType;
    
    /**
     * Vidiun Object format
     * @var VidiunResponseType
     */    
    public $objectFormat;
    
    /**
     * Vidiun response-profile id
     * @var int
     */    
    public $responseProfileId;
    

    private static $map_between_objects = array('apiObjectType', 'objectFormat', 'responseProfileId', 'queueNameParameters', 'queueKeyParameters');
    
    public function __construct()
    {
        $this->type = PushNotificationPlugin::getApiValue(PushNotificationTemplateType::PUSH);
    }
    
    /* (non-PHPdoc)
     * @see VidiunObject::getMapBetweenObjects()
     */
    public function getMapBetweenObjects()
    {
        return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
    }    

    /* (non-PHPdoc)
     * @see VidiunObject::validateForUpdate()
     */
    public function validateForUpdate($sourceObject, $propertiesToSkip = array())
    {
        $propertiesToSkip[] = 'type';
        return parent::validateForUpdate($sourceObject, $propertiesToSkip);
    }
    
    /* (non-PHPdoc)
     * @see VidiunObject::toObject()
     */
    public function toObject($dbObject = null, $propertiesToSkip = array())
    {
        if(is_null($dbObject))
            $dbObject = new PushNotificationTemplate();
        	
        return parent::toObject($dbObject, $propertiesToSkip);
    }
}