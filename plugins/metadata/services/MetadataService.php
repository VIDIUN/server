<?php
/**
 * Metadata service
 *
 * @service metadata
 * @package plugins.metadata
 * @subpackage api.services
 */
class MetadataService extends VidiunBaseService
{

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		$this->applyPartnerFilterForClass('MetadataProfile');
		if ($actionName != 'list')
			$this->applyPartnerFilterForClass('Metadata');
		
		if(!MetadataPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, MetadataPlugin::PLUGIN_NAME);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerGroup()
	 */
	protected function partnerGroup($peer = null)
	{
	    if(in_array($this->actionName, array('get', 'list')) && $peer == 'Metadata'){
	        return $this->partnerGroup . ',0';
	    }
	    elseif (in_array($this->actionName, array('add', 'get', 'list', 'update')) && $peer == 'MetadataProfile'){
	        return $this->partnerGroup . ',0';
	    }
	
	    return $this->partnerGroup;
	}
	
	protected function vidiunNetworkAllowed($actionName)
	{
		if ($actionName == 'list')
		{
			$this->partnerGroup .= ',0';
			return true;
		}
			
		return parent::vidiunNetworkAllowed($actionName);
	}

	/**
	 * Allows you to add a metadata object and metadata content associated with Vidiun object
	 * 
	 * @action add
	 * @param int $metadataProfileId
	 * @param VidiunMetadataObjectType $objectType
	 * @param string $objectId
	 * @param string $xmlData XML metadata
	 * @return VidiunMetadata
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 * @throws MetadataErrors::INCOMPATIBLE_METADATA_PROFILE_OBJECT_TYPE
	 * @throws MetadataErrors::METADATA_ALREADY_EXISTS
	 * @throws MetadataErrors::INVALID_METADATA_DATA
	 */
	function addAction($metadataProfileId, $objectType, $objectId, $xmlData)
	{
	    $metadataProfile = MetadataProfilePeer::retrieveByPK($metadataProfileId);
		if(!$metadataProfile)
		    throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $metadataProfileId);
		    
		if($metadataProfile->getObjectType() != vPluginableEnumsManager::apiToCore('MetadataObjectType', $objectType))
		    throw new VidiunAPIException(MetadataErrors::INCOMPATIBLE_METADATA_PROFILE_OBJECT_TYPE, $metadataProfile->getObjectType() , $objectType);
		
		if($objectType == VidiunMetadataObjectType::USER)
		{
			$vuser = vuserPeer::createVuserForPartner($this->getPartnerId(), $objectId);
			if($vuser)				
				$objectId = $vuser->getId();
		}
		
		$objectType = vPluginableEnumsManager::apiToCore('MetadataObjectType', $objectType);

		$limitEntry = $this->getVs()->getLimitEntry();
		if ($limitEntry) {
			$peer = vMetadataManager::getObjectPeer($objectType);
			if ($peer) {
				$entry = $peer->getEntry($objectId);
				if (!$entry || $entry->getId() != $limitEntry) {
					throw new VidiunAPIException(MetadataErrors::METADATA_NO_PERMISSION_ON_ENTRY, $objectId);
				}
			}
		}
		
		$this->validateObjectId($objectId, $objectType);
		$check = MetadataPeer::retrieveByObject($metadataProfileId, $objectType, $objectId);
		if($check)
			throw new VidiunAPIException(MetadataErrors::METADATA_ALREADY_EXISTS, $check->getId());
			
		// if a metadata xslt is defined on the metadata profile - transform the given metadata
		$xmlDataTransformed = $this->transformMetadata($metadataProfileId, $xmlData);
	    if($xmlDataTransformed)
            $xmlData = $xmlDataTransformed;
		
		$errorMessage = '';
		if(!vMetadataManager::validateMetadata($metadataProfileId, $xmlData, $errorMessage))
			throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_DATA, $errorMessage);
		
		$dbMetadata = $this->addMetadata($metadataProfileId, $objectType, $objectId);
		
		$key = $dbMetadata->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
		vFileSyncUtils::file_put_contents($key, $xmlData);
		
		$this->deleteOldVersions($dbMetadata);
		vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbMetadata));
				
		$metadata = new VidiunMetadata();
		$metadata->fromObject($dbMetadata, $this->getResponseProfile());
		
		return $metadata;
	}

	
	/**
	 * Adds a metadata object associated with Vidiun object
	 * 
	 * @param int $metadataProfileId
	 * @param VidiunMetadataObjectType $objectType
	 * @param string $objectId
	 * @return Metadata
	 * @throws MetadataErrors::METADATA_ALREADY_EXISTS
	 * @throws MetadataErrors::INVALID_METADATA_PROFILE
	 * @throws MetadataErrors::INVALID_METADATA_PROFILE_TYPE
	 * @throws MetadataErrors::INVALID_METADATA_OBJECT
	 */
	protected function addMetadata($metadataProfileId, $objectType, $objectId)
	{
		$objectType = vPluginableEnumsManager::apiToCore('MetadataObjectType', $objectType);
		
		$check = MetadataPeer::retrieveByObject($metadataProfileId, $objectType, $objectId);
		if($check)
			throw new VidiunAPIException(MetadataErrors::METADATA_ALREADY_EXISTS, $check->getId());
			
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK($metadataProfileId);
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_PROFILE, $metadataProfileId);
			
		if($dbMetadataProfile->getObjectType() != $objectType)
			throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_PROFILE_TYPE, $dbMetadataProfile->getObjectType());
		
		$dbMetadata = new Metadata();
		
		$dbMetadata->setPartnerId($this->getPartnerId());
		$dbMetadata->setMetadataProfileId($metadataProfileId);
		$dbMetadata->setMetadataProfileVersion($dbMetadataProfile->getVersion());
		$dbMetadata->setObjectType($objectType);
		$dbMetadata->setObjectId($objectId);
		$dbMetadata->setStatus(VidiunMetadataStatus::VALID);
		$dbMetadata->setLikeNew(true);

		// dynamic objects are metadata only, skip validating object id
		if ($objectType != VidiunMetadataObjectType::DYNAMIC_OBJECT)
		{
			// validate object exists
			$object = vMetadataManager::getObjectFromPeer($dbMetadata);
			if (!$object)
				throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_OBJECT, $objectId);
		}

		$dbMetadata->save();
		
		$this->deleteOldVersions($dbMetadata);
		
		return $dbMetadata;
	}

	/**
	 * Allows you to add a metadata object and metadata file associated with Vidiun object
	 * 
	 * @action addFromFile
	 * @param int $metadataProfileId
	 * @param VidiunMetadataObjectType $objectType
	 * @param string $objectId
	 * @param file $xmlFile XML metadata
	 * @return VidiunMetadata
	 * @throws MetadataErrors::METADATA_ALREADY_EXISTS
	 * @throws MetadataErrors::METADATA_FILE_NOT_FOUND
	 * @throws MetadataErrors::INVALID_METADATA_DATA
	 */
	function addFromFileAction($metadataProfileId, $objectType, $objectId, $xmlFile)
	{
		$filePath = $xmlFile['tmp_name'];
		if(!file_exists($filePath))
			throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $xmlFile['name']);
		
		$xmlData = file_get_contents($filePath);
		@unlink($filePath);
		return $this->addAction($metadataProfileId, $objectType, $objectId, $xmlData);
	}
	
	
	/**
	 * Allows you to add a metadata XML data from remote URL
	 * 

	 * @action addFromUrl
	 * @param int $metadataProfileId
	 * @param VidiunMetadataObjectType $objectType
	 * @param string $objectId
	 * @param string $url XML metadata remote URL

	 * @return VidiunMetadata
	 */
	function addFromUrlAction($metadataProfileId, $objectType, $objectId, $url)
	{
		$xmlData = file_get_contents($url);
		return $this->addAction($metadataProfileId, $objectType, $objectId, $xmlData);
	}
	
	
	/**
	 * Allows you to add a metadata XML data from remote URL.
	 * Enables different permissions than addFromUrl action.
	 * 
	 * @action addFromBulk
	 * @param int $metadataProfileId
	 * @param VidiunMetadataObjectType $objectType
	 * @param string $objectId
	 * @param string $url XML metadata remote URL
	 * @return VidiunMetadata
	 */
	function addFromBulkAction($metadataProfileId, $objectType, $objectId, $url)
	{
		$this->addFromUrlAction($metadataProfileId, $objectType, $objectId, $url);
	}

	
	/**
	 * Retrieve a metadata object by id
	 * 
	 * @action get
	 * @param int $id 
	 * @return VidiunMetadata
	 * @throws MetadataErrors::METADATA_NOT_FOUND
	 */		
	function getAction($id)
	{
		$dbMetadata = MetadataPeer::retrieveByPK( $id );
		
		if(!$dbMetadata)
			throw new VidiunAPIException(MetadataErrors::METADATA_NOT_FOUND, $id);
			
		$metadata = new VidiunMetadata();
		$metadata->fromObject($dbMetadata, $this->getResponseProfile());
		
		return $metadata;
	}
	
	/**
	 * Update an existing metadata object with new XML content
	 * 
	 * @action update
	 * @param int $id 
	 * @param string $xmlData XML metadata
	 * @param int $version Enable update only if the metadata object version did not change by other process
	 * @return VidiunMetadata
	 * @throws MetadataErrors::METADATA_NOT_FOUND
	 * @throws MetadataErrors::INVALID_METADATA_DATA
	 * @throws MetadataErrors::INVALID_METADATA_VERSION
	 * @throws MetadataErrors::XSLT_VALIDATION_ERROR
	 */	
	function updateAction ($id, $xmlData = null, $version = null)
	{
		return vLock::runLocked("metadata_update_xsl_{$id}", array($this, 'updateImpl'), array($id, $xmlData, $version));
	}

	function updateImpl($id, $xmlData = null, $version = null)
	{
		$dbMetadata = MetadataPeer::retrieveByPK($id);
		if(!$dbMetadata)
			throw new VidiunAPIException(MetadataErrors::METADATA_NOT_FOUND, $id);
			
		if($version && $dbMetadata->getVersion() != $version)
			throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_VERSION, $dbMetadata->getVersion());
		
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK($dbMetadata->getMetadataProfileId());
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_PROFILE, $dbMetadata->getMetadataProfileId());
		
		$this->validateObjectId($dbMetadata->getObjectId(), $dbMetadata->getObjectType());
		if($xmlData)
		{
			// if a metadata xslt is defined on the metadata profile - transform the given metadata
		    $xmlDataTransformed = $this->transformMetadata($dbMetadata->getMetadataProfileId(), $xmlData);
		    if ($xmlDataTransformed)
	            $xmlData = $xmlDataTransformed;
			
			$errorMessage = '';
			if(!vMetadataManager::validateMetadata($dbMetadata->getMetadataProfileId(), $xmlData, $errorMessage))
			{
				// if metadata profile is transforming, and metadata profile version is not the latest, try to validate againts previous version
				if($dbMetadataProfile->getStatus() != MetadataProfile::STATUS_TRANSFORMING || $dbMetadata->getMetadataProfileVersion() >= $dbMetadataProfile->getVersion())
					throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_DATA, $errorMessage);
					
				// validates against previous version
				$errorMessagePrevVersion = '';
				if(!vMetadataManager::validateMetadata($dbMetadata->getMetadataProfileId(), $xmlData, $errorMessagePrevVersion, true))
				{
					VidiunLog::err("Failed to validate metadata object [$id] against metadata profile previous version [" . $dbMetadata->getMetadataProfileVersion() . "] error: $errorMessagePrevVersion");

					// throw the error with the original error message
					throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_DATA, $errorMessage);
				}
			}
			else
			{
				$dbMetadata->setMetadataProfileVersion($dbMetadataProfile->getVersion());
			}
			
			$key = $dbMetadata->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
			if (!vFileSyncUtils::compareContent($key, $xmlData))
			{
				MetadataPlugin::updateMetadataFileSync($dbMetadata, $xmlData);
			}
			else 
			{
				VidiunLog::info("XML data MD5 matches current filesync content MD5. Update is not necessary.");
				//adding this save() in order to save the metadata profile version field in case there are no diffrences
				$dbMetadata->save();
			}
		}
		
		$metadata = new VidiunMetadata();
		$metadata->fromObject($dbMetadata, $this->getResponseProfile());
			
		return $metadata;
	}	
	
	
	/**
	 * Update an existing metadata object with new XML file
	 * 
	 * @action updateFromFile
	 * @param int $id 
	 * @param file $xmlFile XML metadata
	 * @return VidiunMetadata
	 * @throws MetadataErrors::METADATA_NOT_FOUND
	 * @throws MetadataErrors::METADATA_FILE_NOT_FOUND
	 * @throws MetadataErrors::INVALID_METADATA_DATA
	 */	
	function updateFromFileAction($id, $xmlFile = null)
	{
		$filePath = $xmlFile['tmp_name'];
		if(!file_exists($filePath))
			throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $xmlFile['name']);
		
		$xmlData = file_get_contents($filePath);
		@unlink($filePath);
		return $this->updateAction($id, $xmlData);
	}		
	
	/**
	 * List metadata objects by filter and pager
	 * 
	 * @action list
	 * @param VidiunMetadataFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunMetadataListResponse
	 */
	function listAction(VidiunMetadataFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunMetadataFilter();
			
		if (! $pager)
			$pager = new VidiunFilterPager();
		
		$applyPartnerFilter = true;
		if($filter->metadataObjectTypeEqual == MetadataObjectType::ENTRY)
		{
			$objectIds = $filter->getObjectIdsFiltered();
			if(!empty($objectIds))
			{
				$objectIds = entryPeer::filterEntriesByPartnerOrVidiunNetwork($objectIds, vCurrentContext::getCurrentPartnerId());
				
				if(!count($objectIds))
					return $filter->getEmptyListResponse();
				
				if(count($objectIds))
					$applyPartnerFilter = false;
			}
		}
		
		if($applyPartnerFilter)
			$this->applyPartnerFilterForClass('Metadata');
		
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}
	
	/**
	 * @param Metadata $metadata
	 * @return int affected records
	 */
	protected function deleteOldVersions(Metadata $metadata)
	{
		$c = new Criteria();
		$c->add(MetadataPeer::OBJECT_ID, $metadata->getObjectId());
		$c->add(MetadataPeer::OBJECT_TYPE, $metadata->getObjectType());
		$c->add(MetadataPeer::METADATA_PROFILE_ID, $metadata->getMetadataProfileId());
		$c->add(MetadataPeer::METADATA_PROFILE_VERSION, $metadata->getMetadataProfileVersion(), Criteria::LESS_THAN);
		$c->add(MetadataPeer::STATUS, VidiunMetadataStatus::DELETED, Criteria::NOT_EQUAL);
		
		MetadataPeer::setUseCriteriaFilter(false);
		$metadatas = MetadataPeer::doSelect($c);
		MetadataPeer::setUseCriteriaFilter(true);
		
		foreach($metadatas as $metadata)
			vEventsManager::raiseEvent(new vObjectDeletedEvent($metadata));
		
		$update = new Criteria();
		$update->add(MetadataPeer::STATUS, VidiunMetadataStatus::DELETED);
			
		$con = Propel::getConnection(MetadataPeer::DATABASE_NAME);
		$count = BasePeer::doUpdate($c, $update, $con);
		
		return $count;
	}	
	
	
	/**
	 * Delete an existing metadata
	 * 
	 * @action delete
	 * @param int $id
	 * @throws MetadataErrors::METADATA_NOT_FOUND
	 */		
	function deleteAction($id)
	{
		$dbMetadata = MetadataPeer::retrieveByPK($id);
		if(!$dbMetadata)
			throw new VidiunAPIException(MetadataErrors::METADATA_NOT_FOUND, $id);
		
		$this->validateObjectId($dbMetadata->getObjectId(), $dbMetadata->getObjectType());
		
		$dbMetadata->setStatus(VidiunMetadataStatus::DELETED);
		$dbMetadata->save();
		vEventsManager::raiseEvent(new vObjectDataChangedEvent($dbMetadata));
	}

	
	/**
	 * Mark existing metadata as invalid
	 * Used by batch metadata transform
	 * 
	 * @action invalidate
	 * @param int $id
	 * @param int $version Enable update only if the metadata object version did not change by other process
	 * @throws MetadataErrors::METADATA_NOT_FOUND
	 * @throws MetadataErrors::INVALID_METADATA_VERSION
	 */		
	function invalidateAction($id, $version = null)
	{
		$dbMetadata = MetadataPeer::retrieveByPK($id);
		if(!$dbMetadata)
			throw new VidiunAPIException(MetadataErrors::METADATA_NOT_FOUND, $id);

		if($version && $dbMetadata->getVersion() != $version)
			throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_VERSION, $dbMetadata->getVersion());

		$dbMetadata->setStatus(VidiunMetadataStatus::INVALID);
		$dbMetadata->save();
	}

	/**
	 * Index metadata by id, will also index the related object
	 *
	 * @action index
	 * @param string $id
	 * @param bool $shouldUpdate
	 * @return int
	 */
	function indexAction($id, $shouldUpdate)
	{
		if(vEntitlementUtils::getEntitlementEnforcement())
			throw new VidiunAPIException(VidiunErrors::CANNOT_INDEX_OBJECT_WHEN_ENTITLEMENT_IS_ENABLE);

		$dbMetadata = MetadataPeer::retrieveByPK($id);
		if(!$dbMetadata)
			throw new VidiunAPIException(MetadataErrors::METADATA_NOT_FOUND, $id);

		$dbMetadata->indexToSearchIndex();
		$relatedObject = vMetadataManager::getObjectFromPeer($dbMetadata);
		if($relatedObject && $relatedObject instanceof IIndexable)
			$relatedObject->indexToSearchIndex();

		return $dbMetadata->getId();

	}

	/**
	 * Serves metadata XML file
	 *  
	 * @action serve
	 * @param int $id
	 * @return file
	 *  
	 * @throws MetadataErrors::METADATA_NOT_FOUND
	 * @throws VidiunErrors::FILE_DOESNT_EXIST
	 */
	public function serveAction($id)
	{
		$dbMetadata = MetadataPeer::retrieveByPK( $id );
		
		if(!$dbMetadata)
			throw new VidiunAPIException(MetadataErrors::METADATA_NOT_FOUND, $id);
		
		$fileName = $dbMetadata->getObjectId() . '.xml';
		$fileSubType = Metadata::FILE_SYNC_METADATA_DATA;
		
		return $this->serveFile($dbMetadata, $fileSubType, $fileName);
	}
		
	
	private function transformMetadata($metadataProfileId, $xmlData)
	{
        $result = null;
	    $metadataProfile = MetadataProfilePeer::retrieveByPK($metadataProfileId); 
	    if (!$metadataProfile) {
	        VidiunLog::err('Cannot find metadata profile id ['.$metadataProfileId.']');
	        return null;
	    }
	    
	    $metadataXsltKey = $metadataProfile->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_XSLT);
	    if (!vFileSyncUtils::file_exists($metadataXsltKey, true))
	    	return null;
	    
	    $xsltString = vFileSyncUtils::file_get_contents($metadataXsltKey, true, false);
	    if (!$xsltString)
	    	return null;
	    
        $xsltParams = array(
        	XsltParameterName::VIDIUN_CURRENT_TIMESTAMP => time(),
        );
        
        $xsltErrors = array();
        $xmlDataTransformed = vXml::transformXmlUsingXslt($xmlData, $xsltString, $xsltParams, $xsltErrors);
        
        if (!empty($xsltErrors))
        {
        	throw new VidiunAPIException(MetadataErrors::XSLT_VALIDATION_ERROR, implode(',', $xsltErrors));
        }
        
        if ($xmlDataTransformed)
            return $xmlDataTransformed;
        
        VidiunLog::err('Failed XML [$xmlData] transformation for metadata with XSL [$xsltString]');
	    return null;
	}
	
	/**
	 * Action transforms current metadata object XML using a provided XSL.
	 * @action updateFromXSL
	 * 
	 * @param int $id
	 * @param file $xslFile
	 * @return VidiunMetadata
	 * @throws MetadataErrors::XSLT_VALIDATION_ERROR
	 * @throws MetadataErrors::METADATA_FILE_NOT_FOUND
	 * @throws MetadataErrors::METADATA_NOT_FOUND
	 */
	public function updateFromXSLAction ($id, $xslFile)
	{
		$xslFilePath = $xslFile['tmp_name'];
		if(!file_exists($xslFilePath))
			throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $xslFile['name']);

		$xslData = file_get_contents($xslFilePath);
		@unlink($xslFilePath);

		return vLock::runLocked("metadata_update_xsl_{$id}", array($this, 'updateFromXSLImpl'), array($id, $xslData));
	}

	public function updateFromXSLImpl ($id, $xslData)
	{
		$dbMetadataObject = MetadataPeer::retrieveByPK($id);
		if (!$dbMetadataObject)
			throw new VidiunAPIException(MetadataErrors::METADATA_NOT_FOUND);
		
		$this->validateObjectId($dbMetadataObject->getObjectId(), $dbMetadataObject->getObjectType());
		$dbMetadataObjectFileSyncKey = $dbMetadataObject->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);

		$xsltErrors = array();
		$transformMetadataObjectData = vXml::transformXmlUsingXslt(vFileSyncUtils::file_get_contents($dbMetadataObjectFileSyncKey), $xslData, array(), $xsltErrors);

		if ( count($xsltErrors))
		{
			throw new VidiunAPIException(MetadataErrors::XSLT_VALIDATION_ERROR, implode(',', $xsltErrors));
		}

		return $this->updateImpl($id, $transformMetadataObjectData);
	}
	
	private function validateObjectId($objectId, $objectType)
	{
		$metadataObjectClassName = vMetadataManager::getObjectTypeName($objectType);
		$this->applyPartnerFilterForClass($metadataObjectClassName);
		$objectPeer = vMetadataManager::getObjectPeer($objectType);
		
		if(!$objectPeer && !vCurrentContext::$is_admin_session)
		{
			VidiunLog::debug("Failed to validate metadata object access for dynamic object id [$objectId]");
		}
		
		if($objectPeer && !$objectPeer::validateMetadataObjectAccess($objectId))
		{
			//VidiunLog::debug("Failed to validate metadata object access for object id [$objectId] using peer [" .get_class($objectPeer) . "]");
			throw new VidiunAPIException(MetadataErrors::METADATA_OBJECT_ID_NOT_FOUND, $objectId);
		}
	}
}
