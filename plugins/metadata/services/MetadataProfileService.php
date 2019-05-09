<?php
/**
 * Metadata Profile service
 *
 * @service metadataProfile
 * @package plugins.metadata
 * @subpackage api.services
 */
class MetadataProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if($actionName !== "list")
			$this->applyPartnerFilterForClass('MetadataProfile');
		$this->applyPartnerFilterForClass('Metadata');
		$this->applyPartnerFilterForClass('entry');
		
		if(!MetadataPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, MetadataPlugin::PLUGIN_NAME);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerGroup()
	 */
	protected function partnerGroup($peer = null)
	{
	    if( $this->actionName == 'get') {
	        return $this->partnerGroup . ',0';
	    }
	    	
	    return $this->partnerGroup;
	}
		
	/**
	 * Allows you to add a metadata profile object and metadata profile content associated with Vidiun object type
	 *
	 * @action add
	 * @param VidiunMetadataProfile $metadataProfile
	 * @param string $xsdData XSD metadata definition
	 * @param string $viewsData UI views definition
	 * @return VidiunMetadataProfile
	 */
	function addAction(VidiunMetadataProfile $metadataProfile, $xsdData, $viewsData = null)
	{		
		if(!vMetadataProfileManager::validateXsdData($xsdData, $errorMessage)) {
		    throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_PROFILE_SCHEMA, $errorMessage);
		}

		$xsdData = html_entity_decode($xsdData);
		if($viewsData)
			$viewsData = html_entity_decode($viewsData);
		
		return $this->addMetadataProfile($metadataProfile, $xsdData, $viewsData);
	}
	
	/**
	 * Allows you to add a metadata profile object and metadata profile file associated with Vidiun object type
	 *
	 * @action addFromFile
	 * @param VidiunMetadataProfile $metadataProfile
	 * @param file $xsdFile XSD metadata definition
	 * @param file $viewsFile UI views definition
	 * @return VidiunMetadataProfile
	 * @throws MetadataErrors::METADATA_FILE_NOT_FOUND
	 */
	function addFromFileAction(VidiunMetadataProfile $metadataProfile, $xsdFile, $viewsFile = null)
	{
		$filePath = $xsdFile['tmp_name'];
		if(!file_exists($filePath) || !filesize($filePath)) {
		    throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $xsdFile['name']);
		}
		
		$xsdData = file_get_contents($filePath);
		if(!vMetadataProfileManager::validateXsdData($xsdData, $errorMessage)) {
		    throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_PROFILE_SCHEMA, $errorMessage);
		}
		
		$viewsData = null;
		if($viewsFile && $viewsFile['size'])
		{
		    $filePath = $viewsFile['tmp_name'];
		    if(!file_exists($filePath))
		        throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $viewsFile['name']);
		    
		    $viewsData = file_get_contents($filePath);
		}
		
		return $this->addMetadataProfile($metadataProfile, $xsdData, $viewsData);
	}
	
	private function addMetadataProfile(VidiunMetadataProfile $metadataProfile, $xsdData, $viewsData = null)
	{
	    // must be validatebefore checking available searchable fields count
	    $metadataProfile->validatePropertyNotNull('metadataObjectType');
	    vMetadataManager::validateProfileFields($this->getPartnerId(), $xsdData);
	    
	    $dbMetadataProfile = $metadataProfile->toInsertableObject();
	    $dbMetadataProfile->setXsdData($xsdData);
	    
	    if($viewsData)
	       $dbMetadataProfile->setViewesData($viewsData);
	    
	    $dbMetadataProfile->setStatus(VidiunMetadataProfileStatus::ACTIVE);
	    $dbMetadataProfile->setPartnerId($this->getPartnerId());
	    $dbMetadataProfile->save();
	    
	    $metadataProfile = new VidiunMetadataProfile();
	    $metadataProfile->fromObject($dbMetadataProfile, $this->getResponseProfile());
	    
	    return $metadataProfile;
	}
	
	/**
	 * Retrieve a metadata profile object by id
	 *
	 * @action get
	 * @param int $id
	 * @return VidiunMetadataProfile
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK( $id );
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);
			
		$metadataProfile = new VidiunMetadataProfile();
		$metadataProfile->fromObject($dbMetadataProfile, $this->getResponseProfile());
		
		return $metadataProfile;
	}
	
	/**
	 * Update an existing metadata object
	 *
	 * @action update
	 * @param int $id
	 * @param VidiunMetadataProfile $metadataProfile
	 * @param string $xsdData XSD metadata definition
	 * @param string $viewsData UI views definition
	 * @return VidiunMetadataProfile
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 * @throws MetadataErrors::METADATA_UNABLE_TO_TRANSFORM
	 * @throws MetadataErrors::METADATA_TRANSFORMING
	 * @throws MetadataErrors::METADATA_PROFILE_FILE_NOT_FOUND
	 */
	function updateAction($id, VidiunMetadataProfile $metadataProfile, $xsdData = null, $viewsData = null)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK($id);
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);
		
		if($dbMetadataProfile->getStatus() != MetadataProfile::STATUS_ACTIVE)
			throw new VidiunAPIException(MetadataErrors::METADATA_TRANSFORMING);

		if ($xsdData)
		{
		    if(!vMetadataProfileManager::validateXsdData($xsdData, $errorMessage)) {
		        throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_PROFILE_SCHEMA, $errorMessage);
		    }
		    
			vMetadataManager::validateProfileFields($this->getPartnerId(), $xsdData);
		}

		$dbMetadataProfile = $metadataProfile->toUpdatableObject($dbMetadataProfile);
		
		$key = $dbMetadataProfile->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_DEFINITION);
		
		$oldXsd = vFileSyncUtils::file_get_contents($key, true, false);
		if(!$oldXsd)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_FILE_NOT_FOUND, $id);

		if($xsdData)
		{
			$xsdData = html_entity_decode($xsdData);
			$dbMetadataProfile->setXsdData($xsdData);
		}
			
		if(!is_null($viewsData) && $viewsData != '')
		{
			$viewsData = html_entity_decode($viewsData);
			$dbMetadataProfile->setViewesData($viewsData);
		}
			
		if($xsdData)
		{		    
			try
			{
				vMetadataManager::diffMetadataProfile($dbMetadataProfile, $oldXsd, $xsdData);
			}
			catch(vXsdException $e)
			{
				throw new VidiunAPIException(MetadataErrors::METADATA_UNABLE_TO_TRANSFORM, $e->getMessage());
			}

			$dbMetadataProfile->save();

		}
		else if(!is_null($viewsData) && $viewsData != '')
		{
			$dbMetadataProfile->save();
		}
		
		$metadataProfile->fromObject($dbMetadataProfile, $this->getResponseProfile());
		return $metadataProfile;
	}
	
	
	/**
	 * List metadata profile objects by filter and pager
	 *
	 * @action list
	 * @param VidiunMetadataProfileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunMetadataProfileListResponse
	 */
	function listAction(VidiunMetadataProfileFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunMetadataProfileFilter;
			
		$metadataProfileFilter = new MetadataProfileFilter();
		$filter->toObject($metadataProfileFilter);
		
		if(isset($filter->systemNameEqual) || isset($filter->systemNameIn) || isset($filter->idEqual)) {
			$this->partnerGroup .= ",0";
		}
		$this->applyPartnerFilterForClass('MetadataProfile');
		
		$c = new Criteria();
		$metadataProfileFilter->attachToCriteria($c);
		$count = MetadataProfilePeer::doCount($c);
		
		if (! $pager)
			$pager = new VidiunFilterPager ();

		$pager->attachToCriteria ( $c );
		$list = MetadataProfilePeer::doSelect($c);
		
		$response = new VidiunMetadataProfileListResponse();
		$response->objects = VidiunMetadataProfileArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
		
		return $response;
	}
	
	/**
	 * List metadata profile fields by metadata profile id
	 *
	 * @action listFields
	 * @param int $metadataProfileId
	 * @return VidiunMetadataProfileFieldListResponse
	 */
	function listFieldsAction($metadataProfileId)
	{
		$dbFields = MetadataProfileFieldPeer::retrieveActiveByMetadataProfileId($metadataProfileId);
		
		$response = new VidiunMetadataProfileFieldListResponse();
		$response->objects = VidiunMetadataProfileFieldArray::fromDbArray($dbFields, $this->getResponseProfile());
		$response->totalCount = count($dbFields);
		
		return $response;
	}
	
	/**
	 * Delete an existing metadata profile
	 *
	 * @action delete
	 * @param int $id
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 */
	function deleteAction($id)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK($id);
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);

		// if this profile is a dynamic object, check for references in other profiles
		if ($dbMetadataProfile->getObjectType() == MetadataObjectType::DYNAMIC_OBJECT)
		{
			$referencedFields = MetadataProfileFieldPeer::retrieveByPartnerAndRelatedMetadataProfileId(
				vCurrentContext::getCurrentPartnerId(),
				$dbMetadataProfile->getId());
			if (count($referencedFields))
			{
				/** @var MetadataProfileField $referencedField */
				$referencedField = $referencedFields[0];
				throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_REFERENCE_EXISTS, $referencedField->getMetadataProfileId(), $referencedField->getKey());
			}
		}

		$dbMetadataProfile->setStatus(VidiunMetadataProfileStatus::DEPRECATED);
		$dbMetadataProfile->save();
		
		$c = new Criteria();
		$c->add(MetadataProfileFieldPeer::METADATA_PROFILE_ID, $id);
		$c->add(MetadataProfileFieldPeer::STATUS, MetadataProfileField::STATUS_DEPRECATED, Criteria::NOT_EQUAL);
		$MetadataProfileFields = MetadataProfileFieldPeer::doSelect($c);
		
		foreach($MetadataProfileFields as $MetadataProfileField)
		{
			$MetadataProfileField->setStatus(MetadataProfileField::STATUS_DEPRECATED);
			$MetadataProfileField->save();
		}
		
		$c = new Criteria();
		$c->add(MetadataPeer::METADATA_PROFILE_ID, $id);
		$c->add(MetadataPeer::STATUS, VidiunMetadataStatus::DELETED, Criteria::NOT_EQUAL);
	
		$peer = null;
		MetadataPeer::setUseCriteriaFilter(false);
		$metadatas = MetadataPeer::doSelect($c);
		
		foreach($metadatas as $metadata)
			vEventsManager::raiseEvent(new vObjectDeletedEvent($metadata));
		
		$update = new Criteria();
		$update->add(MetadataPeer::STATUS, VidiunMetadataStatus::DELETED);
			
		$con = Propel::getConnection(MetadataPeer::DATABASE_NAME);
		BasePeer::doUpdate($c, $update, $con);
	}
	
	/**
	 * Update an existing metadata object definition file
	 *
	 * @action revert
	 * @param int $id
	 * @param int $toVersion
	 * @return VidiunMetadataProfile
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 * @throws MetadataErrors::METADATA_FILE_NOT_FOUND
	 * @throws MetadataErrors::METADATA_UNABLE_TO_TRANSFORM
	 */
	function revertAction($id, $toVersion)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK($id);
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);
	
		$oldKey = $dbMetadataProfile->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_DEFINITION, $toVersion);
		if(!vFileSyncUtils::fileSync_exists($oldKey))
			throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $oldKey);
		
		$dbMetadataProfile->incrementFileSyncVersion();
		$dbMetadataProfile->incrementVersion();
		$dbMetadataProfile->save();
		
		$key = $dbMetadataProfile->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_DEFINITION);
		vFileSyncUtils::createSyncFileLinkForKey($key, $oldKey);
		
		vMetadataManager::parseProfileSearchFields($this->getPartnerId(), $dbMetadataProfile);
		
		MetadataPeer::setUseCriteriaFilter(false);
		$metadatas = MetadataPeer::retrieveByProfile($id, $toVersion);
		foreach($metadatas as $metadata)
		{
			// validate object exists
			$object = vMetadataManager::getObjectFromPeer($metadata);
			if(!$object)
				continue;
				
			$metadata->incrementVersion();
			$oldKey = $metadata->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA, $toVersion);
			if(!vFileSyncUtils::fileSync_exists($oldKey))
				continue;
			
			$xml = vFileSyncUtils::file_get_contents($oldKey, true, false);
			if(!$xml)
				continue;
			
			$errorMessage = '';
			if(!vMetadataManager::validateMetadata($dbMetadataProfile->getId(), $xml, $errorMessage))
				continue;
			
			$metadata->setMetadataProfileVersion($dbMetadataProfile->getVersion());
			$metadata->setStatus(Metadata::STATUS_VALID);
			$metadata->save();
			
			$key = $metadata->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_DEFINITION);
			vFileSyncUtils::createSyncFileLinkForKey($key, $oldKey);
		}
		
		$metadataProfile = new VidiunMetadataProfile();
		$metadataProfile->fromObject($dbMetadataProfile, $this->getResponseProfile());
		
		return $metadataProfile;
	}
	
	/**
	 * Update an existing metadata object definition file
	 *
	 * @action updateDefinitionFromFile
	 * @param int $id
	 * @param file $xsdFile XSD metadata definition
	 * @return VidiunMetadataProfile
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 * @throws MetadataErrors::METADATA_FILE_NOT_FOUND
	 * @throws MetadataErrors::METADATA_UNABLE_TO_TRANSFORM
	 */
	function updateDefinitionFromFileAction($id, $xsdFile)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK($id);
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);
	
		$filePath = null;
		if($xsdFile)
		{
			$filePath = $xsdFile['tmp_name'];
			if(!file_exists($filePath))
				throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $xsdFile['name']);
		}
		
		$newXsd = file_get_contents($filePath);
		if(!$newXsd)
			throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $xsdFile['name']);
		
		if(!vMetadataProfileManager::validateXsdData($newXsd, $errorMessage)) {
		    throw new VidiunAPIException(MetadataErrors::INVALID_METADATA_PROFILE_SCHEMA, $errorMessage);
		}
		
		$key = $dbMetadataProfile->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_DEFINITION);
		
		$oldXsd = vFileSyncUtils::file_get_contents($key);
		if(!$oldXsd)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_FILE_NOT_FOUND, $id);
		
		$dbMetadataProfile->setXsdData($newXsd);
		
		try
		{
			vMetadataManager::diffMetadataProfile($dbMetadataProfile, $oldXsd, $newXsd);
		}
		catch(vXsdException $e)
		{
			throw new VidiunAPIException(MetadataErrors::METADATA_UNABLE_TO_TRANSFORM, $e->getMessage());
		}

		$dbMetadataProfile->save();

		$metadataProfile = new VidiunMetadataProfile();
		$metadataProfile->fromObject($dbMetadataProfile, $this->getResponseProfile());
		
		return $metadataProfile;
	}
	
	/**
	 * Update an existing metadata object views file
	 *
	 * @action updateViewsFromFile
	 * @param int $id
	 * @param file $viewsFile UI views file
	 * @return VidiunMetadataProfile
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 * @throws MetadataErrors::METADATA_FILE_NOT_FOUND
	 */
	function updateViewsFromFileAction($id, $viewsFile)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK($id);
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);
	
		$filePath = null;
		if($viewsFile)
		{
			$filePath = $viewsFile['tmp_name'];
			if(!file_exists($filePath))
				throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $viewsFile['name']);
			
			$viewsDate = file_get_contents($filePath);
			
			if(trim($viewsDate) == '')
			    throw new VidiunAPIException(MetadataErrors::EMPTY_VIEWS_DATA_PROVIDED, $viewsFile['name']);
		}
		
		$dbMetadataProfile->setViewesData($viewsDate);
		$dbMetadataProfile->save();
		
		$metadataProfile = new VidiunMetadataProfile();
		$metadataProfile->fromObject($dbMetadataProfile, $this->getResponseProfile());
		
		return $metadataProfile;
	}

	/**
	 * Update an existing metadata object XSLT file
	 *
	 * @action updateTransformationFromFile
	 * @param int $id
	 * @param file $xsltFile XSLT file, will be executed on every metadata add/update
	 * @return VidiunMetadataProfile
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 * @throws MetadataErrors::METADATA_FILE_NOT_FOUND
	 */
	function updateTransformationFromFileAction($id, $xsltFile)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK($id);
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);
	
		$filePath = null;
		if($xsltFile)
		{
			$filePath = $xsltFile['tmp_name'];
			if(!file_exists($filePath))
				throw new VidiunAPIException(MetadataErrors::METADATA_FILE_NOT_FOUND, $xsltFile['name']);
			
			$xsltData = file_get_contents($filePath);
			
			if(trim($xsltData) == '')
			    throw new VidiunAPIException(MetadataErrors::EMPTY_XSLT_DATA_PROVIDED, $xsltFile['name']);
                
		}
		
		$dbMetadataProfile->setXsltData($xsltData);
		$dbMetadataProfile->save();
		
		$metadataProfile = new VidiunMetadataProfile();
		$metadataProfile->fromObject($dbMetadataProfile, $this->getResponseProfile());
		
		return $metadataProfile;
	}
	
	/**
	 * Serves metadata profile XSD file
	 *
	 * @action serve
	 * @param int $id
	 * @return file
	 *
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 * @throws VidiunErrors::FILE_DOESNT_EXIST
	 */
	public function serveAction($id)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK( $id );
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);
		
		$fileName = $dbMetadataProfile->getSystemName() . '.xml';
		$fileSubType = MetadataProfile::FILE_SYNC_METADATA_DEFINITION;
		
		return $this->serveFile($dbMetadataProfile, $fileSubType, $fileName);
	}

	/**
	 * Serves metadata profile view file
	 *
	 * @action serveView
	 * @param int $id
	 * @return file
	 *
	 * @throws MetadataErrors::METADATA_PROFILE_NOT_FOUND
	 * @throws VidiunErrors::FILE_DOESNT_EXIST
	 */
	public function serveViewAction($id)
	{
		$dbMetadataProfile = MetadataProfilePeer::retrieveByPK( $id );
		
		if(!$dbMetadataProfile)
			throw new VidiunAPIException(MetadataErrors::METADATA_PROFILE_NOT_FOUND, $id);
		
		$fileName = $dbMetadataProfile->getSystemName() . '.xml';
		$fileSubType = MetadataProfile::FILE_SYNC_METADATA_VIEWS;
		
		return $this->serveFile($dbMetadataProfile, $fileSubType, $fileName);
	}
}
