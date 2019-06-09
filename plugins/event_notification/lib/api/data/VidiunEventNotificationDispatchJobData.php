<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.objects
 */
class VidiunEventNotificationDispatchJobData extends VidiunJobData
{
	/**
	 * @var int
	 */
	public $templateId;

	/**
	 * Define the content dynamic parameters
	 * @var VidiunKeyValueArray
	 */
	public $contentParameters;
	
	private static $map_between_objects = array
	(
		'templateId' ,
		'contentParameters',
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/**
	 * @param string $subType is the provider type
	 * @return int
	 */
	public function toSubType($subType)
	{
		return vPluginableEnumsManager::apiToCore('EventNotificationTemplateType', $subType);
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		return vPluginableEnumsManager::coreToApi('EventNotificationTemplateType', $subType);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	protected function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vEventNotificationDispatchJobData */
		parent::doFromObject($dbObject, $responseProfile);
		
		$this->contentParameters = VidiunKeyValueArray::fromKeyValueArray($dbObject->getContentParameters());
	}
}
