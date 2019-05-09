<?php
/**
 * Live Cue Point service
 *
 * @service liveCuePoint
 * @package plugins.cuePoint
 * @subpackage api.services
 * @throws VidiunErrors::SERVICE_FORBIDDEN
 */
class LiveCuePointService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('CuePoint');

		// when session is not admin, allow access to user entries only
		if (!$this->getVs() || !$this->getVs()->isAdmin()) {
			VidiunCriterion::enableTag(VidiunCriterion::TAG_USER_SESSION);
			CuePointPeer::setUserContentOnly(true);
		}
		
		if(!CuePointPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, CuePointPlugin::PLUGIN_NAME);
		
		if(!$this->getPartner()->getEnabledService(PermissionName::FEATURE_VIDIUN_LIVE_STREAM))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, 'Vidiun Live Streams');
	}

	/**
	 * Creates periodic metadata sync-point events on a live stream
	 * 
	 * @action createPeriodicSyncPoints
	 * @actionAlias liveStream.createPeriodicSyncPoints
	 * @deprecated This actions is not required, sync points are sent automatically on the stream.
	 * @param string $entryId Vidiun live-stream entry id
	 * @param int $interval Events interval in seconds 
	 * @param int $duration Duration in seconds
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::NO_MEDIA_SERVER_FOUND
	 * @throws VidiunErrors::MEDIA_SERVER_SERVICE_NOT_FOUND
	 */
	function createPeriodicSyncPoints($entryId, $interval, $duration)
	{
	}
}
