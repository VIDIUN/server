<?php
/**
 * @package plugins.sip
 * @subpackage model.pexip
 */

class vPexipUtils
{
	const CONFIG_LICENSE_THRESHOLD = 'licenseThreshold';
	const CONFIG_HOST_URL = 'hostUrl';
	const CONFIG_API_ADDRESS = 'apiAddress';
	const CONFIG_USER_NAME = 'userName';
	const CONFIG_PASSWORD = 'password';
	const SIP_URL_DELIMITER = '@';
	const PARAM_META = 'meta';
	const PARAM_TOTAL_COUNT = 'total_count';
	const PARAM_LOCAL_ALIAS = 'local_alias';
	const LICENSES_PER_CALL = 3;
	/**
	 * @return bool|null
	 * @throws Exception
	 * @throws VidiunAPIException
	 */
	public static function initAndValidateConfig()
	{
		if (vConf::hasMap('sip') && $pexipConfig = vConf::get('pexip', 'sip'))
		{
			return $pexipConfig;
		}

		throw new VidiunAPIException(VidiunErrors::PEXIP_MAP_NOT_CONFIGURED);
	}

	/**
	 * @param LiveStreamEntry $dbLiveEntry
	 * @param bool $regenerate
	 * @return string
	 */
	public static function generateSipToken(LiveStreamEntry $dbLiveEntry, $pexipConfig, $regenerate = false)
	{
		if (!$dbLiveEntry->getSipToken() || $regenerate)
		{
			$addition = str_pad(substr((string)microtime(true) * 10000, -5), 5, '0', STR_PAD_LEFT);
			return $dbLiveEntry->getPartnerId() . $addition . self::SIP_URL_DELIMITER . $pexipConfig[self::CONFIG_HOST_URL];
		}
		return $dbLiveEntry->getSipToken();
	}

	/**
	 * @param $entry
	 * @param $pexipConfig
	 * @return string
	 */
	public static function getRoomName(LiveEntry $entry, $pexipConfig)
	{
		return $entry->getId() . '@' . $pexipConfig[self::CONFIG_HOST_URL];
	}

	/**
	 * @param $entryId
	 * @return entry
	 * @throws VidiunAPIException
	 */
	public static function validateAndRetrieveEntry($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
		{
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		}

		if ($dbEntry->getType() != entryType::LIVE_STREAM)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_TYPE, $dbEntry->getName(), $dbEntry->getType(), entryType::LIVE_STREAM);
		}

		return $dbEntry;
	}

	/**
	 * @param $queryParams
	 * @param $pexipConfig
	 * @return bool|entry
	 */
	public static function retrieveAndValidateEntryForSipCall($queryParams, $pexipConfig)
	{
		list($partnerId, $sipToken ) = self::extractPartnerIdAndSipTokenFromAddress($queryParams, $pexipConfig);
		if (!$partnerId || !$sipToken )
		{
			return false;
		}

		myPartnerUtils::resetAllFilters();
		$c = VidiunCriteria::create(entryPeer::OM_CLASS);
		$sipFilter = new vSipAdvancedFilter();
		$sipFilter->setSipToken($sipToken);
		$entryFilter = new entryFilter();
		$entryFilter->setAdvancedSearch($sipFilter);
		$entryFilter->setPartnerSearchScope($partnerId);
		$c->attachFilter($entryFilter);
		$dbLiveEntry = entryPeer::doSelectOne($c);

		if (!$dbLiveEntry)
		{
			$msg = "Entry was not found for sip token $sipToken";
			VidiunLog::err($msg);
			return false;
		}

		if (!PermissionPeer::isValidForPartner(PermissionName::FEATURE_SIP, $dbLiveEntry->getPartnerId()))
		{
			$msg = 'Sip Feature is not enabled for partner ' . $dbLiveEntry->getPartnerId();
			VidiunLog::err($msg);
			return false;
		}

		if (!$dbLiveEntry instanceof LiveStreamEntry)
		{
			$msg = 'Entry ' . $dbLiveEntry->getId() . ' is not of type LiveStreamEntry.';
			VidiunLog::err($msg);
			return false;
		}

		if (!$dbLiveEntry->getIsSipEnabled())
		{
			$msg = 'Sip flag is not enabled for entry ' . $dbLiveEntry->getId() . ' - generateSipUrl action should be called before connecting to entry';
			VidiunLog::err($msg);
			return false;
		}

		if ($dbLiveEntry->isCurrentlyLive(false))
		{
			$msg = 'Entry Is currently Live. will not allow call.';
			VidiunLog::err($msg);
			return false;
		}

		if (!$dbLiveEntry->getSipRoomId())
		{
			$msg = 'Missing Sip Room Id - Please generate sip url before connecting to entry';
			VidiunLog::err($msg);
			return false;
		}

		if (!$dbLiveEntry->getPrimaryAdpId() && !$dbLiveEntry->getSecondaryAdpId())
		{
			$msg = 'Missing ADPs - Please generate sip url before connecting to entry';
			return false;
		}

		return $dbLiveEntry;
	}

	/**
	 * @param $queryParams
	 * @return array
	 */
	protected static function extractPartnerIdAndSipTokenFromAddress($queryParams)
	{
		VidiunLog::debug('Extracting entry sip token from local_alias: ' . $queryParams[self::PARAM_LOCAL_ALIAS]);
		$intIdPattern = '/(?<=sip:)(.*)/';
		preg_match($intIdPattern, $queryParams[self::PARAM_LOCAL_ALIAS], $matches);
		if (!empty($matches))
		{
			$parts = explode(self::SIP_URL_DELIMITER, $matches[0]);
			if (!empty($parts))
			{
				$partnerId = substr($parts[0], 0, -5);
				VidiunLog::debug("Extracted partnerId and sipToken : [$partnerId ,$matches[0]]");
				return array($partnerId, $matches[0]);
			}
		}
		VidiunLog::err('Could not extract PartnerId and SipToken from local_alias');
		return array();

	}

	/**
	 * @param LiveEntry $entry
	 * @param $roomId
	 * @param $primaryAdpId
	 * @param $secondaryAdpId
	 * @return bool|EntryServerNode|mixed
	 */
	public static function createSipEntryServerNode(LiveEntry $entry, $roomId, $primaryAdpId, $secondaryAdpId)
	{
		$connectedEntryServerNodes = EntryServerNodePeer::retrieveByEntryIdAndStatuses($entry->getId(), EntryServerNodePeer::$connectedServerNodeStatuses);
		if (count($connectedEntryServerNodes))
		{
			VidiunLog::info('Entry [' . $entry->getId() . '] is Live and Active. can\'t create SipEntryServerNode.');
			return false;
		}

		$sipEntryServerNode = EntryServerNodePeer::retrieveByEntryIdAndServerType($entry->getId(), SipPlugin::getCoreValue('EntryServerNodeType', SipEntryServerNodeType::SIP_ENTRY_SERVER));
		if ($sipEntryServerNode)
		{
			VidiunLog::debug('SipEntryServerNode already created for entry '. $entry->getId() );
			return $sipEntryServerNode;
		}

		$lockKey = 'allocate_sip_room_' . $entry->getId();
		$sipEntryServerNode = vLock::runLocked($lockKey, array('vPexipUtils', 'createSipEntryServerNodeImpl'), array($entry, $roomId, $primaryAdpId, $secondaryAdpId));
		return $sipEntryServerNode;

	}

	/**
	 * @param $entry
	 * @param $roomId
	 * @param $primaryAdpId
	 * @param $secondaryAdpId
	 * @return EntryServerNode|SipEntryServerNode
	 * @throws PropelException
	 */
	public static function createSipEntryServerNodeImpl($entry, $roomId, $primaryAdpId, $secondaryAdpId)
	{
		//In case until this method is run under lock another process already created the sipEntryServerNode.
		$sipEntryServerNode = EntryServerNodePeer::retrieveByEntryIdAndServerType($entry->getId(), SipPlugin::getCoreValue('EntryServerNodeType', SipEntryServerNodeType::SIP_ENTRY_SERVER));
		if ($sipEntryServerNode)
		{
			VidiunLog::debug('SipEntryServerNode ' . $sipEntryServerNode->getId() . " already created for entry $entry->getId() ");
			return $sipEntryServerNode;
		}

		$serverNode = ServerNodePeer::retrieveActiveServerNode(null, null, SipPlugin::getCoreValue('serverNodeType', SipServerNodeType::SIP_SERVER));
		$sipEntryServerNode = new SipEntryServerNode();
		$sipEntryServerNode->setEntryId($entry->getId());
		$sipEntryServerNode->setServerNodeId($serverNode->getId());
		$sipEntryServerNode->setServerType(SipPlugin::getCoreValue('EntryServerNodeType', SipEntryServerNodeType::SIP_ENTRY_SERVER));
		$sipEntryServerNode->setStatus(SipEntryServerNodeStatus::CREATED);
		$sipEntryServerNode->setPartnerId($entry->getPartnerId());
		$sipEntryServerNode->setSipRoomId($roomId);
		$sipEntryServerNode->setSipPrimaryAdpId($primaryAdpId);
		$sipEntryServerNode->setSipSecondaryAdpId($secondaryAdpId);
		$sipEntryServerNode->save();

		return $sipEntryServerNode;
	}

	/**
	 * @return array|bool
	 */
	public static function validateAndGetQueryParams()
	{
		$queryParams = array();
		parse_str($_SERVER['QUERY_STRING'], $queryParams);

		VidiunLog::debug('Retrieved qurey params :' . print_r($queryParams, true));
		if (!isset($queryParams[self::PARAM_LOCAL_ALIAS]))
		{
			VidiunLog::debug('Missing ' . self::PARAM_LOCAL_ALIAS . ' param');
			return false;
		}
		// TODO - validate origin call came from pexip server
		return $queryParams;
	}

	/**
	 * @param $pexipConfig
	 * @return bool
	 */
	public static function validateLicensesAvailable($pexipConfig)
	{
		$result = vPexipHandler::listRooms(0, 1, $pexipConfig, true);
		if (empty($result))
		{
			VidiunLog::debug('Could Not retrieve active rooms - available licenes not validated!');
			return false;
		}
		if ( ( $result[self::PARAM_META][self::PARAM_TOTAL_COUNT] * self::LICENSES_PER_CALL ) >= $pexipConfig[self::CONFIG_LICENSE_THRESHOLD])
		{
			VidiunLog::debug('Max number of active rooms reached - active rooms count is ' . $result[self::PARAM_META][self::PARAM_TOTAL_COUNT] . '- available licenes not validated!');
			return false;
		}

		return true;
	}

	/**
	 * @param VCurlWrapper $curlWrapper
	 * @param $url
	 */
	public static function logError(VCurlWrapper $curlWrapper, $url)
	{
		VidiunLog::info('Sending HTTP request failed ['. $curlWrapper->getErrorNumber() . '] httpCode ['.$curlWrapper->getHttpCode()."] url [$url]: ".$curlWrapper->getError());
	}

	/**
	 * @param $result
	 * @return mixed
	 */
	public static function extractObjectFromdResult($result)
	{
		$resObj = json_decode($result, true);
		if (!empty($resObj['objects']) && isset($resObj['objects'][0]))
		{
			VidiunLog::info('Retrieved Object ' . print_r($resObj['objects'][0],true));
			return $resObj['objects'][0];
		}
		return null;
	}

	/**
	 * @param $result
	 * @param $url
	 * @param $headerSize
	 * @return null
	 */
	public static function extractIdFromCreatedResult($result,$url ,$headerSize)
	{
		$header = substr($result, 0, $headerSize);
		$headerData = explode('\n', $header);
		VidiunLog::info('Checking Headers ' . print_r($headerData, true));
		$locationPattern = "(?<=Location: $url)(.*)(?=/)";
		$locationPattern = str_replace('/', '\/', $locationPattern);
		foreach ($headerData as $part)
		{
			preg_match("/$locationPattern/", $part, $matches);
			if (!empty($matches))
			{
				$virtualRoomId = $matches[0];
				VidiunLog::info("Pexip created ID: $virtualRoomId");
				return $virtualRoomId;
			}
		}
		VidiunLog::info('Could not extract ID from headers');
		return null;
	}
}