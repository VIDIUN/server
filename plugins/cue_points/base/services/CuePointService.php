<?php
/**
 * Cue Point service
 *
 * @service cuePoint
 * @package plugins.cuePoint
 * @subpackage api.services
 * @throws VidiunErrors::SERVICE_FORBIDDEN
 */
class CuePointService extends VidiunBaseService
{
	/**
	 * @return CuePointType or null to limit the service type
	 */
	protected function getCuePointType()
	{
		return null;
	}
	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		

		// Play-Server and Media-Server list entries of all partners
		// This is not too expensive as the requests are cached conditionally and performed on sphinx
		$allowedSystemPartners = array(
			Partner::MEDIA_SERVER_PARTNER_ID,
			Partner::PLAY_SERVER_PARTNER_ID,
			Partner::BATCH_PARTNER_ID,
		);
		
		if(in_array($this->getPartnerId(), $allowedSystemPartners) && $actionName == 'list')
		{
			myPartnerUtils::resetPartnerFilter('entry');
		}
		else 
		{	
			$this->applyPartnerFilterForClass('CuePoint');
		}

		$vs = $this->getVs();
		// when session is not admin, allow access to user entries only
		if (!$vs || (!$vs->isAdmin() && !$vs->verifyPrivileges(vs::PRIVILEGE_LIST, vs::PRIVILEGE_WILDCARD))) {
			VidiunCriterion::enableTag(VidiunCriterion::TAG_USER_SESSION);
			CuePointPeer::setUserContentOnly(true);
		}
		
		if (!$vs || $vs->isAnonymousSession())
		{
			VidiunCriterion::enableTag(VidiunCriterion::TAG_WIDGET_SESSION);
		}
		
		if(!CuePointPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, CuePointPlugin::PLUGIN_NAME);
	}
	
	/**
	 * Allows you to add an cue point object associated with an entry
	 * 
	 * @action add
	 * @param VidiunCuePoint $cuePoint
	 * @return VidiunCuePoint
	 */
	function addAction(VidiunCuePoint $cuePoint)
	{
		$dbCuePoint = $cuePoint->toInsertableObject();

		// check if we have a limitEntry set on the VS, and if so verify that it is the same entry we work on
		$limitEntry = $this->getVs()->getLimitEntry();
		if ($limitEntry && $limitEntry != $cuePoint->entryId)
		{
			throw new VidiunAPIException(VidiunCuePointErrors::NO_PERMISSION_ON_ENTRY, $cuePoint->entryId);
		}

		if($cuePoint->systemName)
		{
			$existingCuePoint = CuePointPeer::retrieveBySystemName($cuePoint->entryId, $cuePoint->systemName);
			if($existingCuePoint)
				throw new VidiunAPIException(VidiunCuePointErrors::CUE_POINT_SYSTEM_NAME_EXISTS, $cuePoint->systemName, $existingCuePoint->getId());
		}
		
		/* @var $dbCuePoint CuePoint */
		$dbCuePoint->setPartnerId($this->getPartnerId());
		$dbCuePoint->setPuserId(is_null($cuePoint->userId) ? $this->getVuser()->getPuserId() : $cuePoint->userId);
		$dbCuePoint->setStatus(CuePointStatus::READY); 
					
		if($this->getCuePointType())
			$dbCuePoint->setType($this->getCuePointType());
			
		$created = $dbCuePoint->save();
		if(!$created)
		{
			VidiunLog::err("Cue point not created");
			return null;
		}
		
		$cuePoint = VidiunCuePoint::getInstance($dbCuePoint, $this->getResponseProfile());
		if(!$cuePoint)
		{
			VidiunLog::err("API Cue point not instantiated");
			return null;
		}
			
		return $cuePoint;
	}
	
	/**
	 * Allows you to add multiple cue points objects by uploading XML that contains multiple cue point definitions
	 * 
	 * @action addFromBulk
	 * @param file $fileData
	 * @return VidiunCuePointListResponse
	 * @throws VidiunCuePointErrors::XML_FILE_NOT_FOUND
	 * @throws VidiunCuePointErrors::XML_INVALID
	 */
	function addFromBulkAction($fileData)
	{
		try
		{
			$list = vCuePointManager::addFromXml($fileData['tmp_name'], $this->getPartnerId());
		}
		catch (vCoreException $e)
		{
			throw new VidiunAPIException($e->getCode());
		}
		
		$response = new VidiunCuePointListResponse();
		$response->objects = VidiunCuePointArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = count($list);
	
		return $response;
	}
	
	/**
	 * Download multiple cue points objects as XML definitions
	 * 
	 * @action serveBulk
	 * @param VidiunCuePointFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return file
	 */
	function serveBulkAction(VidiunCuePointFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunCuePointFilter();
		else
			$this->resetUserContentFilter($filter);

		$c = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		if($this->getCuePointType())
			$c->add(CuePointPeer::TYPE, $this->getCuePointType());
		
		$cuePointFilter = $filter->toObject();
		
		$cuePointFilter->attachToCriteria($c);
		if ($pager)
			$pager->attachToCriteria($c);
			
		$list = CuePointPeer::doSelect($c);
		$xml = vCuePointManager::generateXml($list);
		
		header("Content-Type: text/xml; charset=UTF-8");
		echo $xml;
		vFile::closeDbConnections();
		exit(0);
	}
	
	/**
	 * Retrieve an CuePoint object by id
	 * 
	 * @action get
	 * @param string $id 
	 * @return VidiunCuePoint
	 * @throws VidiunCuePointErrors::INVALID_CUE_POINT_ID
	 */		
	function getAction($id)
	{
		$dbCuePoint = CuePointPeer::retrieveByPK( $id );

		if(!$dbCuePoint)
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);
			
		if($this->getCuePointType() && $dbCuePoint->getType() != $this->getCuePointType())
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);
			
		$cuePoint = VidiunCuePoint::getInstance($dbCuePoint, $this->getResponseProfile());
		if(!$cuePoint)
			return null;
			
		return $cuePoint;
	}
	
	/**
	 * List cue point objects by filter and pager
	 * 
	 * @action list
	 * @param VidiunCuePointFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunCuePointListResponse
	 */
	function listAction(VidiunCuePointFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$pager)
		{
			$pager = new VidiunFilterPager();
			$pager->pageSize = baseObjectFilter::getMaxInValues();			// default to the max for compatibility reasons
		}

		if (!$filter)
			$filter = new VidiunCuePointFilter();
		else
			$this->resetUserContentFilter($filter);
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $this->getCuePointType());
	}
	
	/**
	 * count cue point objects by filter
	 * 
	 * @action count
	 * @param VidiunCuePointFilter $filter
	 * @return int
	 */
	function countAction(VidiunCuePointFilter $filter = null)
	{
		if (!$filter)
			$filter = new VidiunCuePointFilter();
		else
			$this->resetUserContentFilter($filter);
						
		$c = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		if($this->getCuePointType())
			$c->add(CuePointPeer::TYPE, $this->getCuePointType());
		
		$filter->applyPartnerOnCurrentContext($filter->getFilteredEntryIds());
		$cuePointFilter = $filter->toObject();
		$cuePointFilter->attachToCriteria($c);
		
		$c->applyFilters();
		return $c->getRecordsCount();
	}
	
	/**
	 * Update cue point by id 
	 * 
	 * @action update
	 * @param string $id
	 * @param VidiunCuePoint $cuePoint
	 * @return VidiunCuePoint
	 * @throws VidiunCuePointErrors::INVALID_CUE_POINT_ID
	 * @validateUser CuePoint id editcuepoint
	 */
	function updateAction($id, VidiunCuePoint $cuePoint)
	{
		$dbCuePoint = CuePointPeer::retrieveByPK($id);

		if (!$dbCuePoint)
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);

		if($this->getCuePointType() && $dbCuePoint->getType() != $this->getCuePointType())
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);

		// check if we have a limitEntry set on the VS, and if so verify that it is the same entry we work on
		$limitEntry = $this->getVs()->getLimitEntry();
		if ($limitEntry && $limitEntry != $dbCuePoint->getEntryId())
		{
			throw new VidiunAPIException(VidiunCuePointErrors::NO_PERMISSION_ON_ENTRY, $dbCuePoint->getEntryId());
		}

		if($cuePoint->systemName)
		{
			$existingCuePoint = CuePointPeer::retrieveBySystemName($dbCuePoint->getEntryId(), $cuePoint->systemName);
			if($existingCuePoint && $existingCuePoint->getId() != $id)
				throw new VidiunAPIException(VidiunCuePointErrors::CUE_POINT_SYSTEM_NAME_EXISTS, $cuePoint->systemName, $existingCuePoint->getId());
		}
		
		$dbCuePoint = $cuePoint->toUpdatableObject($dbCuePoint);

		$this->validateUserLog($dbCuePoint);
		
		$dbCuePoint->save();
		
		$cuePoint->fromObject($dbCuePoint, $this->getResponseProfile());
		return $cuePoint;
	}
	
	/**
	 * delete cue point by id, and delete all children cue points
	 * 
	 * @action delete
	 * @param string $id 
	 * @throws VidiunCuePointErrors::INVALID_CUE_POINT_ID
	 * @validateUser CuePoint id editcuepoint
	 */		
	function deleteAction($id)
	{
		$dbCuePoint = CuePointPeer::retrieveByPK( $id );
		
		if(!$dbCuePoint)
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);
			
		if($this->getCuePointType() && $dbCuePoint->getType() != $this->getCuePointType())
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);
		
		$this->validateUserLog($dbCuePoint);
		
		$dbCuePoint->setStatus(CuePointStatus::DELETED);
		$dbCuePoint->save();
	}
	
	/*
	 * Track delete and update API calls to identify if enabling validateUser annotation will 
	 * break any existing functionality
	 */
	private function validateUserLog($dbObject)
	{
		$log = 'validateUserLog: action ['.$this->actionName.'] client tag ['.vCurrentContext::$client_lang.'] ';
		if (!$this->getVs()){
			$log = $log.'Error: No VS ';
			VidiunLog::err($log);
			return;
		}		

		$log = $log.'vs ['.$this->getVs()->getOriginalString().'] ';
		// if admin always allowed
		if (vCurrentContext::$is_admin_session)
			return;

		if (strtolower($dbObject->getPuserId()) != strtolower(vCurrentContext::$vs_uid)) 
		{
			$log = $log.'Error: User not an owner ';
			VidiunLog::err($log);
		}
	}
	
	/**
	 * Update cuePoint status by id
	 *
	 * @action updateStatus
	 * @param string $id
	 * @param VidiunCuePointStatus $status
	 * @throws VidiunCuePointErrors::INVALID_CUE_POINT_ID
	 */
	function updateStatusAction($id, $status)
	{
		$dbCuePoint = CuePointPeer::retrieveByPK($id);
		
		if (!$dbCuePoint)
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);
			
		if($this->getCuePointType() && $dbCuePoint->getType() != $this->getCuePointType())
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);
	
		$this->validateUserLog($dbCuePoint);
		
		$dbCuePoint->setStatus($status);
		$dbCuePoint->save();
	}


	/**
	 *
	 * @action updateCuePointsTimes
	 * @param string $id
	 * @param int $startTime
	 * @param int $endTime
	 * @return VidiunCuePoint
	 * @throws VidiunCuePointErrors::INVALID_CUE_POINT_ID
	 */
	function updateCuePointsTimesAction($id, $startTime,$endTime= null)
	{
		$dbCuePoint = CuePointPeer::retrieveByPK($id);

		if (!$dbCuePoint)
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);

		if($this->getCuePointType() && $dbCuePoint->getType() != $this->getCuePointType())
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);

		$this->validateUserLog($dbCuePoint);

		$dbCuePoint->setStartTime($startTime);
		if ($endTime)
		{
			$dbCuePoint->setEndTime($endTime);
		}
		$dbCuePoint->save();
		$cuePoint = VidiunCuePoint::getInstance($dbCuePoint, $this->getResponseProfile());
		return $cuePoint;
	}

	/**
	 * Clone cuePoint with id to given entry
	 *
	 * @action clone
	 * @param string $id
	 * @param string $entryId
	 * @return VidiunCuePoint
	 * @throws VidiunCuePointErrors::INVALID_CUE_POINT_ID
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function cloneAction($id, $entryId)
	{
		$newdbCuePoint = $this->doClone($id, $entryId);
		$newdbCuePoint->save();
		$cuePoint = VidiunCuePoint::getInstance($newdbCuePoint, $this->getResponseProfile());
		return $cuePoint;
	}

	protected function doClone($id, $entryId)
	{
		$dbCuePoint = CuePointPeer::retrieveByPK($id);
		if (!$dbCuePoint)
			throw new VidiunAPIException(VidiunCuePointErrors::INVALID_CUE_POINT_ID, $id);
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		$newdbCuePoint = $dbCuePoint->copyToEntry($dbEntry);
		return $newdbCuePoint;
	}

	private function resetUserContentFilter($filter)
	{
		if (CuePointPeer::getUserContentOnly())
		{
			$entryFilter = $filter->entryIdEqual ? $filter->entryIdEqual : $filter->entryIdIn;
			if($entryFilter && $this->getVs()->verifyPrivileges(vs::PRIVILEGE_LIST, $entryFilter))
				CuePointPeer::setUserContentOnly(false);
		}
	}
}
