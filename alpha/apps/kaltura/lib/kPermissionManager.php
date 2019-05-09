<?php

class vPermissionManager implements vObjectCreatedEventConsumer, vObjectChangedEventConsumer, vObjectInvalidateCacheEventConsumer
{
	// -------------------
	// -- Class members --
	// -------------------
		
	const GLOBAL_CACHE_KEY_PREFIX = 'vPermissionManager_'; // Prefix added for all key names stored in the cache
	
	private static $map = array(); // Local map of permission items allowed for the current role
	
	const API_ACTIONS_ARRAY_NAME    = 'api_actions';      // name of $map's api actions array
	const API_PARAMETERS_ARRAY_NAME = 'api_parameters';   // name of $map's api parameters array
	const PARTNER_GROUP_ARRAY_NAME  = 'partner_group';    // name of $map's partner group array
	const PERMISSION_NAMES_ARRAY    = 'permission_names'; // name of $map's permission names array
	const DEFAULT_ID = 'default';
			
	private static $lastInitializedContext = null; // last initialized security context (vs + partner id)
	private static $cacheWatcher = null;
	private static $useCache = true;     // use cache or not
	
	private static $vsUserId = null;
	private static $adminSession = false; // is admin session
	private static $vsPartnerId = null;
	private static $requestedPartnerId = null;
	private static $vsString = null;
	private static $roleIds = null;
	private static $operatingPartnerId = null;
	
	private static $cacheStores = array();
	
	/**
	 * @var Partner
	 */
	private static $operatingPartner = null;
	
	/**
	 * @var vuser
	 */
	private static $vuser = null;
		
	
	// ----------------------------
	// -- Cache handling methods --
	// ----------------------------
	
	
	private static function useCache()
	{
		if (self::$useCache)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * @param int $roleId
	 * @return cache key name for the given role id
	 */
	private static function getRoleIdKey($roleId, $partnerId)
	{
		if (is_null($roleId)) {
			$roleId = 'null';
		}
		if (is_null($partnerId)) {
			$partnerId = 'null';
		}
		$key = 'role_'.$roleId.'_partner_'.$partnerId.'_internal_'.intval(vIpAddressUtils::isInternalIp());
		return $key;
	}
	
	private static function getCacheKeyPrefix()
	{
		return self::GLOBAL_CACHE_KEY_PREFIX . kConf::get('permission_cache_version', kConfMapNames::CACHE_VERSIONS, '');
	}
	
	/**
	 * Get value from cache for the given key
	 * @param string $key
	 */
	private static function getFromCache($key, $roleCacheDirtyAt)
	{
		if (!self::useCache())
		{
			return null;
		}
		
		self::$cacheStores = array();
		
		$cacheLayers = vCacheManager::getCacheSectionNames(vCacheManager::CACHE_TYPE_PERMISSION_MANAGER);
		
		foreach ($cacheLayers as $cacheLayer)
		{
			$cacheStore = vCacheManager::getCache($cacheLayer);
			if (!$cacheStore)
				continue;
				
			$cacheRole = $cacheStore->get(self::getCacheKeyPrefix() . $key); // try to fetch from cache
			if ( !$cacheRole || !isset($cacheRole['updatedAt']) || ( $cacheRole['updatedAt'] < $roleCacheDirtyAt ) )
			{
				self::$cacheStores[] = $cacheStore;
				continue;
			}

			$map = $cacheStore->get(self::getCacheKeyPrefix() . $cacheRole['mapHash']); // try to fetch from cache
			if ( !$map )
			{
				self::$cacheStores[] = $cacheStore;
				continue;
			}
				
			VidiunLog::debug("Found a cache value for key [$key] map hash [".$cacheRole['mapHash']."] in layer [$cacheLayer]");
			self::storeInCache($key, $cacheRole, $map);		// store in lower cache layers
			self::$cacheStores[] = $cacheStore;

			return $map;
		}

		VidiunLog::debug("No cache value found for key [$key]");
		return null;
	}
	
	/**
	 *
	 * Store given value in cache for with the given key as an identifier
	 * @param string $key
	 * @param string $value
	 */
	private static function storeInCache($key, $cacheRole, $map)
	{
		if (!self::useCache())
		{
			return;
		}
		
		foreach (self::$cacheStores as $cacheStore)
		{
			if (!$cacheStore->set(
				self::getCacheKeyPrefix() . $key,
				$cacheRole,
				vConf::get('apc_cache_ttl')))
				continue;

			$success = $cacheStore->set(
				self::getCacheKeyPrefix() . $cacheRole['mapHash'],
				$map,
				vConf::get('apc_cache_ttl')); // try to store in cache
					
			if ($success)
			{
				VidiunLog::debug("New value stored in cache for key [$key] map hash [".$cacheRole['mapHash']."]");
			}
			else
			{
				VidiunLog::debug("No cache value stored for key [$key] map hash [".$cacheRole['mapHash']."]");
			}
		}
	}
	
	
	
	// ----------------------------
	// -- Initialization methods --
	// ----------------------------
	
	
	/**
	 * Throws an error if init function hasn't been executed yet
	 * @throws Exception
	 */
	private static function errorIfNotInitialized()
	{
		if (is_null(self::$lastInitializedContext))
		{
			throw new Exception('Permission manager has not yet been initialized');
		}
	}
	
	
	/**
	 * Init an empty cache map array for holding "organized" permission items
	 */
	private static function initEmptyMap()
	{
		$map = array();
		$map[self::API_ACTIONS_ARRAY_NAME]    = array();
		$map[self::API_PARAMETERS_ARRAY_NAME] = array();
		$map[self::PARTNER_GROUP_ARRAY_NAME]  = array();
		$map[self::API_PARAMETERS_ARRAY_NAME][ApiParameterPermissionItemAction::READ]   = array();
		$map[self::API_PARAMETERS_ARRAY_NAME][ApiParameterPermissionItemAction::UPDATE] = array();
		$map[self::API_PARAMETERS_ARRAY_NAME][ApiParameterPermissionItemAction::INSERT] = array();
		$map[self::PERMISSION_NAMES_ARRAY] = array();
		return $map;
	}
	
	
	private static function getPermissions($roleId)
	{
		$map = self::initEmptyMap();
		
		// get cache dirty time
		$roleCacheDirtyAt = 0;
		if (self::$operatingPartner) {
			$roleCacheDirtyAt = self::$operatingPartner->getRoleCacheDirtyAt();
		}
		
		// get role from cache
		$roleCacheKey = self::getRoleIdKey($roleId, self::$operatingPartnerId);
		$cacheRole = self::getFromCache($roleCacheKey, $roleCacheDirtyAt);
		
		// compare updatedAt between partner dirty flag and cache
		if ( $cacheRole )
		{
			return $cacheRole; // initialization from cache finished
		}
		
		// cache is not updated - delete stored value and re-init from DB
		
		$dbRole = null;
		if (!is_null($roleId))
		{
			UserRolePeer::setUseCriteriaFilter(false);
			$dbRole = UserRolePeer::retrieveByPK($roleId);
			UserRolePeer::setUseCriteriaFilter(true);
			
			if (!$dbRole)
			{
				VidiunLog::alert('User role ID ['.$roleId.'] set for user ID ['.self::$vsUserId.'] of partner ['.self::$operatingPartnerId.'] was not found in the DB');
				throw new vPermissionException('User role ID ['.$roleId.'] set for user ID ['.self::$vsUserId.'] of partner ['.self::$operatingPartnerId.'] was not found in the DB', vPermissionException::ROLE_NOT_FOUND);
			}
		}
		
		$map = self::getPermissionsFromDb($dbRole);
		
		// update cache
		$cacheRole = array(
			'updatedAt' => time(),
			'mapHash' => md5(serialize($map)));
		self::storeInCache($roleCacheKey, $cacheRole, $map);
		
		return $map;
	}
		
		
	/**
	 * Init permission items map from DB for the given role
	 * @param UserRole $dbRole
	 */
	private static function getPermissionsFromDb($dbRole)
	{
		$map = self::initEmptyMap();
		
		// get all permission object names from role record
		if ($dbRole)
		{
			$tmpPermissionNames = $dbRole->getPermissionNames(true);
			$tmpPermissionNames = array_map('trim', explode(',', $tmpPermissionNames));
		}
		else {
			$tmpPermissionNames = array();
		}
		
		// add always allowed permissions
		if (self::$operatingPartner) {
			$alwaysAllowed = self::$operatingPartner->getAlwaysAllowedPermissionNames();
			$alwaysAllowed = array_map('trim', explode(',', $alwaysAllowed));
		}
		else {
			$alwaysAllowed = array(PermissionName::ALWAYS_ALLOWED_ACTIONS);
		}
		$tmpPermissionNames = array_merge($tmpPermissionNames, $alwaysAllowed);
		
		// if the request sent from the internal server set additional permission allowing access without VS
		// from internal servers
		if (vIpAddressUtils::isInternalIp())
		{
			VidiunLog::debug('IP in range, adding ALWAYS_ALLOWED_FROM_INTERNAL_IP_ACTIONS permission');
			$alwaysAllowedInternal = array(PermissionName::ALWAYS_ALLOWED_FROM_INTERNAL_IP_ACTIONS);
			$tmpPermissionNames = array_merge($tmpPermissionNames, $alwaysAllowedInternal);
		}
		
		$permissionNames = array();
		foreach ($tmpPermissionNames as $name)
		{
			$permissionNames[$name] = $name;
		}
		$map[self::PERMISSION_NAMES_ARRAY] = $permissionNames;
		
		// get mapping of permissions to permission items
		$c = new Criteria();
		$c->addAnd(PermissionPeer::NAME, $permissionNames, Criteria::IN);
		$c->addAnd(PermissionPeer::PARTNER_ID, array(strval(PartnerPeer::GLOBAL_PARTNER), strval(self::$operatingPartnerId)), Criteria::IN);
		$c->addAnd(PermissionItemPeer::PARTNER_ID, array(strval(PartnerPeer::GLOBAL_PARTNER), strval(self::$operatingPartnerId)), Criteria::IN);
		$lookups = PermissionToPermissionItemPeer::doSelectJoinAll($c);
		foreach ($lookups as $lookup)
		{
			$item       = $lookup->getPermissionItem();
			$permission = $lookup->getPermission();
			
			if (!$item)	{
				VidiunLog::err('PermissionToPermissionItem id ['.$lookup->getId().'] is defined with PermissionItem id ['.$lookup->getPermissionItemId().'] which does not exists!');
				continue;
			}
			
			if (!$permission) {
				VidiunLog::err('PermissionToPermissionItem id ['.$lookup->getId().'] is defined with Permission name ['.$lookup->getPermissionName().'] which does not exists!');
				continue;
			}
				
			// organize permission items in local arrays
			$type = $item->getType();
			if ($type == PermissionItemType::API_ACTION_ITEM)
			{
				self::addApiAction($map, $item);
			}
			else if ($type == PermissionItemType::API_PARAMETER_ITEM)
			{
				self::addApiParameter($map, $item);
			}
		}
		
		// set partner group permission
		$c = new Criteria();
		$c->addAnd(PermissionPeer::PARTNER_ID, self::$operatingPartnerId, Criteria::EQUAL);
		$c->addAnd(PermissionPeer::TYPE, PermissionType::PARTNER_GROUP, Criteria::EQUAL);
		$partnerGroupPermissions = PermissionPeer::doSelect($c);
		foreach ($partnerGroupPermissions as $pgPerm)
		{
			self::addPartnerGroupAction($map, $pgPerm);
		}
		
		return $map;
	}
	
	
	
	// ---------------------------------------
	// -- Permission array handling methods --
	// ---------------------------------------
	
	/**
	 * Add an api action permission to the local map
	 * @param array $map map to fill
	 * @param vApiActionPermissionItem $item
	 */
	private static function addApiAction(array &$map, vApiActionPermissionItem $item)
	{
		$service = strtolower($item->getService());
		$action = strtolower($item->getAction());
		if (!isset($map[self::API_ACTIONS_ARRAY_NAME][$service])) {
			$map[self::API_ACTIONS_ARRAY_NAME][$service] = array();
			$map[self::API_ACTIONS_ARRAY_NAME][$service][$action] = array();
		}
		else if (!in_array($action, $map[self::API_ACTIONS_ARRAY_NAME][$service], true)) {
			$map[self::API_ACTIONS_ARRAY_NAME][$service][$action] = array();
		}
	}
	
	
	/**
	 * Add an api parameter permission to the local map
	 * @param array $map map to fill
	 * @param vApiParameterPermissionItem $item
	 */
	private static function addApiParameter(array &$map, vApiParameterPermissionItem $item)
	{
		$itemAction = strtolower($item->getAction());
		$itemObject = strtolower($item->getObject());
		if (!isset($map[self::API_PARAMETERS_ARRAY_NAME][$itemAction][$itemObject])) {
			$map[self::API_PARAMETERS_ARRAY_NAME][$itemAction][$itemObject] = array();
		}
		$map[self::API_PARAMETERS_ARRAY_NAME][$itemAction][$itemObject][strtolower($item->getParameter())] = true;
	}
	
	/**
	 * Add a partner group permission to the local map for the given action
	 * @param array $map map to fill
	 * @param Permission $permission partner group permission object
	 */
	private static function addPartnerGroupAction(array &$map, Permission $permission)
	{
		$partnerGroup = $permission->getPartnerGroup();
		if (!$permission->getPartnerGroup())
		{
			VidiunLog::notice('No partner group defined for permission id ['.$permission->getId().'] with type partner group ['.$permission->getType().']');
			return;
		}
		$partnerGroup = explode(',', trim($partnerGroup, ','));
		
		$permissionItems = $permission->getPermissionItems();
		
		foreach ($permissionItems as $item)
		{
			if ($item->getType() != PermissionItemType::API_ACTION_ITEM)
			{
				VidiunLog::notice('Permission item id ['.$item->getId().'] is not of type PermissionItemType::API_ACTION_ITEM but still defined in partner group permission id ['.$permission->getId().']');
				continue;
			}
			$service = strtolower($item->getService());
			$action  = strtolower($item->getAction());
			
			if (!isset($map[self::PARTNER_GROUP_ARRAY_NAME][$service]))
			{
				$map[self::PARTNER_GROUP_ARRAY_NAME][$service] = array();
				$map[self::PARTNER_GROUP_ARRAY_NAME][$service][$action] = array();
			}
			else if (!isset($map[self::PARTNER_GROUP_ARRAY_NAME][$service][$action]))
			{
				$map[self::PARTNER_GROUP_ARRAY_NAME][$service][$action] = array();
			}
			
			$map[self::PARTNER_GROUP_ARRAY_NAME][$service][$action] = array_merge($map[self::PARTNER_GROUP_ARRAY_NAME][$service][$action], $partnerGroup);
		}
	}
	
	private static function isEmpty($value)
	{
		if (is_null($value) || $value === '') {
			return true;
		}
		return false;
	}
	
	
	// --------------------
	// -- Public methods --
	// --------------------
	
	/**
	 * Init with allowed permissions for the user in the given VS or vCurrentContext if not VS given
	 * vCurrentContext::init should have been executed before!
	 * @param string $vs VS to extract user and partner IDs from instead of vCurrentContext
	 * @param boolean $useCache use cache or not
	 * @throws TODO: add all exceptions
	 */
	public static function init($useCache = null)
	{
		$securityContext = array(vCurrentContext::$partner_id, vCurrentContext::$vs);
		if ($securityContext === self::$lastInitializedContext) {
			self::$cacheWatcher->apply();
			return;
		}
		
		// verify that vCurrentContext::init has been executed since it must be used to init current context permissions
		if (!vCurrentContext::$vsPartnerUserInitialized) {
			VidiunLog::crit('vCurrentContext::initVsPartnerUser must be executed before initializing vPermissionManager');
			throw new Exception('vCurrentContext has not been initialized!', null);
		}
		
		// can be initialized more than once to support multirequest with different vCurrentContext parameters
		self::$lastInitializedContext = null;
		self::$cacheWatcher = new vApiCacheWatcher();
		self::$useCache = $useCache ? true : false;

		// copy vCurrentContext parameters (vCurrentContext::init should have been executed before)
		self::$requestedPartnerId = !self::isEmpty(vCurrentContext::$partner_id) ? vCurrentContext::$partner_id : null;
		self::$vsPartnerId = !self::isEmpty(vCurrentContext::$vs_partner_id) ? vCurrentContext::$vs_partner_id : null;
		if (self::$vsPartnerId == Partner::ADMIN_CONSOLE_PARTNER_ID && 
			vConf::hasParam('admin_console_partner_allowed_ips'))
		{
			$ipAllowed = false;
			$ipRanges = explode(',', vConf::get('admin_console_partner_allowed_ips'));
			foreach ($ipRanges as $curRange)
			{
				if (vIpAddressUtils::isIpInRange($_SERVER['REMOTE_ADDR'], $curRange))
				{
					$ipAllowed = true;
					break;
				}
			} 
			if (!$ipAllowed)
				throw new vCoreException("Admin console partner used from an unallowed address", vCoreException::PARTNER_BLOCKED);
		}
		self::$vsUserId = !self::isEmpty(vCurrentContext::$vs_uid) ? vCurrentContext::$vs_uid : null;
		if (self::$vsPartnerId != Partner::BATCH_PARTNER_ID)
			self::$vuser = !self::isEmpty(vCurrentContext::getCurrentVsVuser()) ? vCurrentContext::getCurrentVsVuser() : null;
		self::$vsString = vCurrentContext::$vs ? vCurrentContext::$vs : null;
		self::$adminSession = !self::isEmpty(vCurrentContext::$is_admin_session) ? vCurrentContext::$is_admin_session : false;
		
		// if vs defined - check that it is valid
		self::errorIfVsNotValid();
		
		// init partner, user, and role objects
		self::initPartnerUserObjects();

		// throw an error if VS partner (operating partner) is blocked
		self::errorIfPartnerBlocked();
		
		//throw an error if VS user is blocked
		self::errorIfUserBlocked();

		// init role ids
		self::initRoleIds();

		// init permissions map
		self::initPermissionsMap();
								
		// initialization done
		self::$lastInitializedContext = $securityContext;
		self::$cacheWatcher->stop();
		
		return true;
	}
	
	public static function getRoleIds(Partner $operatingPartner = null, vuser $vuser = null)
	{
		$roleIds = null;
		$vsString = vCurrentContext::$vs;
		$isAdminSession = !self::isEmpty(vCurrentContext::$is_admin_session) ? vCurrentContext::$is_admin_session : false;

		if (!$vsString ||
			(!$operatingPartner && vCurrentContext::$vs_partner_id != Partner::BATCH_PARTNER_ID))
		{
			$roleId = UserRolePeer::getIdByStrId (UserRoleId::NO_SESSION_ROLE);
			if($roleId)
				return array($roleId);
				
			return null;
		}

		$vs = vs::fromSecureString($vsString);
		$vsSetRoleId = $vs->getRole();

		if ($vsSetRoleId)
		{
			if ($vsSetRoleId == 'null')
			{
				return null;
			}
			$vsPartnerId = !self::isEmpty(vCurrentContext::$vs_partner_id) ? vCurrentContext::$vs_partner_id : null;
			//check if role exists
			$c = new Criteria();
			$c->addAnd(is_numeric($vsSetRoleId) ? UserRolePeer::ID : UserRolePeer::SYSTEM_NAME
				, $vsSetRoleId, Criteria::EQUAL);
			$partnerIds = array_map('strval', array($vsPartnerId, PartnerPeer::GLOBAL_PARTNER));
			$c->addAnd(UserRolePeer::PARTNER_ID, $partnerIds, Criteria::IN);
			$roleId = UserRolePeer::doSelectOne($c);

			if ($roleId){
				$roleIds = $roleId->getId();
			}else{
				VidiunLog::debug("Role id [$vsSetRoleId] does not exists");
				throw new vCoreException("Unknown role Id [$vsSetRoleId]", vCoreException::ID_NOT_FOUND);
			}
		}

		// if user is defined -> get his role IDs
		if (!$roleIds && $vuser) {
			$roleIds = $vuser->getRoleIds();
		}

		// if user has no defined roles or no user is defined -> get default role IDs according to session type (admin/not)
		if (!$roleIds)
		{
			if (!$operatingPartner)
			{
				// use system default roles
				if ($vs->isWidgetSession()) {
					$strId = UserRoleId::WIDGET_SESSION_ROLE;
				}
				elseif ($isAdminSession) {
					$strId = UserRoleId::PARTNER_ADMIN_ROLE;
				}
				else {
					$strId = UserRoleId::BASE_USER_SESSION_ROLE;
				}

				$roleIds = UserRolePeer::getIdByStrId ($strId);
			}
			else
			{
				if ($vs->isWidgetSession()){
					//there is only one partner widget role defined in the system
					$roleIds = $operatingPartner->getWidgetSessionRoleId();
				}
				elseif ($isAdminSession) {
					// there is only one partner admin role defined in the system
					$roleIds = $operatingPartner->getAdminSessionRoleId();
				}
				else {
					// a partner may have special defined user session roles - get them from partner object
					$roleIds = $operatingPartner->getUserSessionRoleId();
				}
			}
		}

		if ($roleIds) {
			$roleIds = explode(',', trim($roleIds, ','));
		}

		return $roleIds;
	}
	
	private static function initRoleIds()
	{
		self::$roleIds = self::getRoleIds(self::$operatingPartner, self::$vuser);
	}
	
	
	private static function initPartnerUserObjects()
	{
		if (self::$vsPartnerId == Partner::BATCH_PARTNER_ID) {
			self::$operatingPartner = null;
			self::$operatingPartnerId = self::$vsPartnerId;
			return;
		}
		
		$vsPartner = null;
		$requestedPartner = null;
		
		// init vs partner = operating partner
		if (!is_null(self::$vsPartnerId)) {
			$vsPartner = PartnerPeer::retrieveByPK(self::$vsPartnerId);
			if (!$vsPartner)
			{
				VidiunLog::crit('Unknown partner id ['.self::$vsPartnerId.']');
				throw new vCoreException("Unknown partner Id [" . self::$vsPartnerId ."]", vCoreException::ID_NOT_FOUND);
			}
		}
		
		// init requested partner
		if (!is_null(self::$requestedPartnerId)) {
			$requestedPartner = PartnerPeer::retrieveActiveByPK(self::$requestedPartnerId);
			if (!$requestedPartner)
			{
				VidiunLog::crit('Unknown partner id ['.self::$requestedPartnerId.']');
				throw new vCoreException("Unknown partner Id [" . self::$requestedPartnerId ."]", vCoreException::PARTNER_BLOCKED);
			}
		}
		
		// init current vuser
		if (self::$vsUserId && !self::$vuser) { // will never be null because vs::uid is never null
			vuserPeer::setUseCriteriaFilter(false);
			self::$vuser = vuserPeer::getActiveVuserByPartnerAndUid(self::$vsPartnerId, self::$vsUserId);
			vuserPeer::setUseCriteriaFilter(true);
			if (!self::$vuser)
			{
				self::$vuser = null;
				// error not thrown to support adding users 'on-demand'
				// current session will get default role according to session type (user/admin)
			}
		}
		
		// choose operating partner!
		if ($vsPartner) {
			self::$operatingPartner = $vsPartner;
			self::$operatingPartnerId = $vsPartner->getId();
		}
		else if (!self::$vsString && $requestedPartner) {
			self::$operatingPartner = $requestedPartner;
			self::$operatingPartnerId = $requestedPartner->getId();
			self::$vuser = null;
		}
	}
	
	
	
	private static function initPermissionsMap()
	{
		// init an empty map
		self::$map = self::initEmptyMap();
		
		if (!self::$roleIds)
		{
			self::$map = self::getPermissions(null);
		}
		else
		{
			foreach (self::$roleIds as $roleId)
			{
				// init actions and parameters arrays from cache
				$roleMap = self::getPermissions($roleId);
				
				// merge current role map to the global map
				self::$map = array_merge_recursive(self::$map, $roleMap);
			}
		}
	}
	
	// ----------------------------------------------------------------------------
	
	
	
	private static function errorIfVsNotValid()
	{
		// if no vs in current context - no need to check anything
		if (!self::$vsString) {
			return;
		}
		
		$vsObj = null;
		$res = vSessionUtils::validateVSessionNoTicket(self::$vsPartnerId, self::$vsUserId, self::$vsString, $vsObj);

		if ( 0 >= $res )
		{
			switch($res)
			{
				case vs::INVALID_STR:
					VidiunLog::err('Invalid VS ['.self::$vsString.']');
					break;
									
				case vs::INVALID_PARTNER:
					VidiunLog::err('Wrong partner ['.self::$vsPartnerId.'] actual partner ['.$vsObj->partner_id.']');
					break;
									
				case vs::INVALID_USER:
					VidiunLog::err('Wrong user ['.self::$vsUserId.'] actual user ['.$vsObj->user.']');
					break;
																		
				case vs::EXPIRED:
					VidiunLog::err('VS Expired [' . date('Y-m-d H:i:s', $vsObj->valid_until) . ']');
					break;
									
				case vs::LOGOUT:
					VidiunLog::err('VS already logged out');
					break;
				
				case vs::EXCEEDED_ACTIONS_LIMIT:
					VidiunLog::err('VS exceeded number of actions limit');
					break;
					
				case vs::EXCEEDED_RESTRICTED_IP:
					VidiunLog::err('IP does not match VS restriction');
					break;
			}
			
			throw new vCoreException("Invalid VS", vCoreException::INVALID_VS, vs::getErrorStr($res));
		}
	}
	
	
	private static function isPartnerAccessAllowed($service, $action)
	{
		if (is_null(self::$operatingPartnerId) || is_null(self::$requestedPartnerId)) {
			return true;
		}
		
		$partnerGroup = self::getPartnerGroup($service, $action);
		$accessAllowed = myPartnerUtils::allowPartnerAccessPartner ( self::$operatingPartnerId , $partnerGroup , self::$requestedPartnerId );
		if(!$accessAllowed)
			VidiunLog::debug("Operating partner [" . self::$operatingPartnerId . "] not allowed using requested partner [" . self::$requestedPartnerId . "] with partner group [$partnerGroup]");
			
		return $accessAllowed;
	}

	private static function errorIfUserBlocked()
	{
		if (!vCurrentContext::$vs_vuser)
			return;
		$status = vCurrentContext::$vs_vuser->getStatus();
		if ($status == VuserStatus::BLOCKED)
			throw new vCoreException("User blocked", vCoreException::USER_BLOCKED);
	}

	private static function errorIfPartnerBlocked()
	{
		if (!self::$operatingPartner) {
			return;
		}
		
		$partnerStatus = self::$operatingPartner->getStatus();
		
		if($partnerStatus == Partner::PARTNER_STATUS_CONTENT_BLOCK)
		{
		    throw new vCoreException("Partner blocked", vCoreException::PARTNER_BLOCKED);
		}
		if($partnerStatus != Partner::PARTNER_STATUS_ACTIVE)
		{
		    throw new vCoreException("Partner fully blocked", vCoreException::PARTNER_BLOCKED);
		}
	}
	
	/**
	 * Checks if the given service & action is permitted for the current user
	 * @param string $service Service name
	 * @param string $action Action name
	 * @return true if given service->action is accisible by the user or false otherwise
	 */
	public static function isActionPermitted($service, $action)
	{
		self::errorIfNotInitialized();
		
		$service = strtolower($service);
		$action = strtolower($action);
		
		$partnerAccessPermitted = self::isPartnerAccessAllowed($service, $action);
		if(!$partnerAccessPermitted)
		{
			VidiunLog::err("Partner is not allowed");
			return false;
		}

		if(self::$operatingPartner && PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIMIT_ALLOWED_ACTIONS, self::$operatingPartner->getId()))
		{
			$actionBlocked = self::isActionBlockedForPartner($service, $action);
			if($actionBlocked)
			{
				KalturaLog::err("The wanted service and action are not allowed for this partner");
				return false;
			}
		}

		$servicePermitted  = isset(self::$map[self::API_ACTIONS_ARRAY_NAME][$service]);
		if(!$servicePermitted)
		{
			VidiunLog::err("Service is not permitted");
			return false;
		}
		
		$actionPermitted   = isset(self::$map[self::API_ACTIONS_ARRAY_NAME][$service][$action]);
		if(!$actionPermitted)
			VidiunLog::err("Action is not permitted");
		
		return $actionPermitted;
	}

	protected static function isActionBlockedForPartner($service, $action)
	{
		$blockedActionsMapContent = kConf::getMap("blocked_actions_per_account");
		if(!empty($blockedActionsMapContent))
		{
			$partnerId = self::$operatingPartner->getId();
			if($partnerId && array_key_exists($partnerId, $blockedActionsMapContent))
			{
				$sectionId = $partnerId;
			}
			else if (array_key_exists(self::DEFAULT_ID, $blockedActionsMapContent))
			{
				$sectionId = self::DEFAULT_ID;
			}
			else
			{
				return false;
			}

			return self::isActionInBlockedActionsMap($service, $action, $sectionId, $blockedActionsMapContent);
		}
		return false;
	}

	protected static function isActionInBlockedActionsMap($service, $action, $sectionId, $blockedActionsMapContent)
	{
		$blockedActionsForPartner = $blockedActionsMapContent[$sectionId];
		foreach($blockedActionsForPartner as $blockedAction)
		{
			list($serviceId, $actionId) = explode(':', $blockedAction);
			if(preg_match("/$serviceId/", $service) && preg_match("/$actionId/", $action))
			{
				return true;
			}
		}
		return false;
	}

	private static function getParamPermitted($array_name, $objectName, $paramName)
	{
		self::errorIfNotInitialized();
		
		$objectName = strtolower($objectName);
		$paramName = strtolower($paramName);
		if ( !isset(self::$map[self::API_PARAMETERS_ARRAY_NAME][$array_name][$objectName]) && !isset(self::$map[self::API_PARAMETERS_ARRAY_NAME][ApiParameterPermissionItemAction::USAGE][$objectName]) )
		{
			return false;
		}
		if ($paramName === vApiParameterPermissionItem::ALL_VALUES_IDENTIFIER) {
			return true;
		}
		if (isset(self::$map[self::API_PARAMETERS_ARRAY_NAME][$array_name][$objectName][vApiParameterPermissionItem::ALL_VALUES_IDENTIFIER])) {
			return true;
		}
		return isset(self::$map[self::API_PARAMETERS_ARRAY_NAME][$array_name][$objectName][$paramName]) || isset(self::$map[self::API_PARAMETERS_ARRAY_NAME][ApiParameterPermissionItemAction::USAGE][$objectName][$paramName]);
		
	}
	
	/**
	 * Returns an array of parameter that belong to the object of type $object_name and are readable for the current user.
	 * @param string $object_name
	 * @return array parameter names
	 */
	public static function getReadPermitted($object_name, $param_name)
	{
		return self::getParamPermitted(ApiParameterPermissionItemAction::READ, $object_name, $param_name);
	}
	
	/**
	 * Returns an array of parameter that belong to the object of type $object_name and are insertable for the current user.
	 * @param string $object_name
	 * @return array parameter names
	 */
	public static function getInsertPermitted($object_name, $param_name)
	{
		return self::getParamPermitted(ApiParameterPermissionItemAction::INSERT, $object_name, $param_name);
	}
	
	/**
	 * Returns an array of parameter that belong to the object of type $object_name and are updatable for the current user.
	 * @param string $object_name
	 * @return array parameter names
	 */
	public static function getUpdatePermitted($object_name, $param_name)
	{
		return self::getParamPermitted(ApiParameterPermissionItemAction::UPDATE, $object_name, $param_name);
	}
	
	/**
	 * Returns an array of parameter that belong to the object of type $object_name and are useable for the current user.
	 * @param string $object_name
	 * @return array parameter names
	 */
	public static function getUsagePermitted($object_name, $param_name)
	{
		return self::getParamPermitted(ApiParameterPermissionItemAction::USAGE, $object_name, $param_name);
	}
	
	/**
	 * @param string $service
	 * @param string $action
	 * @return allowed partner group for the given service and action for the current user
	 */
	public static function getPartnerGroup($service, $action)
	{
		self::errorIfNotInitialized();
		
		$service = strtolower($service); //TODO: save service with normal case ?
		$action = strtolower($action); //TODO: save actions with normal case ?
		
		$partnerGroupSet   = isset(self::$map[self::PARTNER_GROUP_ARRAY_NAME][$service]) &&isset(self::$map[self::PARTNER_GROUP_ARRAY_NAME][$service][$action]);
		
		if (!$partnerGroupSet)
		{
			return self::$operatingPartnerId;
		}
		
		$partnerGroup =  self::$map[self::PARTNER_GROUP_ARRAY_NAME][$service][$action];
		$partnerGroup[] = self::$operatingPartnerId;
		
		if (in_array(myPartnerUtils::ALL_PARTNERS_WILD_CHAR, $partnerGroup, true))
		{
			if (self::$requestedPartnerId && self::$requestedPartnerId != self::$operatingPartnerId)
				return self::$requestedPartnerId;
				
			return myPartnerUtils::ALL_PARTNERS_WILD_CHAR;
		}
		
		$partnerGroup = array_filter($partnerGroup);
		if (self::$requestedPartnerId && self::$requestedPartnerId != self::$operatingPartnerId && in_array(self::$requestedPartnerId, $partnerGroup))
			return self::$requestedPartnerId;
		
		$partnerGroup = implode(',', $partnerGroup);
		return $partnerGroup;
	}
	
	/**
	 * @return array current role ids
	 */
	public static function getCurrentRoleIds()
	{
		return self::$roleIds;
	}
	
	/**
	 * @return return current permission names
	 */
	public static function getCurrentPermissions()
	{
		return self::$map[self::PERMISSION_NAMES_ARRAY];
	}
	
	/**
	 * @return boolean
	 */
	public static function isPermitted($permissionName)
	{
		return isset(self::$map[self::PERMISSION_NAMES_ARRAY][$permissionName]);
	}
		
	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::shouldConsumeChangedEvent()
	 */
	public function shouldConsumeChangedEvent(BaseObject $object, array $modifiedColumns)
	{
		if($object instanceof Permission && $object->getPartnerId() != PartnerPeer::GLOBAL_PARTNER)
			return true;
		
		if ($object instanceof UserRole && $object->getPartnerId() != PartnerPeer::GLOBAL_PARTNER &&
			     (in_array(UserRolePeer::PERMISSION_NAMES, $modifiedColumns) || in_array(UserRolePeer::STATUS, $modifiedColumns))    )
			return true;
			
		if ($object instanceof PermissionToPermissionItem)
			return true;
			
		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::objectChanged()
	 */
	public function objectChanged(BaseObject $object, array $modifiedColumns)
	{
		if($object instanceof Permission && $object->getPartnerId() != PartnerPeer::GLOBAL_PARTNER)
		{
			self::markPartnerRoleCacheDirty($object->getPartnerId());
			return true;
		}
		
		if ($object instanceof UserRole && $object->getPartnerId() != PartnerPeer::GLOBAL_PARTNER &&
			     (in_array(UserRolePeer::PERMISSION_NAMES, $modifiedColumns) || in_array(UserRolePeer::STATUS, $modifiedColumns))    )
		{
			self::markPartnerRoleCacheDirty($object->getPartnerId());
			return true;
		}
		
		if ($object instanceof PermissionToPermissionItem)
		{
			$permission = $object->getPermission();
			if ($permission && $permission->getPartnerId() != PartnerPeer::GLOBAL_PARTNER)
			{
				self::markPartnerRoleCacheDirty($permission->getPartnerId());
				return true;
			}
		}
		
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectCreatedEventConsumer::shouldConsumeCreatedEvent()
	 */
	public function shouldConsumeCreatedEvent(BaseObject $object)
	{
		if($object instanceof Permission)
			return true;
		
		if ($object instanceof PermissionToPermissionItem)
			return true;
		
		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectCreatedEventConsumer::objectCreated()
	 */
	public function objectCreated(BaseObject $object)
	{
		if($object instanceof Permission)
		{
			// changes in permissions for partner, may require new cache generation
			if ($object->getPartnerId() != PartnerPeer::GLOBAL_PARTNER)
			{
				self::markPartnerRoleCacheDirty($object->getPartnerId());
				return true;
			}
		}
		
		if ($object instanceof PermissionToPermissionItem)
		{
			$permission = $object->getPermission();
			if ($permission && $permission->getPartnerId() != PartnerPeer::GLOBAL_PARTNER)
			{
				self::markPartnerRoleCacheDirty($permission->getPartnerId());
				return true;
			}
		}
		
		return true;
	}
	
	private static function markPartnerRoleCacheDirty($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if (!$partner) {
			VidiunLog::err("Cannot find partner with id [$partnerId]");
			return;
		}

		vEventsManager::raiseEventDeferred(new vObjectInvalidateCacheEvent($partner));
	}

	public function shouldConsumeInvalidateCache($object, $params = null)
	{
		if($object instanceof Partner)
		{
			return true;
		}
		return false;
	}

	public function invalidateCache($object, $params = null)
	{
		if($object instanceof Partner)
		{
			$object->setRoleCacheDirtyAt(time());
			$object->save();
		}
		return true;
	}
	/**
	 *
	 * add ps2 permission for given partner
	 * @param Partner $partner
	 */
	public static function setPs2Permission(Partner $partner)
 	{
 		$ps2Permission = new Permission();
 		$ps2Permission->setName(PermissionName::FEATURE_PS2_PERMISSIONS_VALIDATION);
 		$ps2Permission->setPartnerId($partner->getId());
 		$ps2Permission->setStatus(PermissionStatus::ACTIVE);
 		$ps2Permission->setType(PermissionType::SPECIAL_FEATURE);
 		$ps2Permission->save();
 	}
 	
/**
	 *
	 * add ps2 permission for given partner
	 * @param Partner $partner
	 */
	public static function sePermissionForPartner(Partner $partner, $permission)
 	{
 		$ps2Permission = new Permission();
 		$ps2Permission->setName($permission);
 		$ps2Permission->setPartnerId($partner->getId());
 		$ps2Permission->setStatus(PermissionStatus::ACTIVE);
 		$ps2Permission->setType(PermissionType::SPECIAL_FEATURE);
 		$ps2Permission->save();
 	}
	
}
