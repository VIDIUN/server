<?php
/**
 * @abstract
 * @package api
 * @subpackage services
 */
abstract class VidiunBaseService 
{
	/**
	 * @var vs
	 */
	private $vs = null;
	
	/**
	 * @var Partner
	 */
	private $partner = null;

	/**
	 * @var int
	 */
	private $partnerId = null;
	
	/**
	 * @var vuser
	 */
	private $vuser = null;

	/**
	 * @var VidiunPartner
	 */
	private $operating_partner = null;
	
	/**
	 * @var VidiunDetachedResponseProfile
	 */
	private $responseProfile = null;
	 
	
	protected $private_partner_data = null; /// will be used internally and from the actual services for setting the
	
	protected $impersonatedPartnerId = null;
	
	protected $serviceId = null;
	
	protected $serviceName = null;
	
	protected $actionName = null;
	
	protected $partnerGroup = null;
	
	public function __construct()
	{
		//TODO: initialize $this->serviceName here instead of in initService method
	}	

	
	public function __destruct( )
	{
	}
	
	
	/**
	 * Should return true or false for allowing/disallowing vidiun network filter for the given action.
	 * Can be extended to partner specific checks etc...
	 * @return true if "vidiun network" is enabled for the given action or false otherwise
	 * @param string $actionName action name
	 */
	protected function vidiunNetworkAllowed($actionName)
	{
		return false;
	}
	
	/**
	 * Should return 'false' if no partner is required for that action, to make it usable with no VS or partner_id variable.
	 * Return 'true' otherwise (most actions).
	 * @param string $actionName
	 */
	protected function partnerRequired($actionName)
	{
		return true;
	}
	
	/**
	 * Should return 'true' if global partner (partner 0) should be added to the partner group filter for the given action, or 'false' otherwise.
	 * Enter description here ...
	 * @param string $actionName action name
	 */
	protected function globalPartnerAllowed($actionName)
	{
		return false;
	} 
		
	public function setResponseProfile(VidiunDetachedResponseProfile $responseProfile = null)
	{
		$this->responseProfile = $responseProfile;
	}
		
	/**
	 * @return VidiunDetachedResponseProfile
	 */
	protected function getResponseProfile()
	{
		return $this->responseProfile;
	}
	
	public function initService($serviceId, $serviceName, $actionName)
	{	
		// init service and action name
		$this->serviceId = $serviceId;
		$this->serviceName = $serviceName;
		$this->actionName  = $actionName;
		
		// impersonated partner = partner parameter from the request
		$this->impersonatedPartnerId = vCurrentContext::$partner_id;
		
		$this->vs = vCurrentContext::$vs_object ? vCurrentContext::$vs_object : null;
		
		// operating partner = partner from the request or the vs partner
		$partnerId = vCurrentContext::getCurrentPartnerId();
		
		// if there is no session, assume it's partner 0 using actions that doesn't require vs
		if(is_null($partnerId))
			$partnerId = 0;
		
		$this->partnerId = $partnerId;

		// check if current aciton is allowed and if private partner data access is allowed
		$allowPrivatePartnerData = false;
		$actionPermitted = $this->isPermitted($allowPrivatePartnerData);

		// action not permitted at all, not even vidiun network
		if (!$actionPermitted)
		{			
			$e = new VidiunAPIException ( APIErrors::SERVICE_FORBIDDEN, $this->serviceId.'->'.$this->actionName); //TODO: should sometimes thorow MISSING_VS instead
			header("X-Vidiun:error-".$e->getCode());
			header("X-Vidiun-App: exiting on error ".$e->getCode()." - ".$e->getMessage());
			throw $e;		
		}

		$this->validateApiAccessControl();
		
		// init partner filter parameters
		$this->private_partner_data = $allowPrivatePartnerData;
		$this->partnerGroup = vPermissionManager::getPartnerGroup($this->serviceId, $this->actionName);
		if ($this->globalPartnerAllowed($this->actionName)) {
			$this->partnerGroup = PartnerPeer::GLOBAL_PARTNER.','.trim($this->partnerGroup,',');
		}
		
		$this->setPartnerFilters($partnerId);
		
		vCurrentContext::$HTMLPurifierBehaviour = $this->getPartner()->getHtmlPurifierBehaviour();
		vCurrentContext::$HTMLPurifierBaseListOnlyUsage = $this->getPartner()->getHtmlPurifierBaseListUsage();
	}

	/**
	 * apply partner filters according to current context and permissions
	 * 
	 * @param int $partnerId
	 */
	protected function setPartnerFilters($partnerId)
	{
		myPartnerUtils::resetAllFilters();
		myPartnerUtils::applyPartnerFilters($partnerId ,$this->private_partner_data ,$this->partnerGroup() ,$this->vidiunNetworkAllowed($this->actionName));
	}
	
/* >--------------------- Security and config settings ----------------------- */

	/**
	 * Check if current action is permitted for current context (vs/partner/user)
	 * @param bool $allowPrivatePartnerData true if access to private partner data is allowed, false otherwise (vidiun network)
	 * @throws VidiunErrors::MISSING_VS
	 */
	protected function isPermitted(&$allowPrivatePartnerData)
	{		
		// if no partner defined but required -> error MISSING_VS
		if ($this->partnerRequired($this->actionName) && 
			$this->partnerId != Partner::BATCH_PARTNER_ID && 
			!$this->getPartner())
		{
			throw new VidiunAPIException(VidiunErrors::MISSING_VS);
		}
		
		// check if actions is permitted for current context
		$isActionPermitted = vPermissionManager::isActionPermitted($this->serviceId, $this->actionName);
		
		// if action permitted - no problem to access action and the private partner data
		if ($isActionPermitted) {
			$allowPrivatePartnerData = true; // allow private partner data
			return true; // action permitted with access to partner private data
		}
		VidiunLog::err("Action is not permitted");
		
		// action not permitted for current user - check if vidiun network is allowed
		if (!vCurrentContext::$vs && $this->vidiunNetworkAllowed($this->actionName))
		{
			// if the service action support vidiun network - continue without private data
			$allowPrivatePartnerData = false; // DO NOT allow private partner data
			return true; // action permitted (without private partner data)
		}
		VidiunLog::err("Vidiun network is not allowed");
		
		// action not permitted, not even without private partner data access
		return false;
	}
		
		
	/**
	 * Can be used from derived classes to set additionl filter that don't automatically happen in applyPartnerFilters
	 * 
	 * @param string $peer
	 */
	protected function applyPartnerFilterForClass($peer)
	{
		if ( $this->getPartner() )
			$partner_id = $this->getPartner()->getId();
		else
			$partner_id = Partner::PARTNER_THAT_DOWS_NOT_EXIST;
			
		myPartnerUtils::addPartnerToCriteria ( $peer , $partner_id , $this->private_partner_data , $this->partnerGroup($peer) , $this->vidiunNetworkAllowed($this->actionName)  );
	}	
	
	
	protected function applyPartnerFilterForClassNoVidiunNetwork ( $peer )
	{
		if ( $this->getPartner() )
			$partner_id = $this->getPartner()->getId();
		else
			$partner_id = -1; 
		myPartnerUtils::addPartnerToCriteria ( $peer , $partner_id , $this->private_partner_data , $this->partnerGroup($peer) , null );
	}
/* <--------------------- Security and config settings ----------------------- */	
	
	/**
	 * @return A comma seperated string of partner ids to which current context is allowed to access
	 */
	protected function partnerGroup($peer = null) 		
	{ 		
		return $this->partnerGroup;
	}
	
	/**
	 * 
	 * @return vs
	 */
	public function getVs()
	{
		return $this->vs;
	}

	public function getPartnerId()
	{
		return $this->partnerId;
	}
	
	/**
	 * @return Partner
	 */
	public function getPartner()
	{
		if (!$this->partner)
			$this->partner = PartnerPeer::retrieveByPK( $this->partnerId );
			
		return $this->partner; 
	}
	
	/**
	 * Returns Vuser (New vuser will be created if it doesn't exists) 
	 *
	 * @return vuser
	 */
	public function getVuser()
	{
		if (!$this->vuser)
		{
			// if no vs, puser id will be null
			if ($this->vs)
				$puserId = $this->vs->user;
			else
				$puserId = null;
				
			$vuser = vuserPeer::createVuserForPartner($this->getPartnerId(), $puserId);
			
			if ($vuser->getStatus() !== VidiunUserStatus::ACTIVE)
				throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
			
			$this->vuser = $vuser;
		}
		
		return $this->vuser;
	}
	
	protected function getVsUniqueString()
	{
		if ($this->vs)
		{
			return $this->vs->getUniqueString();
		}
		else
		{
			return substr ( md5( rand ( 10000,99999 ) . microtime(true) ) , 1 , 7 );
			//throw new Exception ( "Cannot find unique string" );
		}

	}
	
	/**
	 * @param string $filePath
	 * @param string $mimeType
	 * @param string $key
	 * @param string $iv
	 * @param int $fileSize
	 * @return vRendererDumpFile
	 */
	protected function dumpFile($filePath, $mimeType, $key = null, $iv = null, $fileSize = null)
	{
		$maxAge = null;
		if ($this->vs)
		{
			$maxAge = min(max($this->vs->valid_until - time(), 1), 8640000);
		}

		return vFileUtils::getDumpFileRenderer($filePath, $mimeType, $maxAge, 0, null, $key, $iv, $fileSize);
	}
	
	/**
	 * @param ISyncableFile $syncable
	 * @param int $fileSubType
	 * @param string $fileName
	 * @param bool $forceProxy
	 * @throws VidiunErrors::FILE_DOESNT_EXIST
	 */
	protected function serveFile(ISyncableFile $syncable, $fileSubType, $fileName, $entryId = null, $forceProxy = false)
	{
		/* @var $fileSync FileSync */
		$syncKey = $syncable->getSyncKey($fileSubType);
		if(!vFileSyncUtils::fileSync_exists($syncKey))
			throw new VidiunAPIException(VidiunErrors::FILE_DOESNT_EXIST);

		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
		if($local)
		{
			$filePath = $fileSync->getFullPath();
			$mimeType = vFile::mimeType($filePath);
			
			//PHP's built in mime_content_type funtion returns mime_type of text/x-c++ for anny txt file that contains the word class within
			//Until this is fixed we will check the file extension and manually set the file type to text/plain
			if($mimeType == "text/x-c++" && pathinfo($filePath, PATHINFO_EXTENSION) == "txt")
				$mimeType = "text/plain";
			
			$key = $fileSync->isEncrypted() ?  $fileSync->getEncryptionKey() : null;
			$iv = $key ? $fileSync->getIv() : null;
			return $this->dumpFile($filePath, $mimeType, $key, $iv);
		}
		else if ( in_array($fileSync->getDc(), vDataCenterMgr::getDcIds()) )
		{
			$remoteUrl = vDataCenterMgr::getRedirectExternalUrl($fileSync);
			VidiunLog::info("Redirecting to [$remoteUrl]");
			if($forceProxy)
			{
				vFileUtils::dumpApiRequest($remoteUrl);
			}
			else
			{
				//TODO find or build function which redurects the API request with all its parameters without using curl.
				// or redirect if no proxy
				header("Location: $remoteUrl");
				die;
			}
		}
		else
		{
			$remoteUrl =  $fileSync->getExternalUrl($entryId);
			header("Location: $remoteUrl");
			die;
		}	
	}

	protected function validateApiAccessControl($partnerId = null)
	{
		// ignore for system partners
		// for cases where an api action has a 'partnerId' parameter which will causes loading that partner instead of the vs partner
		if ($this->getVs() && $this->getVs()->partner_id < 0)
			return;
		
		if (is_null($partnerId))
			$partner = $this->getPartner();
		else
			$partner = PartnerPeer::retrieveByPK($partnerId);
		
		if (!$partner)
			return;
		
		if (!$partner->validateApiAccessControl())
			throw new VidiunAPIException(APIErrors::SERVICE_ACCESS_CONTROL_RESTRICTED, $this->serviceId.'->'.$this->actionName);
	}
}
