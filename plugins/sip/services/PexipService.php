<?php
/**
 * @service pexip
 * @package plugins.sip
 * @subpackage api.services
 */
class PexipService extends VidiunBaseService
{
	const CALL_DIRECTION_PARAM_NAME = 'call_direction';
	const CALL_DIRECTION_DIAL_IN = 'dial_in';

	/**
	 * no partner will be provided by vendors as this called externally and not from vidiun
	 * @param string $actionName
	 * @return bool
	 */
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'handleIncomingCall')
		{
			return false;
		}
		return true;
	}

	/**
	 * @action generateSipUrl
	 * @param string $entryId
	 * @param bool $regenerate
	 * @return string
	 * @throws Exception
	 */
	public function generateSipUrlAction($entryId, $regenerate = false)
	{
		if (!PermissionPeer::isValidForPartner(PermissionName::FEATURE_SIP, $this->getPartnerId()))
		{
			throw new VidiunAPIException (APIErrors::FEATURE_FORBIDDEN, $this->serviceId . '->' . $this->actionName);
		}

		$pexipConfig = vPexipUtils::initAndValidateConfig();

		/** @var LiveStreamEntry $dbLiveEntry */
		$dbLiveEntry = vPexipUtils::validateAndRetrieveEntry($entryId);

		if ($regenerate)
		{
			vPexipHandler::deleteCallObjects($dbLiveEntry, $pexipConfig);
		}

		$sipToken = vPexipUtils::generateSipToken($dbLiveEntry, $pexipConfig, $regenerate);
		list ($roomId, $primaryAdpId, $secondaryAdpId) = vPexipHandler::createCallObjects($dbLiveEntry, $pexipConfig, $sipToken);

		$dbLiveEntry->setSipToken($sipToken);
		$dbLiveEntry->setSipRoomId($roomId);
		$dbLiveEntry->setPrimaryAdpId($primaryAdpId);
		$dbLiveEntry->setSecondaryAdpId($secondaryAdpId);
		$dbLiveEntry->setIsSipEnabled(true);
		$dbLiveEntry->save();

		return $sipToken;
	}

	/**
	 * @action handleIncomingCall
	 * @return bool
	 */
	public function handleIncomingCallAction()
	{
		$response = new VidiunSipResponse();
		$response->action = 'reject';

		try
		{
			$pexipConfig = vPexipUtils::initAndValidateConfig();
		}
		catch(Exception $e)
		{
			VidiunLog::err($e->getMessage());
			return $response;
		}

		$queryParams = vPexipUtils::validateAndGetQueryParams();
		if(!$queryParams)
		{
			return $response;
		}

		if ($queryParams[self::CALL_DIRECTION_PARAM_NAME] != self::CALL_DIRECTION_DIAL_IN)
		{
			VidiunLog::debug(self::CALL_DIRECTION_PARAM_NAME . ' ' . $queryParams[self::CALL_DIRECTION_PARAM_NAME] .' not validated!');
			$response->action = 'done';
			return $response;
		}

		VidiunLog::debug(self::CALL_DIRECTION_PARAM_NAME . ' validated!');
		try
		{
			$dbLiveEntry = vPexipUtils::retrieveAndValidateEntryForSipCall($queryParams, $pexipConfig);
		}
		catch(Exception $e)
		{
			VidiunLog::err('Error validating and retrieving Entry for sip Call');
			return $response;
		}

		/** @var LiveStreamEntry $dbLiveEntry */
		if (!$dbLiveEntry)
		{
			VidiunLog::err('Live entry for call not Validated!');
			return $response;
		}

		if(!vPexipUtils::validateLicensesAvailable($pexipConfig))
		{
			return $response;
		}

		$sipEntryServerNode = vPexipUtils::createSipEntryServerNode($dbLiveEntry, $dbLiveEntry->getSipRoomId(), $dbLiveEntry->getPrimaryAdpId(), $dbLiveEntry->getSecondaryAdpId());
		/** @var  SipEntryServerNode $sipEntryServerNode */
		if (!$sipEntryServerNode)
		{
			VidiunLog::debug("Could not create or retrieve SipEntryServerNode.");
			return $response;
		}

		$response->action = 'done';
		return $response;
	}

	/**
	 * @action listRooms
	 * @param int $offset
	 * @param int $pageSize
	 * @param bool $activeOnly
	 * @return VidiunStringValueArray
	 * @throws VidiunAPIException
	 */
	public function listRoomsAction($offset = 0, $pageSize = 500, $activeOnly = false )
	{
		if (!PermissionPeer::isValidForPartner(PermissionName::FEATURE_SIP, $this->getPartnerId()))
		{
			throw new VidiunAPIException (APIErrors::FEATURE_FORBIDDEN, $this->serviceId . '->' . $this->actionName);
		}
		$pexipConfig = vPexipUtils::initAndValidateConfig();
		$res = vPexipHandler::listRooms($offset, $pageSize, $pexipConfig, $activeOnly);
		return VidiunStringValueArray::fromDbArray(array(json_encode($res)));
	}

}