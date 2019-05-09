<?php

/**
 * Subclass for representing a row from the 'storage_profile' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class StorageProfile extends BaseStorageProfile implements IBaseObject
{
	const STORAGE_SERVE_PRIORITY_VIDIUN_ONLY = 1;
	const STORAGE_SERVE_PRIORITY_VIDIUN_FIRST = 2;
	const STORAGE_SERVE_PRIORITY_EXTERNAL_FIRST = 3;
	const STORAGE_SERVE_PRIORITY_EXTERNAL_ONLY = 4;
	
	const STORAGE_STATUS_DISABLED = 1;
	const STORAGE_STATUS_AUTOMATIC = 2;
	const STORAGE_STATUS_MANUAL = 3;
	
	const STORAGE_VIDIUN_DC = 0;
	const STORAGE_PROTOCOL_FTP = 1;
	const STORAGE_PROTOCOL_SCP = 2;
	const STORAGE_PROTOCOL_SFTP = 3;
	const STORAGE_PROTOCOL_S3 = 6;
	
	const STORAGE_DEFAULT_VIDIUN_PATH_MANAGER = 'vPathManager';
	const STORAGE_DEFAULT_EXTERNAL_PATH_MANAGER = 'vExternalPathManager';
	
	const CUSTOM_DATA_DELIVERY_IDS = 'delivery_profile_ids';
	const CUSTOM_DATA_PATH_MANAGER_PARAMS = 'path_manager_params';
	const CUSTOM_DATA_PATH_FORMAT = 'path_format';
	const CUSTOM_DATA_READY_BEHAVIOR = 'ready_behavior';
	const CUSTOM_DATA_RULES = 'rules';
	const CUSTOM_DATA_CREATE_FILE_LINK ='create_file_link';
	const CUSTOM_DATA_SHOULD_EXPORT_THUMBS ='should_export_thumbs';

	/**
	 * @var vStorageProfileScope
	 */
	protected $scope;
	
	/**
	 * @return vPathManager
	 */
	
	public function getPathManager()
	{
		$class = $this->getPathManagerClass();
		if(!$class || !strlen(trim($class)) || !class_exists($class))
		{
			if($this->getProtocol() == self::STORAGE_VIDIUN_DC)
			{
				$class = self::STORAGE_DEFAULT_VIDIUN_PATH_MANAGER;
			}
			else
			{
				$class = self::STORAGE_DEFAULT_EXTERNAL_PATH_MANAGER;
			}
		}
			
		return new $class();
	}
	
	/* ---------------------------------- TODO - temp solution -----------------------------------------*/
	// remove after event manager implemented
	
	const STORAGE_TEMP_TRIGGER_MODERATION_APPROVED = 2;
	const STORAGE_TEMP_TRIGGER_FLAVOR_READY = 3;
		
	public function getTrigger() { return $this->getFromCustomData("trigger", null, self::STORAGE_TEMP_TRIGGER_FLAVOR_READY); }
	public function setTrigger( $v ) { $this->putInCustomData("trigger", (int)$v); }
	
	//external path format
	public function setPathFormat($v) { $this->putInCustomData(self::CUSTOM_DATA_PATH_FORMAT, $v);}
	public function getPathFormat()
	{
		$params = $this->getPathManagerParams();
		if (isset($params[self::CUSTOM_DATA_PATH_FORMAT]))
		{
		    return $params[self::CUSTOM_DATA_PATH_FORMAT];
		}
		
		return $this->getFromCustomData(self::CUSTOM_DATA_PATH_FORMAT);
	}
	
	/**
	 * 
	 * Get the allow_auto_delete parameter value
	 */
	public function getAllowAutoDelete() 
	{ 
		return (bool)$this->getFromCustomData("allow_auto_delete", null, false); 
	} // if not set to true explicitly, default will be false
	

	public function setAllowAutoDelete( $v )
	{ 
		$this->putInCustomData("allow_auto_delete", (bool)$v); 
	}
	
    public function setRules ($v)
	{
	    $this->putInCustomData(self::CUSTOM_DATA_RULES, $v);
	}
	
	public function getRules ()
	{
	    return $this->getFromCustomData(self::CUSTOM_DATA_RULES);
	}
	
	public function getCreateFileLink ()
	{
	    return $this->getFromCustomData(self::CUSTOM_DATA_CREATE_FILE_LINK);
	}
	
    public function setCreateFileLink ($v)
	{
	    $this->putInCustomData(self::CUSTOM_DATA_CREATE_FILE_LINK, $v);
	}
	
	
	/* Delivery Settings */
	
	public function setDeliveryProfileIds($params)
	{
		$this->putInCustomData(self::CUSTOM_DATA_DELIVERY_IDS, $params);
	}
	
	public function getDeliveryProfileIds()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_DELIVERY_IDS, null, array());
	}
	
	/* ---------------------------------- TODO - temp solution -----------------------------------------*/
	
	/* Path Manager Params */
	
    public function setPathManagerParams($params)
	{
	    $this->putInCustomData(self::CUSTOM_DATA_PATH_MANAGER_PARAMS, serialize($params));
	}
	
	public function getPathManagerParams()
	{
	    $params = $this->getFromCustomData(self::CUSTOM_DATA_PATH_MANAGER_PARAMS);
	    $params = unserialize($params);
	    if (!$params) {
	        return array();
	    }
	    return $params;
	}
	
	
    public function setReadyBehavior($readyBehavior)
	{
	    $this->putInCustomData(self::CUSTOM_DATA_READY_BEHAVIOR, $readyBehavior);
	}
	
	public function getReadyBehavior()
	{
	    // return NO_IMPACT as default when no other value is set
	    return $this->getFromCustomData(self::CUSTOM_DATA_READY_BEHAVIOR, null, StorageProfileReadyBehavior::NO_IMPACT);
	}
	
	/* Cache Invalidation */
	
	public function getCacheInvalidationKeys()
	{
		return array("storageProfile:id=".strtolower($this->getId()), "storageProfile:partnerId=".strtolower($this->getPartnerId()));
	}
	
	/**
	 * @param flavorAsset $flavorAsset
	 * @return boolean true if the given flavor asset is configured to be exported or false otherwise
	 */
	public function shouldExportFlavorAsset(asset $flavorAsset, $skipFlavorAssetStatusValidation = false)
	{
		if(!$skipFlavorAssetStatusValidation && !$flavorAsset->isLocalReadyStatus())
		{
			return false;
		}

		if ($flavorAsset instanceof thumbAsset)
			return $this->getShouldExportThumbs();

		if(!$this->isFlavorAssetConfiguredForExport($flavorAsset))
		{
			return false;
		}

		$scope = $this->getScope();
		
		$scopeEntryId = $flavorAsset->getEntryId();
		$entry = entryPeer::retrieveByPK($scopeEntryId);
		if($entry && $entry->getReplacedEntryId())
			$scopeEntryId = $entry->getReplacedEntryId();
		
		$scope->setEntryId($scopeEntryId);
		if(!$this->fulfillsRules($scope))
		{
			VidiunLog::log('Storage profile export rules are not fulfilled');
			return false;
		}
			
		return true;	    
	}
	
	public function shoudlExportFileSync(FileSyncKey $key)
	{
		if($this->isExported($key))
		{
			VidiunLog::info('Flavor was already exported');
			return false;
		}
		if(!$this->isValidFileSync($key))
		{
			VidiunLog::info('File sync is not valid for export');
			return false;
		}
		return true;	    
	}
	
	/**
	 * @return true if the profile's trigger fits a ready flavor asset for the given entry id
	 * @param string $entryId
	 */
	public function triggerFitsReadyAsset($entryId)
	{
	    if ($this->getTrigger() == StorageProfile::STORAGE_TEMP_TRIGGER_FLAVOR_READY) {
	        return true;
	    }
	    
	    if ($this->getTrigger() == StorageProfile::STORAGE_TEMP_TRIGGER_MODERATION_APPROVED) {
	        $entry = entryPeer::retrieveByPK($entryId);
	        if ($entry && $entry->getModerationStatus() == entry::ENTRY_MODERATION_STATUS_APPROVED) {
                return true;	            
	        }
	    }
	    return false;
	}
	
	
	public function isPendingExport(FileSyncKey $key)
	{
	    $c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		$c->addAnd(FileSyncPeer::DC, $this->getId(), Criteria::EQUAL);
		$fileSync = FileSyncPeer::doSelectOne($c);
		if (!$fileSync) {
		    return false;
		}
		return ($fileSync->getStatus() == FileSync::FILE_SYNC_STATUS_PENDING);
	}
	
	/**
	 * Validate if the entry should be exported to the remote storage according to the defined export rules
	 * 
	 * @param vStorageProfileScope $scope
	 */
	public function fulfillsRules(vStorageProfileScope $scope)
	{
		if(!PermissionPeer::isValidForPartner(PermissionName::FEATURE_REMOTE_STORAGE_RULE, $this->getPartnerId()))
			return true;
			
		if(!is_array($this->getRules()) || !count($this->getRules())) 
			return true;
			
		$context = null;
		if(!array_key_exists($this->getId(), vStorageExporter::$entryContextDataResult))
		{
			vStorageExporter::$entryContextDataResult[$this->getId()] = array();
		}
		
		if(array_key_exists($scope->getEntryId(), vStorageExporter::$entryContextDataResult[$this->getId()]))
		{
			$context = vStorageExporter::$entryContextDataResult[$this->getId()][$scope->getEntryId()];
		}
		else
		{	
			$context = new vContextDataResult();	
			foreach ($this->getRules() as $rule) 
			{
				/* @var $rule vRule */
				$rule->setScope($scope);
				$fulfilled = $rule->applyContext($context);
				
				if($fulfilled && $rule->getStopProcessing())
					break;
			}
			vStorageExporter::$entryContextDataResult[$this->getId()][$scope->getEntryId()] = $context;
		}
		
		foreach ($context->getActions() as $action) 
		{
			/* @var $action vRuleAction */
			if($action->getType() == RuleActionType::ADD_TO_STORAGE)
				return true;
		}
		
		return false;
	}
	
	/**
	 * Check if input key was already exported for this storage profile
	 * 
	 * @param FileSyncKey $key
	 */
	public function isExported(FileSyncKey $key)
	{
		$storageFileSync = vFileSyncUtils::getReadyPendingExternalFileSyncForKey($key, $this->getId());

		if($storageFileSync) // already exported or currently being exported
		{
			VidiunLog::log(__METHOD__ . " key [$key] already exported or being exported");
			return true;
		}
		else 
		{
			return false;
		}
	}
	
	/**
	 * Check if flavor asset id set for export on the storage profile
	 * 
	 * @param flavorAsset $flavorAsset
	 */
	protected function isFlavorAssetConfiguredForExport(asset $flavorAsset)
	{
		$configuredForExport = null;

	    // check if flavor params id is in the list to export
	    $flavorParamsIdsToExport = $this->getFlavorParamsIds();
	    VidiunLog::log(__METHOD__ . " flavorParamsIds [$flavorParamsIdsToExport]");
	    
	    if (is_null($flavorParamsIdsToExport) || strlen(trim($flavorParamsIdsToExport)) == 0)
	    {
	        // all flavor assets should be exported
	        $configuredForExport = true;
	    }
	    else
	    {
	        $flavorParamsIdsToExport = array_map('trim', explode(',', $flavorParamsIdsToExport));
	        if (in_array($flavorAsset->getFlavorParamsId(), $flavorParamsIdsToExport))
	        {
	            // flavor set to export
	            $configuredForExport = true;
	        }
	        else
	        {
	            // flavor not set to export
	            $configuredForExport = false;
	        }
	    }

	    return $configuredForExport;
	}
	
	public function isValidFileSync(FileSyncKey $key)
	{
		VidiunLog::log(__METHOD__ . " - key [$key], externalStorage id[" . $this->getId() . "]");
		
		list($vidiunFileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($key, false, false);
		if(!$vidiunFileSync) // no local copy to export from
		{
			VidiunLog::log(__METHOD__ . " key [$key] not found localy");
			return false;
		}
		
		VidiunLog::log(__METHOD__ . " validating file size [" . $vidiunFileSync->getFileSize() . "] is between min [" . $this->getMinFileSize() . "] and max [" . $this->getMaxFileSize() . "]");
		if($this->getMaxFileSize() && $vidiunFileSync->getFileSize() > $this->getMaxFileSize()) // too big
		{
			VidiunLog::log(__METHOD__ . " key [$key] file too big");
			return false;
		}
			
		if($this->getMinFileSize() && $vidiunFileSync->getFileSize() < $this->getMinFileSize()) // too small
		{
			VidiunLog::log(__METHOD__ . " key [$key] file too small");
			return false;
		}
			
		return true;
		
	}
	
	/**
	 * Get the storage profile scope
	 * 
	 * @return vStorageProfileScope
	 */
	public function &getScope()
	{
		if (!$this->scope)
		{
			$this->scope = new vStorageProfileScope();
			$this->scope->setStorageProfileId($this->getId());
		}
			
		return $this->scope;
	}
	
	/**
	 * Set the vStorageProfileScope, called internally only
	 * 
	 * @param $scope
	 */
	protected function setScope(vStorageProfileScope $scope)
	{
		$this->scope = $scope;
	}
	
	public function setPrivateKey($v) {
		$this->putInCustomData("privateKey", $v);
	}
	
	public function setPublicKey($v) {
		$this->putInCustomData("publicKey", $v);
	}
	
	public function setPassPhrase($v) {
		$this->putInCustomData("passPhrase", $v);
	}
	
	public function getPrivateKey() {
		return $this->getFromCustomData("privateKey");
	}
	
	public function getPublicKey() {
		return $this->getFromCustomData("publicKey");
	}
	
	public function getPassPhrase() {
		return $this->getFromCustomData("passPhrase");
	}

	public function getShouldExportThumbs ()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_SHOULD_EXPORT_THUMBS, null, false);
	}

	public function setShouldExportThumbs ($v)
	{
		$this->putInCustomData(self::CUSTOM_DATA_SHOULD_EXPORT_THUMBS, $v);
	}

}
