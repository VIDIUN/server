<?php
/**
 * NOTE: this code runs before the API dispatcher - should not use Propel / autoloader
 *  
 * @package server-infra
 * @subpackage request
 */
require_once(dirname(__FILE__) . '/infraRequestUtils.class.php');
require_once(dirname(__FILE__) . '/../cache/vCacheManager.php');
require_once(dirname(__FILE__) . '/../../../../../infra/general/VCryptoWrapper.class.php');
require_once(dirname(__FILE__) . '/../../../../config/vConfMapNames.php');

/** 
 * @package server-infra
 * @subpackage request
 */
class vSessionBase
{
	const SESSION_TYPE_NONE		= -1;
	const SESSION_TYPE_USER		= 0;
	const SESSION_TYPE_WIDGET	= 1;
	const SESSION_TYPE_ADMIN	= 2;
	
	// Common constants
	const TYPE_VS =  0; // change to be 1
	const TYPE_VAS = 1; // change to be 2

	const PRIVILEGE_EDIT = "edit";
	const PRIVILEGE_VIEW = "sview";
	const PRIVILEGE_LIST = "list"; // used to bypass the user filter in entry and cue point list
	const PRIVILEGE_DOWNLOAD = "download";
	const PRIVILEGE_DOWNLOAD_ASSET = 'downloadasset';
	const PRIVILEGE_EDIT_ENTRY_OF_PLAYLIST = "editplaylist";
	const PRIVILEGE_VIEW_ENTRY_OF_PLAYLIST = "sviewplaylist";
	const PRIVILEGE_ACTIONS_LIMIT = "actionslimit";
	const PRIVILEGE_SET_ROLE = "setrole";
	const PRIVILEGE_LIMIT_ENTRY = "limitentry";
	const PRIVILEGE_IP_RESTRICTION = "iprestrict";
	const PRIVILEGE_URI_RESTRICTION = "urirestrict";
	const PRIVILEGE_ENABLE_ENTITLEMENT = "enableentitlement";
	const PRIVILEGE_DISABLE_ENTITLEMENT = "disableentitlement";
	const PRIVILEGE_DISABLE_ENTITLEMENT_FOR_ENTRY = "disableentitlementforentry";
	const PRIVILEGE_PRIVACY_CONTEXT = "privacycontext";
	const PRIVILEGE_ENABLE_CATEGORY_MODERATION = "enablecategorymoderation";
	const PRIVILEGE_REFERENCE_TIME = "reftime";
	const PRIVILEGE_SESSION_KEY = "sessionkey";
	const PRIVILEGE_PREVIEW = "preview";
	const PRIVILEGE_SESSION_ID = "sessionid";
	const PRIVILEGE_BATCH_JOB_TYPE = "jobtype";
	const PRIVILEGE_APP_TOKEN = "apptoken";
	const PRIVILEGES_DELIMITER = "/";
	const PRIVILEGE_ENABLE_PARTNER_CHANGE_ACCOUNT = "enablechangeaccount";
	const PRIVILEGE_EDIT_USER = "edituser";
	const PRIVILEGE_ENABLE_CAPTION_MODERATION = "enablecaptionmoderation";
	const PRIVILEGE_EDIT_ADMIN_TAGS = 'editadmintags';
	const PRIVILEGE_RESTRICT_EXPLICIT_LIVE_VIEW = "restrictexplicitliveview";
	const PRIVILEGE_SEARCH_CONTEXT = "searchcontext";

	const SECRETS_CACHE_PREFIX = 'partner_secrets_vsver_';
	
	const INVALID_SESSION_KEY_PREFIX = 'invalid_session_';
	const INVALID_SESSIONS_SYNCED_KEY = 'invalid_sessions_synched';

	const INVALID_STR = -1;
	const INVALID_PARTNER = -2;
	const INVALID_USER = -3;
	const INVALID_TYPE = -4;
	const EXPIRED = -5;
	const LOGOUT = -6;
	const INVALID_LVS = -7;
	const EXCEEDED_ACTIONS_LIMIT = -8;
	const EXCEEDED_RESTRICTED_IP = -9;
	const EXCEEDED_RESTRICTED_URI = -11;		// skipping -10 since it's Partner::VALIDATE_LVS_DISABLED
	const UNKNOWN = 0;
	const OK = 1;
	
	// VS V1 constants
	const SEPARATOR = ";";
	
	// VS V2 constants
	const SHA1_SIZE = 20;
	const RANDOM_SIZE = 16;
	const AES_IV = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";	// no need for an IV since we add a random string to the message anyway
	
	const FIELD_EXPIRY =              '_e';
	const FIELD_TYPE =                '_t';
	const FIELD_USER =                '_u';
	const FIELD_MASTER_PARTNER_ID =   '_m';
	const FIELD_ADDITIONAL_DATA =     '_d';

	protected static $fieldMapping = array(
		self::FIELD_EXPIRY => 'valid_until',
		self::FIELD_TYPE => 'type',
		self::FIELD_USER => 'user',
		self::FIELD_MASTER_PARTNER_ID => 'master_partner_id',
		self::FIELD_ADDITIONAL_DATA => 'additional_data',
	);
	
	// Members
	protected $hash = null;
	protected $real_str = null;
	protected $original_str = "";

	public $partner_id = null;
	public $partner_pattern = null;
	public $valid_until = null;
	public $type = null;
	public $rand = null;
	public $user = null;
	public $privileges = null;
	public $master_partner_id = null;
	public $additional_data = null;
	/**
	 * @var array
	 */
	protected $parsedPrivileges = null;

	public function getParsedPrivileges()
	{
		return $this->parsedPrivileges;
	}

	/**
	 * @param string $encoded_str
	 * @return vSessionBase
	 */
	public static function getVSObject($encoded_str)
	{
		if (empty($encoded_str))
			return null;

		$vs = new vSessionBase();		
		if (!$vs->parseVS($encoded_str))
			return null;

		return $vs;
	}
	
	/**
	 * @param string $encoded_str
	 * @return boolean - true = success, false = error, null = failed to get secret
	 */
	public function parseVS($encoded_str)
	{
		// Convert to a string in order to ensure str_replace below won't break.
		// If the input is an array for example (entered by mistake), the string conversion will yield "Array"
		// which will be parsed as a bad VS (this is the expected behavior in this case).
		$encoded_str = @(string)$encoded_str;

		$decodedVs = base64_decode(str_replace(array('-', '_'), array('+', '/'), $encoded_str), true);
		if (!$decodedVs)
		{
			$this->logError("Couldn't base 64 decode the VS.");
			return false;
		}
		
		if (substr($decodedVs, 0, 3) == 'v2|')
		{		
			$parseResult = $this->parseVsV2($decodedVs);
		}
		else
		{
			$parseResult = $this->parseVsV1($decodedVs);
		}
		
		if (!$parseResult)
			return $parseResult;
		
		$this->original_str = $encoded_str;
		
		return true;
	}
	
	public static function buildPrivileges(array $array)
	{
		$privileges = array();
		foreach($array as $privilegeName => $privilegeValue)
		{
			if(count($privilegeValue))
			{
				$privilegeValue = implode(self::PRIVILEGES_DELIMITER, $privilegeValue);
				$privileges[] = "$privilegeName:$privilegeValue";
			}
			else
			{
				$privileges[] = $privilegeName;
			}
		}
		return implode(',', $privileges);
	}
	
	public static function parsePrivileges($str)
	{
		$parsedPrivileges = array();
		$privileges = explode(',', $str);
		foreach ($privileges as $privilege)
		{
			list($privilegeName, $privilegeValue) = strpos($privilege, ":") !== false ? explode(':', $privilege, 2) : array($privilege, null);
			if (!is_null($privilegeValue) && strlen($privilegeValue))
			{
				$privilegeValue = explode(self::PRIVILEGES_DELIMITER, $privilegeValue);
			}
			if (!isset($parsedPrivileges[$privilegeName]))
			{
				$parsedPrivileges[$privilegeName] = array();
			}
			if (is_array($privilegeValue) && count($privilegeValue))
			{
				$parsedPrivileges[$privilegeName] = array_merge($parsedPrivileges[$privilegeName], $privilegeValue);
			}
		}
		
		return $parsedPrivileges;
	}
	
	public function parseVsV1($str)
	{
		$explodedStr = explode( "|" , $str , 2 );
		if (count($explodedStr) != 2)
		{
			$this->logError("Couldn't find | seperator in the VS");
			return false;
		}
			
		list($hash , $real_str) = $explodedStr;

		$parts = explode(self::SEPARATOR, $real_str);
		if (count($parts) < 3)
		{
			$this->logError("Couldn't find 3 seperated parts in the VS");
			return false;
		}
		
		$partnerId = reset($parts);
		$secrets = $this->getAdminSecrets($partnerId);
		if (!$secrets)
		{
			$this->logError("Couldn't get admin secrets for partner [$partnerId]");
			return null;
		}
		if (!$this->matchAdminSecretV1($hash, $real_str, $secrets))
		{
			$this->logError("Hash [$hash] doesn't match the sha1 on the salt on partner [$partnerId].");
			return false;
		}
		
		list(
			$this->partner_id,
			$this->partner_pattern,
			$this->valid_until,
		) = $parts;

		if(isset($parts[3]))
			$this->type = $parts[3];

		if(isset($parts[4]))
			$this->rand = $parts[4];
		
		if(isset($parts[5]))
			$this->user = $parts[5];
			
		if(isset($parts[6]))
			$this->privileges = $parts[6];

		$this->parsedPrivileges = self::parsePrivileges($this->privileges);
			
		if(isset($parts[7]))
			$this->master_partner_id = $parts[7];
		
		if(isset($parts[8]))
			$this->additional_data = $parts[8];

		$this->hash = $hash;
		$this->real_str = $real_str;
		
		return true;
	}

	public function isAdmin()
	{
		return $this->type >= self::TYPE_VAS;
	}
	
	public function isWidgetSession()
	{
		return ($this->type == self::TYPE_VS) && $this->isAnonymousSession() && (strstr($this->privileges,'widget:1') !== false);
	}
	
	public function isAnonymousSession()
	{
		return $this->user === '' || $this->user === '0' || is_null($this->user);
	}
	
	// overridable
	protected function logError($msg)
	{
	}
	
	static protected function getSecretsCacheKey($partnerId)
	{
		return self::SECRETS_CACHE_PREFIX . vConf::get('secrets_cache_version', vConfMapNames::CACHE_VERSIONS, '1') . '_' . $partnerId;
	}
	
	static public function getSecretsFromCache($partnerId)
	{
		$cacheSections = vCacheManager::getCacheSectionNames(vCacheManager::CACHE_TYPE_PARTNER_SECRETS);

		if(!$cacheSections)
			return null;
			
		$cacheKey = self::getSecretsCacheKey($partnerId);
		$lowerStores = array();
		foreach ($cacheSections as $cacheSection)
		{
			$cacheStore = vCacheManager::getCache($cacheSection);
			if (!$cacheStore)
				continue;

			$secrets = $cacheStore->get($cacheKey);
			if (!$secrets)
			{
				$lowerStores[] = $cacheStore; 
				continue;
			}
			
			foreach ($lowerStores as $cacheStore)
			{
				$cacheStore->set($cacheKey, $secrets);
			}
			
			return $secrets;
		}
		return null;
	}

	// overridable
	protected function getVSVersionAndSecret($partnerId)
	{
		$secrets = self::getSecretsFromCache($partnerId);
		if (!$secrets)
			return null;
		
		list($adminSecret, $userSecret, $vsVersion) = $secrets;
		return array($vsVersion, $adminSecret);
	}
	
	protected function getAdminSecrets($partnerId)
	{
		$versionAndSecret = $this->getVSVersionAndSecret($partnerId);
		if (!$versionAndSecret)
			return null;
		$adminSecrets = $versionAndSecret[1];
		return $adminSecrets;
	}

	protected function isVSInvalidated()
	{
		if (strpos($this->privileges, self::PRIVILEGE_ACTIONS_LIMIT) !== false)
			return null;			// cannot validate action limited VS at this level
		
		$memcache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_QUERY_CACHE_KEYS);
		if (!$memcache)
			return null;			// failed to connect to memcache or memcache not enabled

		$vsKey = self::INVALID_SESSION_KEY_PREFIX . $this->hash;
		$keysToGet = array(self::INVALID_SESSIONS_SYNCED_KEY, $vsKey);
		
		$sessionIdKey = $this->getSessionIdHash();
		if ($sessionIdKey)
		{
			$sessionIdKey = self::INVALID_SESSION_KEY_PREFIX . $sessionIdKey;
			$keysToGet[] = $sessionIdKey;
		}
		
		$cacheResult = $memcache->multiGet($keysToGet);
		if ($cacheResult === false)
			return null;			// failed to get the keys

		if (!array_key_exists(self::INVALID_SESSIONS_SYNCED_KEY, $cacheResult) ||
			!$cacheResult[self::INVALID_SESSIONS_SYNCED_KEY])
			return null;			// invalid sessions not synched to memcache
		
		unset($cacheResult[self::INVALID_SESSIONS_SYNCED_KEY]);
		if ($cacheResult)
			return true;			// the session is invalid
		
		return false;
	}
	
	public function getPrivileges()
	{
		return $this->privileges;
	}

	public function getPartnerId()
	{
		return $this->partner_id;
	}
	
	protected function isValidUriRestrict()
	{
		$requestUri = $_SERVER["REQUEST_URI"];
		$value = implode(self::PRIVILEGES_DELIMITER, $this->parsedPrivileges[self::PRIVILEGE_URI_RESTRICTION]);
		$uris = explode('|', $value);
		foreach ($uris as $uri)
		{
			if ($requestUri == $uri ||			// exact match
				(substr($uri, -1) == '*' && 	// prefix match
				substr($requestUri, 0, strlen($uri) - 1) == substr($uri, 0, -1)))
			{
				return true;
			}
		}
		return false;
	}

	public function isValidBase()
	{
		if (!$this->real_str || !$this->hash)
			return self::INVALID_STR;			// VS parsing failed
		
		if ($this->valid_until <= time())
			return self::EXPIRED;				// VS is expired
			
		if (array_key_exists(self::PRIVILEGE_IP_RESTRICTION, $this->parsedPrivileges) &&
			!in_array(infraRequestUtils::getRemoteAddress(), $this->parsedPrivileges[self::PRIVILEGE_IP_RESTRICTION]))
		{
			return self::EXCEEDED_RESTRICTED_IP;
		}
		
		if (array_key_exists(self::PRIVILEGE_URI_RESTRICTION, $this->parsedPrivileges))
		{
			if (!$this->isValidUriRestrict())
			{
				return self::EXCEEDED_RESTRICTED_URI;
			}
		}

		return self::OK;
	}
	
	public function tryToValidateVS()
	{
		$result = $this->isValidBase();
		if ($result != self::OK)
			return $result;
		
		if ($this->partner_id == -1 ||			// Batch VS are never invalidated
			$this->isWidgetSession())			// Since anyone can create a widget session, no need to check for invalidation
			return self::OK;
		
		$isInvalidated = $this->isVSInvalidated();
		if ($isInvalidated)
			return self::LOGOUT;
		else if ($isInvalidated === null)
			return self::UNKNOWN;				// failed to check
		
		return self::OK;
	}

	public static function generateSession($vsVersion, $adminSecretForSigning, $userId, $type, $partnerId, $expiry, $privileges, $masterPartnerId = null, $additionalData = null)
	{
		if ($vsVersion == 2)
			return self::generateVsV2($adminSecretForSigning, $userId, $type, $partnerId, $expiry, $privileges, $masterPartnerId, $additionalData);

		return self::generateVsV1($adminSecretForSigning, $userId, $type, $partnerId, $expiry, $privileges, $masterPartnerId, $additionalData);
	}
	
	public static function generateVsV1($adminSecret, $userId, $type, $partnerId, $expiry, $privileges, $masterPartnerId, $additionalData)
	{
		$rand = microtime(true);
		$expiry = time() + $expiry;
		$fields = array(
			$partnerId,
			$partnerId,
			$expiry,
			$type,
			$rand,
			$userId,
			$privileges,
			$masterPartnerId,
			$additionalData,
		);
		$info = implode ( ";" , $fields );

		$signature = sha1( $adminSecret . $info );
		$strToHash =  $signature . "|" . $info ;
		$encoded_str = base64_encode( $strToHash );

		return $encoded_str;
	}
	// VS V2 functions
	protected static function aesEncrypt($key, $message)
	{
		
		$key = substr(sha1($key, true), 0, 16);
		return VCryptoWrapper::encrypt_aes($message, $key, self::AES_IV);
	}

	protected static function aesDecrypt($key, $message)
	{
		$key = substr(sha1($key, true), 0, 16);
		return VCryptoWrapper::decrypt_aes($message, $key, self::AES_IV);
	}


	public static function generateVsV2($adminSecret, $userId, $type, $partnerId, $expiry, $privileges, $masterPartnerId, $additionalData)
	{
		// build fields array
		$fields = array();
		foreach (explode(',', $privileges) as $privilege)
		{
			$privilege = trim($privilege);
			if (!$privilege)
				continue;
			if ($privilege == '*')
				$privilege = 'all:*';
			$splittedPrivilege = explode(':', $privilege, 2);
			if (count($splittedPrivilege) > 1)
				$fields[$splittedPrivilege[0]] = $splittedPrivilege[1];
			else
				$fields[$splittedPrivilege[0]] = '';
		}
		$fields[self::FIELD_EXPIRY] = time() + $expiry;
		$fields[self::FIELD_TYPE] = $type;
		$fields[self::FIELD_USER] = $userId;
		$fields[self::FIELD_MASTER_PARTNER_ID] = $masterPartnerId;
		$fields[self::FIELD_ADDITIONAL_DATA] = $additionalData;

		// build fields string
		$fieldsStr = http_build_query($fields, '', '&');
		$rand = '';
		for ($i = 0; $i < self::RANDOM_SIZE; $i++)
			$rand .= chr(rand(0, 0xff));
		$fieldsStr = $rand . $fieldsStr;
		$fieldsStr = sha1($fieldsStr, true) . $fieldsStr;
		
		// encrypt and encode
		$encryptedFields = self::aesEncrypt($adminSecret, $fieldsStr);
		$decodedVs = "v2|{$partnerId}|" . $encryptedFields;
		return str_replace(array('+', '/'), array('-', '_'), base64_encode($decodedVs));
	}
	
	public function parseVsV2($decodedVs)
	{
		$explodedVs = explode('|', $decodedVs , 3);
		if (count($explodedVs) != 3)
		{
			$this->logError("Not enough | delimiters in the VS");
			return false;						// not VS V2
		}
		
		list($version, $partnerId, $encVs) = $explodedVs;
		
		$adminSecrets = $this->getAdminSecrets($partnerId);
		if (!$adminSecrets)
		{
			$this->logError("Couldn't get secrets for partner [$partnerId].");
			return null;						// admin secret not found, can't decrypt the VS
		}
		$arrayMatch = $this->matchAdminSecretV2($encVs, $adminSecrets);
		if(!$arrayMatch)
		{
			$this->logError("Hash doesn't match sha1 on partner [$partnerId].");
			return false;						// invalid signature
		}
		list($hash, $fields) = $arrayMatch;
		$rand = substr($fields, 0, self::RANDOM_SIZE);
		$fields = substr($fields, self::RANDOM_SIZE);
		
		$fieldsArr = null;
		parse_str($fields, $fieldsArr);
		
		// TODO: the following code translates a VS v2 into members that are more suitable for V1
		//	in the future it makes sense to change the structure of the vs class
		$privileges = array();
		$this->parsedPrivileges = array();
		foreach ($fieldsArr as $fieldName => $fieldValue)
		{
			if (isset(self::$fieldMapping[$fieldName]))
			{
				$fieldMember = self::$fieldMapping[$fieldName];
				$this->$fieldMember = $fieldValue;
				continue;
			}
			if (strlen($fieldValue))
			{
				$privileges[] = "{$fieldName}:{$fieldValue}";
				$this->parsedPrivileges[$fieldName] = explode(self::PRIVILEGES_DELIMITER, $fieldValue);
			}
			else 
			{
				$privileges[] = "{$fieldName}";
				$this->parsedPrivileges[$fieldName] = array();
			}
		}
		
		$this->hash = bin2hex($hash);
		$this->real_str = $fields;
		$this->partner_id = $partnerId;
		$this->rand = bin2hex($rand);
		$this->privileges = implode(',', $privileges);
		if ($this->privileges == 'all:*')
			$this->privileges = '*';

		return true;
	}

	public static function buildSessionIdHash($partnerId, $sessionId)
	{
		return sha1($partnerId . '_' . $sessionId);
	}

	public function getSessionIdHash()
	{
		if(isset($this->parsedPrivileges[self::PRIVILEGE_SESSION_ID][0])) {
			return self::buildSessionIdHash($this->partner_id, $this->parsedPrivileges[self::PRIVILEGE_SESSION_ID][0]);
		}
		return null;
	}
	
	public function getHash()
	{
		return $this->hash;
	}


	public static function getServerPrivileges()
	{
		$serverPrivileges = array();
		$refl = new ReflectionClass('vSessionBase');
		$refConstants = $refl->getConstants();

		foreach($refConstants as $constName => $constValue)
		{
			if(substr($constName, 0, 10) === "PRIVILEGE_")
				$serverPrivileges[] = $constValue;
		}

		return $serverPrivileges;
	}

	/**
	 * @param $encVs
	 * @param $adminSecrets
	 * @return array|bool
	 */
	private function matchAdminSecretV2($encVs, $adminSecrets)
	{
		$adminSecretsArray = explode(',', $adminSecrets);
		foreach ($adminSecretsArray as $adminSecret)
		{
			$decVs = self::aesDecrypt($adminSecret, $encVs);
			$decVs = rtrim($decVs, "\0");

			$hash = substr($decVs, 0, self::SHA1_SIZE);
			$fields = substr($decVs, self::SHA1_SIZE);
			if ($hash === sha1($fields, true))
				return array($hash, $fields);
		}
		return false;
	}

	/**
	 * @param $hash
	 * @param $real_str
	 * @param $adminSecrets
	 * @return bool
	 */
	private function matchAdminSecretV1($hash, $real_str, $adminSecrets)
	{
		$adminSecretsArray = explode(',', $adminSecrets);
		foreach ($adminSecretsArray as $adminSecret)
		{
			if (sha1($adminSecret . $real_str) === $hash)
				return true;
		}
		return false;
	}


}
