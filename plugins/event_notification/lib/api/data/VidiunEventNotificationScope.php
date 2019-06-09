<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.objects
 */
class VidiunEventNotificationScope extends VidiunScope
{
	/**
	 * @var string
	 */
	public $objectId;

	/**
	 * @var VidiunEventNotificationEventObjectType
	 */
	public $scopeObjectType;

	public function toObject($objectToFill = null, $propsToSkip = array())
	{
		if (is_null($objectToFill))
			$objectToFill = new vEventNotificationScope();

		/** @var vEventNotificationScope $objectToFill */
		$objectToFill = parent::toObject($objectToFill);

		$objectClassName = VidiunPluginManager::getObjectClass('EventNotificationEventObjectType', vPluginableEnumsManager::apiToCore('EventNotificationEventObjectType', $this->scopeObjectType));
		$peerClass = $objectClassName.'Peer';
		$objectId = $this->objectId;
		if (class_exists($peerClass))
		{
			$objectToFill->setObject($peerClass::retrieveByPk($objectId));
		}
		else
		{
			$b = new $objectClassName();
			$peer = $b->getPeer();
			$object = $peer::retrieveByPK($objectId);
			$objectToFill->setObject($object);
		}

		if (is_null($objectToFill->getObject()))
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $this->objectId);

		return $objectToFill;
	}
}
