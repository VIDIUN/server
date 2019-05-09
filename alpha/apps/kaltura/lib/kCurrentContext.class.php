<?php
/**
 * Will hold the current context of the API call / current running batch.
 * The inforamtion is static per call and can be used from anywhare in the code. 
 */
class vCurrentContext
{
	/**
	 * @var string
	 */
	public static $vs;
	
	/**
	 * @var vs
	 */
	public static $vs_object;
	
	/**
	 * @var string
	 */
	public static $vs_hash;
	
	/**
	 * This value is populated only in case of impersonation using partnerId in the request.
	 * It's used by the batch and the admin console only.
	 * 
	 * @var int
	 */
	public static $partner_id;

	/**
	 * @var int
	 */
	public static $vs_partner_id;

	/**
	 * @var int
	 */
	public static $master_partner_id;
	
	/**
	 * @var string
	 */
	public static $uid;
	
	
	/**
	 * @var string
	 */
	public static $vs_uid;
	
	/**
	 * @var int
	 */
	public static $vs_vuser_id = null;
	
	/**
	 * @var vuser
	 */
	public static $vs_vuser;

	/**
	 * @var string
	 */
	public static $ps_vesion;
	
	/**
	 * @var string
	 */
	public static $call_id;
	
	/**
	 * @var string
	 */
	public static $service;
	
	/**
	 * @var string
	 */
	public static $action;
	
	/**
	 * @var string
	 */
	public static $host;
	
	/**
	 * @var string
	 */
	public static $client_version;
	
	/**
	 * @var string
	 */
	public static $client_lang;
	
	/**
	 * @var string
	 */
	public static $user_ip;
	
	/**
	 * @var bool
	 */
	public static $is_admin_session;
	
	/**
	 * @var bool
	 */
	public static $vsPartnerUserInitialized = false;
	
	/**
	 * @var int
	 */
	public static $multiRequest_index = 1;
	
	/**
	 * @var callable
	 */	
	public static $serializeCallback;

	/**
	 * @var int
	 */
	public static $HTMLPurifierBehaviour = null;

	/**
	 * @var bool
	 */
	public static $HTMLPurifierBaseListOnlyUsage = null;
	
	public static function getEntryPoint()
	{
		if(self::$service && self::$action)
			return self::$service . '::' . self::$action;
			
		if(isset($_SERVER['SCRIPT_NAME']))
			return $_SERVER['SCRIPT_NAME'];
			
		if(isset($_SERVER['PHP_SELF']))
			return $_SERVER['PHP_SELF'];
			
		if(isset($_SERVER['SCRIPT_FILENAME']))
			return $_SERVER['SCRIPT_FILENAME'];
			
		return '';
	}
	
	public static function isApiV3Context()
	{		
		if (vCurrentContext::$ps_vesion == 'ps3') {
			return true;
		}
		
		return false;
	}
	
	public static function initPartnerByEntryId($entryId)
	{		
		$entry = entryPeer::retrieveByPKNoFilter($entryId);
		if(!$entry)
			return null;
			
		vCurrentContext::$vs = null;
		vCurrentContext::$vs_object = null;
		vCurrentContext::$vs_hash = null;
		vCurrentContext::$vs_partner_id = $entry->getPartnerId();
		vCurrentContext::$vs_uid = null;
		vCurrentContext::$master_partner_id = null;
		vCurrentContext::$partner_id = $entry->getPartnerId();
		vCurrentContext::$uid = null;
		vCurrentContext::$is_admin_session = false;
		
		return $entry;
	}
	
	public static function initPartnerByAssetId($assetId)
	{		
		VidiunCriterion::disableTags(array(VidiunCriterion::TAG_ENTITLEMENT_ENTRY, VidiunCriterion::TAG_WIDGET_SESSION));
		$asset = assetPeer::retrieveByIdNoFilter($assetId);
		VidiunCriterion::restoreTags(array(VidiunCriterion::TAG_ENTITLEMENT_ENTRY, VidiunCriterion::TAG_WIDGET_SESSION));
		
		if(!$asset)
			return null;
			
		vCurrentContext::$vs = null;
		vCurrentContext::$vs_object = null;
		vCurrentContext::$vs_hash = null;
		vCurrentContext::$vs_partner_id = $asset->getPartnerId();
		vCurrentContext::$vs_uid = null;
		vCurrentContext::$master_partner_id = null;
		vCurrentContext::$partner_id = $asset->getPartnerId();
		vCurrentContext::$uid = null;
		vCurrentContext::$is_admin_session = false;
		
		return $asset;
	}
	
	public static function initVsPartnerUser($vsString, $requestedPartnerId = null, $requestedPuserId = null)
	{		
		if (!$vsString)
		{
			vCurrentContext::$vs = null;
			vCurrentContext::$vs_object = null;
			vCurrentContext::$vs_hash = null;
			vCurrentContext::$vs_partner_id = null;
			vCurrentContext::$vs_uid = null;
			vCurrentContext::$master_partner_id = null;
			vCurrentContext::$partner_id = $requestedPartnerId;
			vCurrentContext::$uid = $requestedPuserId;
			vCurrentContext::$is_admin_session = false;
		}
		else
		{
			try { $vsObj = vSessionUtils::crackVs ( $vsString ); }
			catch(Exception $ex)
			{
				if (strpos($ex->getMessage(), "INVALID_STR") !== null)
					throw new vCoreException($ex->getMessage(), vCoreException::INVALID_VS, $vsString);
				else 
					throw $ex;
			}
		
			vCurrentContext::$vs = $vsString;
			vCurrentContext::$vs_object = $vsObj;
			vCurrentContext::$vs_hash = $vsObj->getHash();
			vCurrentContext::$vs_partner_id = $vsObj->partner_id;
			vCurrentContext::$vs_uid = $vsObj->user;
			vCurrentContext::$master_partner_id = $vsObj->master_partner_id ? $vsObj->master_partner_id : vCurrentContext::$vs_partner_id;
			vCurrentContext::$is_admin_session = $vsObj->isAdmin();
			
			if($requestedPartnerId == PartnerPeer::GLOBAL_PARTNER && self::$vs_partner_id > PartnerPeer::GLOBAL_PARTNER)
				$requestedPartnerId = null;
			
			vCurrentContext::$partner_id = $requestedPartnerId;
			vCurrentContext::$uid = $requestedPuserId;
		}

		// set partner ID for logger
		if (vCurrentContext::$partner_id) {
			$GLOBALS["partnerId"] = vCurrentContext::$partner_id;
		}
		else if (vCurrentContext::$vs_partner_id) {
			$GLOBALS["partnerId"] = vCurrentContext::$vs_partner_id;
		}
		
		self::$vsPartnerUserInitialized = true;
	}
	
	public static function getCurrentVsVuser($activeOnly = true)
	{
		if(!vCurrentContext::$vs_vuser)
		{			
			vCurrentContext::$vs_vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::$vs_partner_id, vCurrentContext::$vs_uid, true);
		}
		
		if(vCurrentContext::$vs_vuser &&
		   $activeOnly && 
		   vCurrentContext::$vs_vuser->getStatus() != VuserStatus::ACTIVE)
		   	return null;
			
		return vCurrentContext::$vs_vuser;
	}

	public static function getCurrentSessionType()
	{
		if(!self::$vs_object)
			return vSessionBase::SESSION_TYPE_NONE;
			
		if(self::$vs_object->isAdmin())
			return vSessionBase::SESSION_TYPE_ADMIN;
			
		if(self::$vs_object->isWidgetSession())
			return vSessionBase::SESSION_TYPE_WIDGET;
			
		return vSessionBase::SESSION_TYPE_USER;
	}

	public static function getCurrentPartnerId()
	{
		if(isset(self::$partner_id))
			return self::$partner_id;
			
		return self::$vs_partner_id;
	}

	public static function getCurrentVsVuserId()
	{
		if (!is_null(vCurrentContext::$vs_vuser_id))
			return vCurrentContext::$vs_vuser_id;
			
		$vsVuser = vCurrentContext::getCurrentVsVuser(false);
		if($vsVuser)
			vCurrentContext::$vs_vuser_id = $vsVuser->getId();
		else 
			vCurrentContext::$vs_vuser_id = 0;
			
		return vCurrentContext::$vs_vuser_id;
	}
}
