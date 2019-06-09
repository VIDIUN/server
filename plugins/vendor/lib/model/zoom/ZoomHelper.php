<?php
/**
 * @package plugins.venodr
 * @subpackage model.zoom
 */
class ZoomHelper
{
	/** API */
	const API_USERS_ME = '/v2/users/me';
	const API_PARTICIPANT = '/v2/report/meetings/@meetingId@/participants';
	const API_USERS_ME_PERMISSIONS = '/v2/users/me/permissions';

	/** payload data */
	const ACCOUNT_ID = "account_id";
	const PAYLOAD = 'payload';
	const DOWNLOAD_TOKEN = 'download_token';
	const MEETING = 'meeting';
	const HOST_EMAIL = 'host_email';
	const RECORDING_FILES = 'recording_files';
	const MP4 = 'MP4';
	const FILE_TYPE = 'file_type';
	const DOWNLOAD_URL = 'download_url';
	const MEETING_ID = 'id';
	const USER_EMAIL = 'user_email';
	const PARTICIPANTS = 'participants';
	const UUID = 'uuid';
	const TOPIC = 'topic';
	/** php body */
	const PHP_INPUT = 'php://input';

	const ADMIN_TAG_ZOOM = 'zoomentry';

	/**
	 * @param vuser $dbUser
	 * @param string $zoomCategory
	 * @param $emails
	 * @param $meetingId
	 * @param $topic
	 * @return string
	 * @throws Exception
	 */
	public static function createEntryForZoom($dbUser, $zoomCategory, $emails, $meetingId, $topic)
	{
		$entry = new entry();
		$entry->setType(entryType::MEDIA_CLIP);
		$entry->setSourceType(EntrySourceType::URL);
		$entry->setMediaType(entry::ENTRY_MEDIA_TYPE_VIDEO);
		$entry->setDescription('Zoom meeting id:' . $meetingId);
		$entry->setName($topic);
		$entry->setPartnerId($dbUser->getPartnerId());
		$entry->setStatus(entryStatus::NO_CONTENT);
		$entry->setPuserId($dbUser->getPuserId());
		$entry->setVuserId($dbUser->getVuserId());
		$entry->setConversionProfileId(myPartnerUtils::getConversionProfile2ForPartner($dbUser->getPartnerId())->getId());
		$entry->setAdminTags(self::ADMIN_TAG_ZOOM);
		if ($zoomCategory)
		{
			$entry->setCategories($zoomCategory);
		}
		if ($emails)
		{
			foreach ($emails as $email)
			{
				vuserPeer::createUniqueVuserForPartner($dbUser->getPartnerId(), $email);
				//User John.Doe@k.com will be added both as John.Doe@k.com and John.Doe
				if(vString::isEmailString($email))
				{
					list($puserId,) = explode('@', $email);
					vuserPeer::createUniqueVuserForPartner($dbUser->getPartnerId(), $puserId);
					$emails [] = $puserId;
				}
			}
			$entry->setEntitledPusersPublish(implode(",", array_unique($emails)));
		}
		//In case that user is saved in Zoom with full email addresss,we keep also user ID on the entry as co-editor.
		if(vString::isEmailString($dbUser->getPuserId()))
		{
			list($puserId,) = explode('@', $dbUser->getPuserId());
			vuserPeer::createUniqueVuserForPartner($dbUser->getPartnerId(), $puserId);
			$entry->setEntitledPusersEdit($puserId);
		}
		$entry->save();
		return $entry->getId();
	}

	/**
	 * @param array $zoomUserPermissions
	 * @return bool
	 */
	public static function canConfigureEventSubscription($zoomUserPermissions)
	{
		if (in_array('Recording:Read', $zoomUserPermissions) && in_array('Recording:Edit', $zoomUserPermissions))
		{
			return true;
		}
		return false;
	}
	/**
	 * redirects to new URL
	 * @param $url
	 */
	public static function redirect($url)
	{
		$redirect  = new vRendererRedirect($url);
		$redirect->output();
		VExternalErrors::dieGracefully();
	}

	/**
	 * @param array $tokens
	 * @throws Exception
	 */
	public static function loadLoginPage($tokens)
	{
		$file_path = dirname(__FILE__) . "/../../api/webPage/zoom/vidiunZoomLoginPage.html";
		if (file_exists($file_path))
		{
			$page = file_get_contents($file_path);
			$tokensString = json_encode($tokens);
			$zoomConfiguration = vConf::get('ZoomAccount', 'vendor');
			$verificationToken = $zoomConfiguration['verificationToken'];
			list($enc, $iv) = AESEncrypt::encrypt($verificationToken, $tokensString);
			$page = str_replace('@BaseServiceUrl@', requestUtils::getHost(), $page);
			$page = str_replace('@encryptData@', base64_encode($enc), $page);
			$page = str_replace('@iv@', base64_encode($iv), $page);
			echo $page;
			die();
		}
	}

	/**
	 * @param ZoomVendorIntegration $zoomIntegration
	 * @param $accountId
	 * @param $vs
	 * @throws Exception
	 */
	public static function loadSubmitPage($zoomIntegration, $accountId, $vs)
	{
		$file_path = dirname(__FILE__) . "/../../api/webPage/zoom/VidiunZoomRegistrationPage.html";
		if (file_exists($file_path))
		{
			$page = file_get_contents($file_path);
			/** @noinspection PhpUndefinedMethodInspection */
			$page = str_replace('@vs@', $vs->getOriginalString(), $page);
			$page = str_replace('@BaseServiceUrl@', requestUtils::getHost(), $page);
			if (!is_null($zoomIntegration))
			{
				$page = str_replace('@defaultUserID@', $zoomIntegration->getDefaultUserEMail() , $page);
				$page = str_replace('@zoomCategory@', $zoomIntegration->getZoomCategory() ? $zoomIntegration->getZoomCategory()  : 'Zoom Recordings'  , $page);
				$page = str_replace('@enableRecordingUpload@', $zoomIntegration->getStatus()== VendorStatus::ACTIVE ? 'checked'  : ''  , $page);
				$page = str_replace('@createUserIfNotExist@', $zoomIntegration->getCreateUserIfNotExist() ? 'checked'  : ''  , $page);
			}
			else
			{
				$page = str_replace('@defaultUserID@', '' , $page);
				$page = str_replace('@zoomCategory@', 'Zoom Recordings', $page);
				$page = str_replace('@enableRecordingUpload@', 'checked', $page);
				$page = str_replace('@createUserIfNotExist@', 'checked', $page);
			}
			$page = str_replace('@accountId@', $accountId , $page);
			echo $page;
			die();
		}
		throw new VidiunAPIException('unable to find submit page, please contact support');
	}

	/**
	 * @param $tokensDataAsArray
	 * @param $accountId
	 * @param ZoomVendorIntegration $zoomClientData
	 * @throws PropelException
	 */
	public static function saveNewTokenData($tokensDataAsArray, $accountId, $zoomClientData = null)
	{
		if (!$zoomClientData) // create new vendorIntegration during oauth first time
		{
			$zoomClientData = new ZoomVendorIntegration();
			$zoomClientData->setStatus(VendorStatus::DISABLED);
		}
		$zoomClientData->saveNewTokenData($tokensDataAsArray, $accountId);
	}


	/**
	 * verify headers tokens, if not equal die
	 * @throws Exception
	 */
	public static function verifyHeaderToken()
	{
		$headers = self::getAllHeaders();
		$zoomConfiguration = vConf::get('ZoomAccount', 'vendor');
		$verificationToken = $zoomConfiguration['verificationToken'];
		if ($verificationToken !== $headers['Authorization'])
		{
			VExternalErrors::dieGracefully('ZOOM - Received verification token is different from existing token');
		}
	}

	/**
	 * @param array $downloadURLs
	 * @param string $downloadToken
	 * @return array
	 */
	public static function parseDownloadUrls($downloadURLs, $downloadToken)
	{
		$urls = array();
		foreach ($downloadURLs as $downloadURL)
		{
			$urls[] = $downloadURL . '?access_token=' . $downloadToken;
		}
		return $urls;
	}

	/**
	 * @param $data
	 * @return array
	 */
	public static function extractDataFromRecordingCompletePayload($data)
	{
		VidiunLog::debug('recordingcomplete data recived from zoom:');
		VidiunLog::debug(print_r($data, true));
		$payload = $data[self::PAYLOAD];
		$downloadToken = $data[self::DOWNLOAD_TOKEN];
		$accountId = $payload[self::ACCOUNT_ID];
		$meeting = $payload[self::MEETING];
		$hostEmail = $meeting[self::HOST_EMAIL];
		$recordingFiles = $meeting[self::RECORDING_FILES];
		$downloadURLs = self::getDownloadUrls($recordingFiles);
		$meetingId = $meeting[self::MEETING_ID];
		$topic = $meeting[self::TOPIC];
		return array($accountId, $downloadToken, $hostEmail, $downloadURLs, $meetingId, $topic);
	}

	/**
	 * @param $data
	 * @return string
	 */
	public static function extractAccountIdFromDeAuthPayload($data)
	{
		$payload = $data[self::PAYLOAD];
		$accountId = $payload[self::ACCOUNT_ID];
		return $accountId;
	}

	/**
	 * @param $meetingId
	 * @param ZoomVendorIntegration $zoomIntegration
	 * @param $accountId
	 * @return array
	 * @throws Exception
	 */
	public static function extractCoHosts($meetingId, $zoomIntegration, $accountId)
	{
		$emails = array();
		$meetingApi = str_replace('@meetingId@', $meetingId, self::API_PARTICIPANT);
		list($tokens, $participants) = ZoomWrapper::retrieveZoomDataAsArray($meetingApi, false, $zoomIntegration->getTokens(), $accountId);
		if (isset($tokens[vZoomOauth::ACCESS_TOKEN]) &&
			$tokens[vZoomOauth::ACCESS_TOKEN] &&
			$zoomIntegration->getAccessToken() !== $tokens[vZoomOauth::ACCESS_TOKEN])
		{
			// token changed -> refresh tokens
			self::saveNewTokenData($tokens, $accountId, $zoomIntegration);
		}
		if ($participants)
		{
			$participants = $participants[self::PARTICIPANTS];
			foreach ($participants as $participant)
			{
				if (isset($participant[self::USER_EMAIL]) && $participant[self::USER_EMAIL])
				{
					$emails[] = $participant[self::USER_EMAIL];
				}
			}
		}

		return $emails;
	}

	/**
	 * @param $emails
	 * @param $partnerId
	 * @param $createIfNoFound
	 * @return array
	 */
	public static function getValidatedUsers($emails, $partnerId, $createIfNotFound)
	{
		$validatedEmails=array();
		foreach ($emails as $usersEmail)
		{
			if(vuserPeer::getVuserByPartnerAndUid($partnerId, $usersEmail, true))
			{
				$validatedEmails[] = $usersEmail;
			}
			elseif($createIfNotFound)
			{
				vuserPeer::createVuserForPartner($partnerId, $usersEmail);
				$validatedEmails[] = $usersEmail;
			}
		}
		return $validatedEmails;
	}

	/**
	 * @param $hostEmail
	 * @param $defaultHostEmail
	 * @param $partnerId
	 * @param $createIfNotFound
	 * @return vuser
	 */
	public static function getEntryOwner($hostEmail, $defaultHostEmail, $partnerId, $createIfNotFound)
	{
		$dbUser = vuserPeer::getVuserByPartnerAndUid($partnerId, $hostEmail, true);
		if (!$dbUser)
		{
			if($createIfNotFound)
			{
				$dbUser = vuserPeer::createVuserForPartner($partnerId, $hostEmail);
			}
			else//get the default user that will be the owner if the entry.
			{
				$dbUser = vuserPeer::getVuserByPartnerAndUid($partnerId, $defaultHostEmail, true);
			}
		}
		return $dbUser;
	}



	/**
	 * @return array
	 */
	protected static function getAllHeaders()
	{
		if (!function_exists('getallheaders'))
		{
			$headers = array();
			foreach ($_SERVER as $name => $value)
			{
				/* RFC2616 (HTTP/1.1) defines header fields as case-insensitive entities. */
				if (strtolower(substr($name, 0, 5)) == 'http_')
				{
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
			return $headers;
		}
		else
		{
			return getallheaders();
		}
	}

	/**
	 * @param array $recordingFiles
	 * @return array
	 */
	protected static function getDownloadUrls($recordingFiles)
	{
		$downloadURLs = array();
		foreach ($recordingFiles as $recordingFile) {
			if ($recordingFile[self::FILE_TYPE] === self::MP4)
			{
				$downloadURLs[] = $recordingFile[self::DOWNLOAD_URL];
			}
		}
		if (!$downloadURLs)
		{
			VExternalErrors::dieGracefully('Zoom - MP4 downland url was not found');
		}
		return $downloadURLs;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function getPayloadData()
	{
		$request_body = file_get_contents(self::PHP_INPUT);
		$data = json_decode($request_body, true);
		return $data;
	}

	/**
	 * @param array $urls
	 * @param vuser $dbUser
	 * @param ZoomVendorIntegration $zoomIntegration
	 * @param $emails
	 * @param $meetingId
	 * @param $hostEmail
	 * @param $topic
	 * @throws Exception
	 */
	public static function uploadToVidiun($urls, $dbUser, $zoomIntegration, $emails, $meetingId, $hostEmail, $topic)
	{
		foreach ($urls as $url)
		{
			$entryId = ZoomHelper::createEntryForZoom($dbUser, $zoomIntegration->getZoomCategory(), $emails, $meetingId, $topic);
			vJobsManager::addImportJob(null, $entryId, $dbUser->getPartnerId(), $url);
			VidiunLog::debug('Zoom - upload entry to vidiun started, partner id: ' .
				$zoomIntegration->getPartnerId() . ' host email: ' . $hostEmail .
				' emails: ' . print_r($emails, true) . ' category: ' .  $zoomIntegration->getZoomCategory() .
				' meeting Id: ' . $meetingId . ' entry Id: ' . $entryId);
			if ($zoomIntegration->getZoomCategoryId())
			{
				self::createCategoryEntry($entryId, $zoomIntegration->getZoomCategoryId(), $dbUser->getPartnerId());
			}
		}
	}

	/**
	 * @param int $partnerId
	 * @param string $categoryFullName
	 * @param bool $createIfNotExist
	 * @throws Exception
	 * @return int id;
	 */
	public static function createCategoryForZoom($partnerId, $categoryFullName, $createIfNotExist = true)
	{
		$category = categoryPeer::getByFullNameExactMatch($categoryFullName, null, $partnerId);
		if($category)
		{
			VidiunLog::debug('Category: ' . $categoryFullName . ' already exist for partner: ' . $partnerId);
			return $category->getId();
		}
		if(!$createIfNotExist)
		{
			return null;
		}

		$categoryDb = new category();

		//Check if this is a root category or child , if child get its parent ID
		$categoryNameArray = explode(categoryPeer::CATEGORY_SEPARATOR, $categoryFullName);
		$categoryName = end($categoryNameArray);
		if(count($categoryNameArray) > 1)
		{
			$parentCategoryFullNameArray = array_slice ($categoryNameArray,0,-1);
			$parentCategoryFullName = implode(categoryPeer::CATEGORY_SEPARATOR, $parentCategoryFullNameArray );
			$parentCategory = categoryPeer::getByFullNameExactMatch($parentCategoryFullName, null, $partnerId);
			if(!$parentCategory)
			{
				throw new VidiunAPIException(VidiunErrors::PARENT_CATEGORY_NOT_FOUND, $parentCategoryFullName);
			}
			$parentCategoryId = $parentCategory->getId();
			$categoryDb->setParentId($parentCategoryId);
		}

		$categoryDb->setName($categoryName);
		$categoryDb->setFullName($categoryFullName);
		$categoryDb->setPartnerId($partnerId);
		$categoryDb->save();
		return $categoryDb->getId();
	}

	/**
	 * @param $entryId
	 * @param $categoryId
	 * @param $getPartnerId
	 * @throws PropelException
	 */
	protected static function createCategoryEntry($entryId, $categoryId, $getPartnerId)
	{
		$categoryEntry = new categoryEntry();
		$categoryEntry->setEntryId($entryId);
		$categoryEntry->setCategoryId($categoryId);
		$categoryEntry->setPartnerId($getPartnerId);
		$categoryEntry->setStatus(CategoryEntryStatus::ACTIVE);
		$categoryEntry->save();
	}
}