<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage api.objects
 */
class VidiunBusinessProcessCase extends VidiunObject
{
	/**
	 * @var string
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $businessProcessId;
	
	/**
	 * @var int
	 */
	public $businessProcessStartNotificationTemplateId;

	/**
	 * @var bool
	 */
	public $suspended;

	/**
	 * @var string
	 */
	public $activityId;

	private static $map_between_objects = array
	(
		'id',
		'businessProcessId',
		'suspended',
		'activityId',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
