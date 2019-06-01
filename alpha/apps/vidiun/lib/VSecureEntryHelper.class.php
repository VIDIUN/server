<?php
/**
 * @package Core
 * @subpackage utils
 *
 */
class VSecureEntryHelper
{
	/**
	 * 
	 * @var entry
	 */
	private $entry;

	/**
	 *
	 * @var asset
	 */
	private $asset;
	
	/**
	 * 
	 * @var string
	 */
	private $vsStr;
	
	/**
	 * 
	 * @var vs
	 */
	private $vs;
	
	/**
	 * 
	 * @var string
	 */
	private $referrer;
	
	/**
	 * Indicates what contexts should be tested 
	 * No contexts means any context
	 * 
	 * @var array of ContextType
	 */
	private $contexts;
	
	/**
	 * Indicates that access control need to be checked every request and therefore can't be cached
	 * 
	 * @var bool
	 */
	private $disableCache;
	
	/**
	 * @var array
	 */
	private $hashes;
	
	/**
	 * the result of applyContext
	 * @var vEntryContextDataResult
	 */
	private $contextResult;
	
	/**
	 * access control actions lists keyed by RuleActionType
	 * @var array
	 */
	private $actionLists = array();


	/**
	 * Array containing trusted admministrative partner IDs
	 * @var array
	 */
	protected static $trustedPartnerIds = array (Partner::BATCH_PARTNER_ID);

	/**
	 * @var accessControlScope
	 */
	protected $scope;

	/**
	 * 
	 * @param entry $entry
	 */
	public function __construct(entry $entry, $vsStr, $referrer, $contexts = array(), $hashes = array(), $asset = null)
	{
		if(!is_array($contexts))
			$contexts = array($contexts);
			
		if($entry->getSecurityParentId())
		{
			$entry = $entry->getParentEntry();
			if(!$entry)
				VExternalErrors::dieError(VExternalErrors::PARENT_ENTRY_ID_NOT_FOUND, "Entry is configured with parent entry, but parent entry was not found");
		}

		$this->entry = $entry;
		$this->vsStr = $vsStr;
		$this->referrer = $referrer;
		$this->contexts = $contexts;
		$this->hashes = $hashes;
		$this->asset = $asset;
		
		$this->validateVs();
		$this->applyContext();
	}
	
	public function hasRules($contextType = null)
	{
		$accessControl = $this->entry->getAccessControl();
		if ($accessControl)
			return $accessControl->hasRules($contextType);
			
		return false;
	}
	
	public function shouldPreview()
	{
		if ($this->isVsAdmin())
			return false;
		
		if ($this->isEntryInModeration()) // don't preview when entry is in moderation
			return false;
			
		// should preview only when the access control is valid, but the preview and session restrictions exists
		if ($this->contextResult)
		{
			if ($this->shouldBlock())
				return false;
					
			return $this->getActionList(RuleActionType::PREVIEW);
		}
		return false;
	}
	
	public function getPreviewLength()
	{
		$preview = null;
		if ($this->contextResult && $this->getActionList(RuleActionType::PREVIEW))
		{			
			$actions = $this->getActionList(RuleActionType::PREVIEW);
			foreach($actions as $action)
			{
				$preview = $preview ? min($preview, $action->getLimit()) : $action->getLimit();
			}
		}
		return $preview;
	}

	public function validateApiAccessControl()
	{
		$partner = $this->entry->getPartner();
		if ($partner && !$partner->validateApiAccessControl())
			VExternalErrors::dieError(VExternalErrors::SERVICE_ACCESS_CONTROL_RESTRICTED);
	}

	public function validateForPlay($performApiAccessCheck = true)
	{
	    if ($this->contexts != array(ContextType::THUMBNAIL))
        {
            if ( ! ($this->vs &&
                   ($this->isVsAdmin() ||
                    $this->vs->verifyPrivileges(vs::PRIVILEGE_VIEW, vs::PRIVILEGE_WILDCARD) ||
                    $this->vs->verifyPrivileges(vs::PRIVILEGE_VIEW, $this->entry->getId()) ))){
                $this->validateModeration();
                $this->validateScheduling();
            }
        }

        $this->validateAccessControl($performApiAccessCheck);
	}
	
	public function validateForDownload()
	{
		$this->validateApiAccessControl();
		
		if ($this->vs)
		{
			if ($this->isVsAdmin()) // no need to validate when vs is admin
				return;
			
			if ($this->vs->verifyPrivileges(vs::PRIVILEGE_DOWNLOAD, vs::PRIVILEGE_WILDCARD)) // no need to validate when we have wildcard download privilege
				return;
				
			if ($this->vs->verifyPrivileges(vs::PRIVILEGE_DOWNLOAD, $this->entry->getId())) // no need to validate when we have specific entry download privilege
				return;
		}	
			
		$this->validateForPlay(false);
	}
	
	protected function validateModeration()
	{
		if ($this->isVsAdmin()) // no need to validate when vs is admin
			return;
			
		if ($this->isEntryInModeration())
			VExternalErrors::dieError(VExternalErrors::ENTRY_MODERATION_ERROR);
	}
	
	public function validateAccessControl($performApiAccessCheck = true)
	{
		if ($performApiAccessCheck)
		{
			$this->validateApiAccessControl();
		}
		
		if(!$this->contextResult)
			return;
			
		if(count($this->contextResult->getMessages()))
		{
			foreach($this->contextResult->getMessages() as $msg)
				header("X-Vidiun: access-control: $msg");
		}

		if ($this->shouldBlock())
		{
			VExternalErrors::dieError(VExternalErrors::ACCESS_CONTROL_RESTRICTED);
		}
	}

	public function getActionList($actionType)
	{
		if (!isset($this->actionLists[$actionType]))
			return null;
		
		return $this->actionLists[$actionType]; 
	}
	
	public function filterAllowedFlavorParams(array $flavorParamsIds)
	{
		$actionList = $this->getActionList(RuleActionType::LIMIT_FLAVORS);
		if ($actionList)
		{
			// take only the first LIMIT_FLAVORS action
			$action = reset($actionList);
		
			$actionflavorParamsIds = explode(',', $action->getFlavorParamsIds());
			$flavorParamsIds = $action->getIsBlockedList() ? array_diff($flavorParamsIds, $actionflavorParamsIds) :	array_intersect($flavorParamsIds, $actionflavorParamsIds);
		}
		
		return $flavorParamsIds;
	}
	
	public function isAssetAllowed(asset $asset)
	{
		if ($this->vs && $this->vs->verifyPrivileges(vs::PRIVILEGE_DOWNLOAD_ASSET, $asset->getId()))
			return true;

		return $this->isFlavorParamsAllowed($asset->getFlavorParamsId());		
	}
	
	public function shouldBlock()
	{
		return $this->getActionList(RuleActionType::BLOCK);		
	}
	
	public function shouldServeFromServerNode()
	{
		$actionsList = $this->getActionList(RuleActionType::SERVE_FROM_REMOTE_SERVER);
		if(!$actionsList)
			return null;
		
		/* @var $action vAccessControlServeRemoteEdgeServerAction */
		$action = reset($actionsList);
		$activeServerNodes =  $action->getRegiteredNodeServers();
		
		if(!count($activeServerNodes))
			return null;
		
		return $activeServerNodes[0];
	}
	
	protected function isFlavorParamsAllowed($flavorParamsId)
	{
		$actionList = $this->getActionList(RuleActionType::LIMIT_FLAVORS);
		if ($actionList)
		{
			// take only the first LIMIT_FLAVORS action
			$action = reset($actionList);
			$flavorParamsIds = explode(',', $action->getFlavorParamsIds());
			$exists = in_array($flavorParamsId, $flavorParamsIds);
			if($action->getIsBlockedList())
				return !$exists;
			else 
				return $exists;
		}
		return true;
	}

	public function updateDeliveryAttributes(DeliveryProfileDynamicAttributes $deliveryAttributes)
	{
		foreach ($this->actionLists as $actionList)
		{
			// take only the first action of each type
			foreach ($actionList as $action)
			{
				if($action->applyDeliveryProfileDynamicAttributes($deliveryAttributes))
					break;
			}	
		}
	}
	
	protected function applyContext()
	{
		$this->contextResult = null;
		$accessControl = $this->entry->getAccessControl();
		if(!$accessControl)
			return;
			
		$this->contextResult = new vEntryContextDataResult();
		$this->scope = $this->getAccessControlScope();
		$this->disableCache = $accessControl->applyContext($this->contextResult, $this->scope);

		if (count ( $this->contextResult->getActions () )) {
			foreach ( $this->contextResult->getActions () as $action )
			{
				if (!isset($this->actionLists[$action->getType()]))
					$this->actionLists[$action->getType()] = array($action);
				else
					array_push($this->actionLists[$action->getType()], $action);
			}
		}
	}
	
	protected function validateScheduling()
	{
		if (!$this->entry->isScheduledNow() && !$this->isVsAdmin())
		{
			VExternalErrors::dieError(VExternalErrors::NOT_SCHEDULED_NOW);
		}
	}
	
	protected function validateVs()
	{
		if ($this->vsStr)
		{
			try
			{
				// todo need to check if partner is within a partner group
				$vs = vSessionUtils::crackVs($this->vsStr);
				// if entry is "display_in_search=2" validate partner ID from the VS
				// => meaning it will alwasy pass on partner_id
				if($this->entry->getDisplayInSearch() != mySearchUtils::DISPLAY_IN_SEARCH_VIDIUN_NETWORK)
				{
					$valid = $vs->isValidForPartner($this->entry->getPartnerId());
				}
				else
				{
					$valid = $vs->isValidForPartner($vs->partner_id);
				}
				if ($valid === vs::EXPIRED)
				{
					VExternalErrors::dieError(VExternalErrors::VS_EXPIRED, "This URL is expired");
				}
				else if ($valid === vs::INVALID_PARTNER)
				{
					if (!in_array($vs->partner_id, self::$trustedPartnerIds) && $this->hasRules())
					{
						// TODO - for now if the entry doesnt have restrictions any way disregard a partner group check
						VExternalErrors::dieError(VExternalErrors::INVALID_PARTNER, "Invalid session [".$valid."]");
					}
				}
				else if ($valid === vs::EXCEEDED_RESTRICTED_IP)
				{
					VExternalErrors::dieError(VExternalErrors::EXCEEDED_RESTRICTED_IP);
				}
				else if ($valid !== vs::OK)
				{
					VExternalErrors::dieError(VExternalErrors::INVALID_VS, "Invalid session [".$valid."]");
				}
				
				if ($vs->partner_id != $this->entry->getPartnerId() && $vs->partner_id != Partner::BATCH_PARTNER_ID)
				{
					return;
				}
					
				$this->vs = $vs;	
			}
			catch(Exception $ex)
			{
				VExternalErrors::dieError(VExternalErrors::INVALID_VS_SRT);
			}
		}
	}
	
	public function isVsAdmin()
	{
		 return ($this->vs && $this->vs->isAdmin());
	}
	
	public function isVsWidget()
	{
		 return (!$this->vsStr || ($this->vs && $this->vs->isWidgetSession()));
	}
	
	/**
	 * Indicates that the VS user is the owner of the entry
	 * @return bool
	 */
	protected function isVsUserOwnsEntry()
	{
		return (!$this->isVsWidget() && $this->vs && $this->entry && $this->entry->getVuserId() == $this->vs->getVuserId());
	}
	
	
	/**
	 * Indicates that the entry is not approved
	 * @return bool
	 */
	public function isEntryInModeration()
	{
		$entry = $this->entry;
		$moderationStatus = $entry->getModerationStatus();
		$invalidModerationStatuses = array(
			entry::ENTRY_MODERATION_STATUS_PENDING_MODERATION, 
			entry::ENTRY_MODERATION_STATUS_REJECTED
		);
		
		if(!in_array($moderationStatus, $invalidModerationStatuses))
			return false;
			
		if($this->isVsAdmin() || $this->isVsUserOwnsEntry())
			return false;
			
		return true;
	}
	
	/**
	 * Access control need to be checked every request and therefore request can't be cached
	 * @return boolean
	 */
	public function shouldDisableCache()
	{
		return $this->disableCache;
	}
	
	private function getAccessControlScope()
	{
		$accessControlScope = new accessControlScope();
		if ($this->referrer)
			$accessControlScope->setReferrer($this->referrer);
		$accessControlScope->setVs($this->vs);
		$accessControlScope->setEntryId($this->entry->getId());
		$accessControlScope->setContexts($this->contexts);
		$accessControlScope->setHashes($this->hashes);
		$accessControlScope->setAsset($this->asset);
		
		return $accessControlScope;
	}

	public function setHashes (array $hashes)
	{
		$this->hashes = $hashes;
	}
	public function getContextResult()
	{
		return $this->contextResult;
	}
	
	public function validateForServe($flavorParamsId)
	{
		if (!$this->isFlavorParamsAllowed($flavorParamsId))
		{
			VExternalErrors::dieError(VExternalErrors::ACCESS_CONTROL_RESTRICTED);
		}
		if ($this->shouldBlock())
		{
			VExternalErrors::dieError(VExternalErrors::ACCESS_CONTROL_RESTRICTED);
		}
	}

	public function getScope()
	{
		return $this->scope;
	}
}