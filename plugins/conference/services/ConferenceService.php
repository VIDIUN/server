<?php

/**
 *
 * @service conference
 * @package plugins.conference
 * @subpackage api.services
 */
class ConferenceService extends VidiunBaseService {
	const CAN_REACH_EXPECTED_VALUE = 'vidiun';

	/**
	 * Allocates a conference room or returns ones that has already been allocated
	 *
	 * @action allocateConferenceRoom
	 * @actionAlias liveStream.allocateConferenceRoom
	 * @param string $entryId
	 * @param string $env
	 * @return VidiunRoomDetails
	 * @throws VidiunAPIException
	 * @beta
	 */
	public function allocateConferenceRoomAction($entryId, $env = '')
	{
		$partner = $this->getPartner();

		if (!PermissionPeer::isValidForPartner(PermissionName::FEATURE_SELF_SERVE, $this->getPartnerId()))
		{
			throw new VidiunAPIException ( APIErrors::SERVICE_FORBIDDEN, $this->serviceId.'->'.$this->actionName);
		}

		$liveEntryDb = entryPeer::retrieveByPK($entryId);
		if (!$liveEntryDb)
		{
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		}
		/**
		 * @var LiveStreamEntry $liveEntryDb
		 */
		if ($liveEntryDb->getType() != entryType::LIVE_STREAM)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_TYPE, $liveEntryDb->getName(), $liveEntryDb->getType(), entryType::LIVE_STREAM);
		}

		$existingConfRoom = $this->findExistingConferenceRoom($entryId);
		if ($existingConfRoom)
			return $existingConfRoom;
		VidiunLog::log('Could not find existing conference room');

		$liveEntryService = new LiveStreamService();
		$liveEntryService->dumpApiRequest($entryId, true);
		$lockKey = "allocate_conference_room_" . $entryId;
		$conference = vLock::runLocked($lockKey, array($this, 'allocateConferenceRoomImpl'), array($entryId, $env));
		return $conference;
	}

	public function allocateConferenceRoomImpl($entryId, $env)
	{
		//In case until this method is run under lock another process already created the conf room.
		$existingConfRoom = $this->findExistingConferenceRoom($entryId);
		if ($existingConfRoom)
			return $existingConfRoom;
		
		$partner = $this->getPartner();
		$numOfConcurrentRtcStreams = EntryServerNodePeer::retrieveByPartnerIdAndServerType($this->getPartnerId(), ConferencePlugin::getCoreValue('EntryServerNodeType', ConferenceEntryServerNodeType::CONFERENCE_ENTRY_SERVER ));
		// $partner->getMaxLiveRtcStreamInputs() will return the number configured for the user in the admin console, otherwise the default value - 2
		$maxRTCStreamInputs = $partner->getMaxLiveRtcStreamInputs();
		if ($numOfConcurrentRtcStreams >= $maxRTCStreamInputs)
		{
			throw new VidiunAPIException(VidiunErrors::LIVE_STREAM_EXCEEDED_MAX_RTC_STREAMS, $this->getPartnerId(), $maxRTCStreamInputs);
		}
		
		$serverNode = $this->findFreeServerNode($env, $partner);
		$confEntryServerNode = new ConferenceEntryServerNode();
		$confEntryServerNode->setEntryId($entryId);
		$confEntryServerNode->setServerNodeId($serverNode->getId());
		$confEntryServerNode->setServerType(ConferencePlugin::getCoreValue('EntryServerNodeType', ConferenceEntryServerNodeType::CONFERENCE_ENTRY_SERVER ));
		$confEntryServerNode->setConfRoomStatus(ConferenceRoomStatus::READY);
		$confEntryServerNode->setLastAllocationTime(time());
		$confEntryServerNode->setStatus(EntryServerNodeStatus::PLAYABLE);
		$confEntryServerNode->setPartnerId($this->getPartnerId());
		$confEntryServerNode->save();

		$outObj = $this->getRoomDetails($entryId, $confEntryServerNode, $serverNode);
		return $outObj;
	}


	protected function findExistingConferenceRoom($entryId)
	{
		$existingConfRoom = EntryServerNodePeer::retrieveByEntryIdAndServerType($entryId, ConferencePlugin::getCoreValue('EntryServerNodeType', ConferenceEntryServerNodeType::CONFERENCE_ENTRY_SERVER ));
		if ($existingConfRoom)
		{
			/**
			 * @var ConferenceEntryServerNode $existingConfRoom
			 */
			$serverNode = ServerNodePeer::retrieveByPK($existingConfRoom->getServerNodeId());
			if (!$serverNode)
				return null;
			if (!$this->canReach($serverNode))
			{
				$serverNode->setStatus(ServerNodeStatus::NOT_REGISTERED);
				$serverNode->save();
				return null;
			}

			$existingConfRoom->setLastAllocationTime(time());

			$outObj = $this->getRoomDetails($entryId, $existingConfRoom, $serverNode);
			return $outObj;
		}
		return null;
	}

	protected function findFreeServerNode($env, Partner $partner)
	{
		if (empty($env))
		{
			$env = $partner->getRTCEnv();
		}
		$serverNodes = ServerNodePeer::retrieveActiveUnoccupiedServerNodesByType(ConferencePlugin::getCoreValue('serverNodeType',ConferenceServerNodeType::CONFERENCE_SERVER), $env);
		if (!$serverNodes)
		{
			VidiunLog::debug("Could not find avaialable conference server node in pool");
			if (vConf::get('CONFERNCE_SERVER_NODE_DYNAMIC_ALLOCATION', null, null) === true)
			{
				throw new VidiunAPIException(VidiunErrors::INTERNAL_SERVERL_ERROR);
			}
			throw new VidiunAPIException(VidiunConferenceErrors::CONFERENCE_ROOMS_UNAVAILABLE);
		}
		foreach ($serverNodes as $serverNode)
		{
			/**
			 * @var ConferenceServerNode $serverNode
			 */
			if ($this->canReach($serverNode))
				return $serverNode;
			$serverNode->setStatus(ServerNodeStatus::NOT_REGISTERED);
			$serverNode->save();
		}
		throw new VidiunAPIException(VidiunConferenceErrors::CONFERENCE_ROOMS_UNREACHABLE);
	}

	protected function canReach(ConferenceServerNode $serverNode)
	{
		//TODO: make sure that HTTP protocol is available for RTC servers.
		$aliveUrl = $serverNode->getServiceBaseUrl() . "/alive";
		$content = VCurlWrapper::getContent($aliveUrl);
		if (strtolower($content) === self::CAN_REACH_EXPECTED_VALUE)
			return true;
		return false;
	}

	/**
	 * When the conf is finished this API should be called.
	 *
	 * @action finishConf
	 * @actionAlias liveStream.finishConf
	 * @param string $entryId
	 * @param int $serverNodeId
	 * @return bool
	 * @throws VidiunAPIException
	 * @beta
	 */
	public function finishConfAction($entryId, $serverNodeId = null)
	{
		$confEntryServerNode = EntryServerNodePeer::retrieveByEntryIdAndServerType($entryId, ConferencePlugin::getCoreValue('EntryServerNodeType',ConferenceEntryServerNodeType::CONFERENCE_ENTRY_SERVER));
		if ($confEntryServerNode)
		{
			if (!$confEntryServerNode->isValid())
			{
				VidiunLog::debug("conf still has grace period, not finishing");
				return false;
			}
			$confEntryServerNode->delete();
			$serverNodeId = $confEntryServerNode->getServerNodeId();
		}

		if ($serverNodeId)
		{
			$serverNode = ServerNodePeer::retrieveByPK($serverNodeId);
			if (!$serverNode)
			{
				VidiunLog::info("Could not find server node with id [$serverNodeId]");
				throw new VidiunAPIException(VidiunErrors::SERVER_NODE_NOT_FOUND_WITH_ID, $serverNodeId);
			}

			$otherEntryServerNodes = EntryServerNodePeer::retrieveByServerNodeIdAndType($serverNode->getId(), ConferencePlugin::getCoreValue('serverNodeType', ConferenceServerNodeType::CONFERENCE_SERVER));
			if (!count($otherEntryServerNodes))
			{
				VidiunLog::debug('No entry server nodes left, marking server node as not registered');
				$serverNode->setStatus(ServerNodeStatus::NOT_REGISTERED);
				$serverNode->save();
			}
		}
		return true;
	}

	/**
	 * Mark that the conference has actually started
	 *
	 * @action registerConf
	 * @actionAlias liveStream.registerConf
	 * @param string $entryId
	 * @return bool
	 * @throws VidiunAPIException
	 * @beta
	 *
	 */
	public function registerConfAction($entryId)
	{
		$confEntryServerNode = EntryServerNodePeer::retrieveByEntryIdAndServerType($entryId, ConferencePlugin::getCoreValue('EntryServerNodeType',ConferenceEntryServerNodeType::CONFERENCE_ENTRY_SERVER));
		if (!$confEntryServerNode)
		{
			throw new VidiunAPIException(VidiunErrors::ENTRY_SERVER_NODE_NOT_FOUND,$entryId, ConferencePlugin::getCoreValue('EntryServerNodeType',ConferenceEntryServerNodeType::CONFERENCE_ENTRY_SERVER));
		}
		/** @var ConferenceEntryServerNode $confEntryServerNode */
		$confEntryServerNode->incRegistered();
		return true;
	}

	/**
	 * @param $entryId
	 * @param $confRoom
	 * @return VidiunRoomDetails
	 * @throws vCoreException
	 */
	protected function getRoomDetails($entryId, ConferenceEntryServerNode $confRoom, ConferenceServerNode $serverNode)
	{
		$liveStreamEntry = entryPeer::retrieveByPK($entryId);
		/** @var LiveStreamEntry $liveStreamEntry */
		if (!$liveStreamEntry)
		{
			throw new vCoreException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->getEntryId());
		}
		$outObj = new VidiunRoomDetails();
		$outObj->serverUrl = $confRoom->buildRoomUrl($this->getPartnerId());
		$outObj->entryId = $entryId;
		$expiry = time() + vConf::get('rtc_token_expiry', null, '60');
		$rtcSecret = vConf::get('rtc_token_secret');
		$token = hash_hmac("sha256" ,$entryId . $expiry, $rtcSecret);
		$outObj->token = $token;
		$outObj->expiry = $expiry;
		$outObj->serverName = $serverNode->getHostName();
		return $outObj;
	}

}
