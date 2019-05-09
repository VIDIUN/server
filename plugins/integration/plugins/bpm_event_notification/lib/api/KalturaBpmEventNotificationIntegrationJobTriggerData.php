<?php
/**
 * @package plugins.bpmEventNotificationIntegration
 * @subpackage api.objects
 */
class VidiunBpmEventNotificationIntegrationJobTriggerData extends VidiunIntegrationJobTriggerData
{
	/**
	 * VidiunBusinessProcessNotificationTemplate id
	 * @var int
	 */
	public $templateId;
	
	/**
	 * @var string
	 */
	public $businessProcessId;
	
	/**
	 * Execution unique id
	 * @var string
	 */
	public $caseId;
	
	private static $map_between_objects = array
	(
		'templateId' ,
		'businessProcessId' ,
		'caseId' ,
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
