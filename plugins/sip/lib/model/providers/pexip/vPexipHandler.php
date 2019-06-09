<?php
/**
 * @package plugins.sip
 * @subpackage model.pexip
 */

class vPexipHandler
{
	const ROOM_PREFIX = '/api/admin/configuration/v1/conference/';
	const ADP_PREFIX = '/api/admin/configuration/v1/automatic_participant/';
	const ACTIVE_ROOM_PREFIX = '/api/admin/status/v1/conference/';
	const ROOM_NAME_KEY = 'roomName';
	const ROOM_ID_KEY = 'id';
	const ADP_ALIAS_KEY = 'alias';
	const ADP_ID_KEY = 'id';

	const ALREADY_EXISTS_PATTERN = '/already exists/';

	/**
	 * @param LiveStreamEntry $dbLiveEntry
	 * @param $pexipConfig
	 * @param $alias
	 * @return array
	 * @throws VidiunAPIException
	 */
	public static function createCallObjects(LiveStreamEntry $dbLiveEntry, $pexipConfig, $alias)
	{
		$roomId = self::addVirtualRoom($dbLiveEntry, $pexipConfig, $alias);
		if(!$roomId)
		{
			throw new VidiunAPIException(VidiunErrors::PEXIP_ROOM_CREATION_FAILED, $dbLiveEntry->getId());
		}
		$primaryAdpId = null;
		if($dbLiveEntry->getPrimaryBroadcastingUrl())
		{
			$primaryRtmp = $dbLiveEntry->getPrimaryBroadcastingUrl() . '/' . $dbLiveEntry->getStreamName();
			$primaryRtmp  = str_replace("%i", "1", $primaryRtmp);
			$primaryAdpId = self::addADP($dbLiveEntry, $roomId, $primaryRtmp, 'Primary', $pexipConfig);
			if(!$primaryAdpId)
			{
				throw new VidiunAPIException(VidiunErrors::PEXIP_ADP_CREATION_FAILED, $dbLiveEntry->getId());
			}
		}

		$secondaryAdpId = null;
		if($dbLiveEntry->getSecondaryBroadcastingUrl())
		{
			$secondaryRtmp = $dbLiveEntry->getSecondaryBroadcastingUrl() . '/' . $dbLiveEntry->getStreamName();
			$secondaryRtmp = str_replace("%i", "1", $secondaryRtmp );
			$secondaryAdpId = self::addADP($dbLiveEntry, $roomId, $secondaryRtmp, 'Secondary', $pexipConfig);

			if(!$secondaryAdpId)
			{
				throw new VidiunAPIException(VidiunErrors::PEXIP_ADP_CREATION_FAILED, $dbLiveEntry->getId());
			}
		}

		$sipEntryServerNode = vPexipUtils::createSipEntryServerNode($dbLiveEntry, $roomId, $primaryAdpId, $secondaryAdpId);
		/** @var  SipEntryServerNode $sipEntryServerNode */
		if(!$sipEntryServerNode)
		{
			throw new VidiunAPIException(VidiunErrors::SIP_ENTRY_SERVER_NODE_CREATION_FAILED, $dbLiveEntry->getId());
		}

		return array($roomId, $primaryAdpId, $secondaryAdpId);
	}

	/**
	 * @param LiveStreamEntry $entry
	 * @param $pexipConfig
	 * @param $alias
	 * @return mixed|null
	 */
	protected static function addVirtualRoom(LiveStreamEntry $entry, $pexipConfig, $alias)
	{
		$roomName = vPexipUtils::getRoomName($entry, $pexipConfig);
		$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ROOM_PREFIX;
		VidiunLog::info("Creating Virtual Room with name $roomName for entry " . $entry->getId());
		$data = array(
			'name' => $roomName,
			'service_type' => 'conference',
			'aliases' => array(array('alias' => $alias)),
			'participant_limit' => 3
		);
		$curlWrapper = self::initPexipCurlWrapper(HttpMethods::POST, $pexipConfig, $data);

		$result = $curlWrapper->doExec($url);
		VidiunLog::info('Result is ' . print_r($result, true));
		$virtualRoomId = null;
		if($curlWrapper->getHttpCode() == VCurlHeaderResponse::HTTP_STATUS_CREATED)
		{
			$virtualRoomId = vPexipUtils::extractIdFromCreatedResult($result, $url, $curlWrapper->getInfo(CURLINFO_HEADER_SIZE));
		}
		else
		{
			preg_match(self::ALREADY_EXISTS_PATTERN, $result, $alreadyExistsMatch);
			if(!empty($alreadyExistsMatch))
			{
				VidiunLog::info('Virtual Room for entry ' . $entry->getId() . ' already exists.');
				$virtualRoom = self::getVirtualRoom($pexipConfig, self::ROOM_NAME_KEY, $roomName);
				if($virtualRoom && isset($virtualRoom[self::ROOM_ID_KEY]))
				{
					if(!self::shouldUpdateRoomAlias($virtualRoom, $alias) || self::updateRoomAlias($virtualRoom[self::ROOM_ID_KEY], $pexipConfig, $alias))
					{
						$virtualRoomId = $virtualRoom[self::ROOM_ID_KEY];
					}
				}
			}
			else
			{
				vPexipUtils::logError($curlWrapper, $url);
			}
		}
		$curlWrapper->close();
		return $virtualRoomId;
	}


	/**
	 * @param $pexipConfig
	 * @param null $key
	 * @param null $value
	 * @return mixed|null
	 */
	public static function getVirtualRoom($pexipConfig, $key = null, $value = null)
	{
		$virtualRoom = null;
		switch ($key)
		{
			case self::ROOM_NAME_KEY:
				$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ROOM_PREFIX . '?name=' . $value;
				break;
			case self::ROOM_ID_KEY:
				$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ROOM_PREFIX . "$value/";
				break;
			default:
				return $virtualRoom;
		}

		VidiunLog::info("Getting Virtual Room info for $key $value");

		$curlWrapper = self::initPexipCurlWrapper(HttpMethods::GET, $pexipConfig);
		$result = $curlWrapper->exec($url);

		VidiunLog::info('Result From Pexip Server : ' . print_r($result, true));
		if($result && $curlWrapper->getHttpCode() == VCurlHeaderResponse::HTTP_STATUS_OK)
		{
			$virtualRoom = vPexipUtils::extractObjectFromdResult($result);
			if(!$virtualRoom)
			{
				VidiunLog::info("Didn't find Virutal-Room matching to $key $value");
			}
		}
		else
		{
			vPexipUtils::logError($curlWrapper, $url);
		}
		$curlWrapper->close();
		return $virtualRoom;
	}

	/**
	 * @param $pexipConfig
	 * @param null $key
	 * @param null $value
	 * @return mixed|null
	 */
	protected static function getADP($pexipConfig, $key = null, $value = null)
	{
		$adp = null;
		switch ($key)
		{
			case self::ADP_ALIAS_KEY:
				$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ADP_PREFIX . '?alias=' . urlencode($value);
				break;
			case self::ADP_ID_KEY:
				$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ADP_PREFIX . "$value/";
				break;
			default:
				return $adp;
		}

		VidiunLog::info("Getting ADP info for $key $value");
		$curlWrapper = self::initPexipCurlWrapper(HttpMethods::GET, $pexipConfig);
		$result = $curlWrapper->exec($url);

		VidiunLog::info('Result From Pexip Server : ' . print_r($result, true));
		if($result && $curlWrapper->getHttpCode() == VCurlHeaderResponse::HTTP_STATUS_OK)
		{
			$adp = vPexipUtils::extractObjectFromdResult($result);
			if(!$adp)
			{
				VidiunLog::info("Didn't find ADP matching to $key $value");
			}
		}
		else
		{
			vPexipUtils::logError($curlWrapper, $url);
		}
		$curlWrapper->close();
		return $adp;
	}

	/**
	 * @param $adp
	 * @param $roomId
	 * @return bool
	 */
	protected static function shouldUpdateAdp($adp, $roomId)
	{
		$roomAddress = self::ROOM_PREFIX . "$roomId/";
		if(isset($adp['conference']))
		{
			foreach ($adp['conference'] as $connectedRoom)
			{
				if($connectedRoom == $roomAddress)
					return false;
			}
		}
		return true;
	}

	/**
	 * @param $room
	 * @param $alias
	 * @return bool
	 */
	protected static function shouldUpdateRoomAlias($room, $alias)
	{
		if(isset($room['aliases']))
		{
			foreach ($room['aliases'] as $aliasItem)
			{
				if($aliasItem['alias'] === $alias)
				{
					return false;
				}
			}
		}
		return true;
	}


	/**
	 * @param LiveStreamEntry $entry
	 * @param $roomId
	 * @param $participantAddress
	 * @param $name
	 * @param $pexipConfig
	 * @return null
	 */
	protected static function addADP(LiveStreamEntry $entry, $roomId, $participantAddress, $name, $pexipConfig)
	{
		$adpId = null;
		VidiunLog::info("Creating RTMP-ADP $name to Virtual Room $roomId");

		$adpData = array(
			'alias' => $participantAddress,
			'remote_display_name' => $entry->getId() . "_{$name}_ADP",
			'description' => "ADP for $name " . $entry->getId(),
			'protocol' => 'rtmp',
			'role' => 'guest',
			'conference' => array($pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ROOM_PREFIX . "$roomId/"),
			'streaming' => 1,
			'keep_conference_alivei_if_multiple' => 1
		);
		$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ADP_PREFIX;
		$curlWrapper = self::initPexipCurlWrapper(HttpMethods::POST, $pexipConfig, $adpData);
		$result = $curlWrapper->doExec($url);
		VidiunLog::info('Result for ADP creation is ' . print_r($result, true));

		if($curlWrapper->getHttpCode() == VCurlHeaderResponse::HTTP_STATUS_CREATED)
		{
			$adpId = vPexipUtils::extractIdFromCreatedResult($result, $url, $curlWrapper->getInfo(CURLINFO_HEADER_SIZE));
		}
		else
		{
			preg_match(self::ALREADY_EXISTS_PATTERN, $result, $alreadyExistsMatch);
			if(!empty($alreadyExistsMatch))
			{
				VidiunLog::info('ADP for alias ' . $participantAddress . ' already exists.');
				$adp = self::getADP($pexipConfig, self::ADP_ALIAS_KEY, $participantAddress);
				if($adp && isset($adp[self::ADP_ID_KEY]))
				{
					if(!self::shouldUpdateAdp($adp, $roomId) || self::updateADP($adp[self::ADP_ID_KEY], $roomId, $pexipConfig))
					{
						$adpId = $adp[self::ADP_ID_KEY];
					}
				}
			}
			else
			{
				vPexipUtils::logError($curlWrapper, $url);
			}
		}
		$curlWrapper->close();
		return $adpId;
	}

	/**
	 * @param $adpIp
	 * @param $roomId
	 * @param $pexipConfig
	 * @return bool
	 */
	protected static function updateADP($adpIp, $roomId, $pexipConfig)
	{
		VidiunLog::info("Updating ADP $adpIp adding Virtual Room $roomId");
		$result = true;
		$adpData = array(
			'conference' => array($pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ROOM_PREFIX . "$roomId/"),
		);
		$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ADP_PREFIX . "$adpIp/";

		$curlWrapper = self::initPexipCurlWrapper(HttpMethods::UPDATE, $pexipConfig, $adpData);
		$execResult = $curlWrapper->doExec($url);
		VidiunLog::info('Result FOR ADP update is ' . print_r($execResult, true));

		if($curlWrapper->getHttpCode() != VCurlHeaderResponse::HTTP_STATUS_ACCEPTED)
		{
			vPexipUtils::logError($curlWrapper, $url);
			$result = false;
		}
		$curlWrapper->close();
		return $result;
	}

	/**
	 * @param $roomId
	 * @param $pexipConfig
	 * @param $alias
	 * @return bool
	 */
	protected static function updateRoomAlias($roomId, $pexipConfig, $alias)
	{
		VidiunLog::info("Updating Room $roomId");
		$result = true;
		$roomData = array(
			'aliases' => array(array('alias' => $alias))
		);
		$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ROOM_PREFIX . "$roomId/";

		$curlWrapper = self::initPexipCurlWrapper(HttpMethods::UPDATE, $pexipConfig, $roomData);
		$execResult = $curlWrapper->doExec($url);
		VidiunLog::info('Result for ROOM update is' . print_r($execResult, true));

		if($curlWrapper->getHttpCode() != VCurlHeaderResponse::HTTP_STATUS_ACCEPTED)
		{
			vPexipUtils::logError($curlWrapper, $url);
			$result = false;
		}
		$curlWrapper->close();
		return $result;
	}

	/**
	 * @param LiveStreamEntry $liveEntry
	 * @param $pexipConfig
	 * @throws PropelException
	 */
	public static function deleteCallObjects(LiveStreamEntry $liveEntry, $pexipConfig)
	{
		if($liveEntry->getSipRoomId())
		{
			VidiunLog::info('Deleting Virtual room with id ' . $liveEntry->getSipRoomId());
			self::deleteItem($liveEntry->getSipRoomId(), self::ROOM_PREFIX, $pexipConfig);
		}
		if($liveEntry->getPrimaryAdpId())
		{
			VidiunLog::info('Deleting Primary ADP with id ' . $liveEntry->getPrimaryAdpId());
			self::deleteItem($liveEntry->getPrimaryAdpId(), self::ADP_PREFIX, $pexipConfig);
		}
		if($liveEntry->getSecondaryAdpId())
		{
			VidiunLog::info('Deleting Secondary ADP with id ' . $liveEntry->getSecondaryAdpId());
			self::deleteItem($liveEntry->getSecondaryAdpId(), self::ADP_PREFIX, $pexipConfig);
		}

		$sipEntryServerNode = EntryServerNodePeer::retrieveByEntryIdAndServerType($liveEntry->getEntryId(), SipPlugin::getCoreValue('EntryServerNodeType', SipEntryServerNodeType::SIP_ENTRY_SERVER));
		if($sipEntryServerNode)
		{
			$sipEntryServerNode->delete();
		}
	}

	/**
	 * @param $itemId
	 * @param $path
	 * @param $pexipConfig
	 * @return bool
	 */
	protected static function deleteItem($itemId, $path, $pexipConfig)
	{
		$result = true;
		$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . $path . "$itemId/";
		$curlWrapper = self::initPexipCurlWrapper(HttpMethods::DELETE, $pexipConfig);
		$results = $curlWrapper->exec($url);
		$httpCode = $curlWrapper->getHttpCode();
		VidiunLog::info('HTTP Request httpCode [' . $httpCode . "] Results [$results]");
		if($httpCode == VCurlHeaderResponse::HTTP_STATUS_NO_CONTENT)
		{
			VidiunLog::info("Pexip Item With id $itemId was deleted succesfully");
		}
		else
		{
			vPexipUtils::logError($curlWrapper, $url);
			$result = false;
		}
		$curlWrapper->close();
		return $result;
	}

	/**
	 * @param $offset
	 * @param $pageSize
	 * @param $pexipConfig
	 * @param $activeOnly
	 * @return array|mixed
	 */
	public static function listRooms($offset, $pageSize, $pexipConfig, $activeOnly = false)
	{
		$listResult = array();
		VidiunLog::info('Fetching Virtual Rooms');
		$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ROOM_PREFIX . '?service_type=conference&offset=' . $offset . '&limit=' . $pageSize;
		if($activeOnly)
		{
			$url = $pexipConfig[vPexipUtils::CONFIG_API_ADDRESS] . self::ACTIVE_ROOM_PREFIX . '?service_type=conference&offset=' . $offset . '&limit=' . $pageSize;
		}

		$curlWrapper = self::initPexipCurlWrapper(HttpMethods::GET, $pexipConfig);
		$result = $curlWrapper->exec($url);
		$httpCode = $curlWrapper->getHttpCode();
		VidiunLog::info('HTTP Request httpCode [' . $httpCode . ']');
		if(!$result || $httpCode != VCurlHeaderResponse::HTTP_STATUS_OK)
		{
			vPexipUtils::logError($curlWrapper, $url);
		}
		else
		{
			$listResult = json_decode($result, true);
			VidiunLog::info('Retrieved virtual Rooms: ' . $listResult[vPexipUtils::PARAM_META][vPexipUtils::PARAM_TOTAL_COUNT]);
		}
		$curlWrapper->close();
		return $listResult;
	}

	/**
	 * @param $method
	 * @param $pexipConfig
	 * @param null $data
	 * @return VCurlWrapper
	 */
	protected static function initPexipCurlWrapper($method, $pexipConfig, $data = null)
	{
		$curlWrapper = new VCurlWrapper();
		$curlWrapper->setOpts(array(CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_USERPWD => $pexipConfig[vPexipUtils::CONFIG_USER_NAME] . ':' . $pexipConfig[vPexipUtils::CONFIG_PASSWORD],
			CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_VERBOSE => 0,
			CURLOPT_HEADER => 1));

		switch($method)
		{
			case HttpMethods::POST:
				$curlWrapper->setOpt(CURLOPT_POST, 1);
				break;
			case HttpMethods::DELETE:
				$curlWrapper->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			case HttpMethods::UPDATE:
				$curlWrapper->setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
				break;
			default:
				break;
		}

		if($data)
		{
			$curlWrapper->setOpt(CURLOPT_POSTFIELDS, json_encode($data));
		}

		return $curlWrapper;
	}
}