<?php
/**
 * @package plugins.httpNotification
 * @subpackage api.objects
 */
class VidiunHttpNotificationDataText extends VidiunHttpNotificationData
{
	/**
	 * @var VidiunStringValue
	 */
	public $content;
	
	/**
	 * It's protected on purpose, used by getData
	 * @see VidiunHttpNotificationDataText::getData()
	 * @var string
	 */
	protected $data;
	
	private static $map_between_objects = array
	(
		'content',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if(is_null($dbObject))
			$dbObject = new vHttpNotificationDataText();
			
		return parent::toObject($dbObject, $propertiesToSkip);
	}
	 
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vHttpNotificationDataText */
		parent::doFromObject($dbObject, $responseProfile);
		
		if($this->shouldGet('content', $responseProfile))
		{
			$contentType = get_class($dbObject->getContent());
			switch ($contentType)
			{
				case 'vStringValue':
					$this->content = new VidiunStringValue();
					break;
					
				case 'vEvalStringField':
					$this->content = new VidiunEvalStringField();
					break;
					
				default:
					$this->content = VidiunPluginManager::loadObject('VidiunStringValue', $contentType);
					break;
			}
			
			if($this->content)
				$this->content->fromObject($dbObject->getContent());
		}
			
		if($this->shouldGet('data', $responseProfile))
			$this->data = $dbObject->getData();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunHttpNotificationData::getData()
	 */
	public function getData(vHttpNotificationDispatchJobData $jobData = null)
	{
		return $this->data;
	}
}
