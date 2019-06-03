<?php
/**
 * Evaluates PHP statement, depends on the execution context
 * 
 * @package plugins.httpNotification
 * @subpackage api.objects
 */
class VidiunHttpNotificationObjectData extends VidiunHttpNotificationData
{
	/**
	 * Vidiun API object type
	 * @var string
	 */
	public $apiObjectType;
	
	/**
	 * Data format
	 * @var VidiunResponseType
	 */
	public $format;
	
	/**
	 * Ignore null attributes during serialization
	 * @var bool
	 */
	public $ignoreNull;
	
	/**
	 * PHP code
	 * @var string
	 */
	public $code;

	/**
	 * An array of pattern-replacement pairs used for data string regex replacements
	 * @var VidiunKeyValueArray
	 */
	public $dataStringReplacements;

	/**
	 * Serialized object, protected on purpose, used by getData
	 * @see VidiunHttpNotificationObjectData::getData()
	 * @var string
	 */
	protected $coreObject;

	static private $map_between_objects = array
	(
		'apiObjectType' => 'objectType',
		'format',
		'ignoreNull',
		'code',
		'dataStringReplacements',
	);

	/* (non-PHPdoc)
	 * @see VidiunValue::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$this->apiObjectType || !is_subclass_of($this->apiObjectType, 'VidiunObject'))
			throw new VidiunAPIException(VidiunHttpNotificationErrors::HTTP_NOTIFICATION_INVALID_OBJECT_TYPE);
			
		if(!$dbObject)
			$dbObject = new vHttpNotificationObjectData();
			
		return parent::toObject($dbObject, $skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($srcObj)
	 */
	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $srcObj vHttpNotificationObjectData */
		parent::doFromObject($srcObj, $responseProfile);
		$this->coreObject = $srcObj->getCoreObject();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunHttpNotificationData::getData()
	 */
	public function getData(vHttpNotificationDispatchJobData $jobData = null)
	{
		$coreObject = unserialize($this->coreObject);

		$apiObject = new $this->apiObjectType;
		/* @var $apiObject VidiunObject */
		$apiObject->fromObject($coreObject);
		
		$httpNotificationTemplate = EventNotificationTemplatePeer::retrieveByPK($jobData->getTemplateId());
		
		$notification = new VidiunHttpNotification();
		$notification->object = $apiObject;
		$notification->eventObjectType = vPluginableEnumsManager::coreToApi('EventNotificationEventObjectType', $httpNotificationTemplate->getObjectType());
		$notification->eventNotificationJobId = $jobData->getJobId();
		$notification->templateId = $httpNotificationTemplate->getId();
		$notification->templateName = $httpNotificationTemplate->getName();
		$notification->templateSystemName = $httpNotificationTemplate->getSystemName();
		$notification->eventType = $httpNotificationTemplate->getEventType();

		$data = '';
		switch ($this->format)
		{
			case VidiunResponseType::RESPONSE_TYPE_XML:
				$serializer = new VidiunXmlSerializer($this->ignoreNull);				
				$data = '<notification>' . $serializer->serialize($notification) . '</notification>';
				break;
				
			case VidiunResponseType::RESPONSE_TYPE_PHP:
				$serializer = new VidiunPhpSerializer($this->ignoreNull);				
				$data = $serializer->serialize($notification);
				break;
				
			case VidiunResponseType::RESPONSE_TYPE_JSON:
				$serializer = new VidiunJsonSerializer($this->ignoreNull);				
				$data = $serializer->serialize($notification);

				if($this->dataStringReplacements)
				{
					VidiunLog::info("replacing data string");
					$patterns = array();
					$replacements = array();
					foreach($this->dataStringReplacements->toArray() as $dataStringReplacement)
					{
						$patterns[] = "/" . $dataStringReplacement->key . "/";
						$replacements[] = $dataStringReplacement->value;
					}

					if(!empty($patterns))
						$data = preg_replace($patterns, $replacements, $data);
				}
				if (!$httpNotificationTemplate->getUrlEncode())
					return $data;
				
				$data = urlencode($data);
				break;
		}
		
		return "data=$data";
	}
}
