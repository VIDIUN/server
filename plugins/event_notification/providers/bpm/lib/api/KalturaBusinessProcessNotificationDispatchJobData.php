<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage api.objects
 */
class VidiunBusinessProcessNotificationDispatchJobData extends VidiunEventNotificationDispatchJobData
{
	/**
	 * @var VidiunBusinessProcessServer
	 */
	public $server;
	
	/**
	 * @var string
	 */
	public $caseId;
	
	private static $map_between_objects = array
	(
		'caseId',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	protected function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vBusinessProcessNotificationDispatchJobData */
		parent::doFromObject($dbObject, $responseProfile);
		
		$server = $dbObject->getServer();
		$this->server = VidiunBusinessProcessServer::getInstanceByType($server->getType());
		$this->server->fromObject($server);
	}
}
