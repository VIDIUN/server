<?php

/**
 * Notification Service
 *
 * @service notification
 * @package api
 * @subpackage services
 * @deprecated deprecated in favour of eventNotificationTemplate 
 */
class NotificationService extends VidiunBaseService 
{
	/**
	 * Return the notifications for a specific entry id and type
	 * 
	 * @action getClientNotification
	 * @param string $entryId
	 * @param VidiunNotificationType $type
	 * @return VidiunClientNotification
	 */
	function getClientNotificationAction($entryId, $type)
	{
		// in case of a multirequest, a mediaService.addFromUploadedFile may fail and therefore the resulting entry id will be empty
		// in such a case return immidiately without looking for the notification
		if ($entryId == '')
		{
            throw new VidiunAPIException(VidiunErrors::NOTIFICATION_FOR_ENTRY_NOT_FOUND, $entryId);
		}
		
		$notifications = BatchJobPeer::retrieveByEntryIdAndType($entryId, BatchJobType::NOTIFICATION, $type);
		
		// FIXME: throw error if not found		
		if (count($notifications) == 0)
		{
            throw new VidiunAPIException(VidiunErrors::NOTIFICATION_FOR_ENTRY_NOT_FOUND, $entryId);
		}
		
	    $notification = $notifications[0];

	    $partnerId = $this->getPartnerId();
	    
	    $nofication_config_str = null;
		list($nofity, $nofication_config_str) = myPartnerUtils::shouldNotify($partnerId);
		
		if (!$nofity)
			return new VidiunClientNotification();
			
		$nofication_config = myNotificationsConfig::getInstance($nofication_config_str);
		$nofity_send_type = $nofication_config->shouldNotify($type);
	    
	    if ($nofity_send_type != myNotificationMgr::NOTIFICATION_MGR_SEND_SYNCH && $nofity_send_type != myNotificationMgr::NOTIFICATION_MGR_SEND_BOTH)
	    	return new VidiunClientNotification();
	    
		$partner = PartnerPeer::retrieveByPK($partnerId);
		list($url, $signatureKey) = myNotificationMgr::getPartnerNotificationInfo ($partner );
		
		list($params, $rawSignature) = myNotificationMgr::prepareNotificationData($url, $signatureKey, $notification, null);
		$serializedParams = http_build_query( $params , "" , "&" );
		
		$result = new VidiunClientNotification();
		$result->url = $url;
		$result->data = $serializedParams;
		
		return $result;
	}
}
