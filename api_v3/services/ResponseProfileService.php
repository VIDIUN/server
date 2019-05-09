<?php

/**
 * Manage response profiles
 *
 * @service responseProfile
 */
class ResponseProfileService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		//Don;t apply partner filter if action is list to avoid returning default partner 0 response profiles on every call
		if($actionName !== "list")
			$this->applyPartnerFilterForClass('ResponseProfile'); 	
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerGroup()
	 */
	protected function partnerGroup($peer = null)
	{
		
		switch ($this->actionName)
		{
			case 'get':
				return $this->partnerGroup . ',0';
			//When requesting response profiles allow default once in case requesting partner is internal
			case 'list':
				if(vCurrentContext::$vs_partner_id <= 0)
					return $this->partnerGroup . ',0';
		}
			
		return $this->partnerGroup;
	}
	
	/**
	 * Add new response profile
	 * 
	 * @action add
	 * @param VidiunResponseProfile $addResponseProfile
	 * @return VidiunResponseProfile
	 */
	function addAction(VidiunResponseProfile $addResponseProfile)
	{
		$dbResponseProfile = $addResponseProfile->toInsertableObject();
		/* @var $dbResponseProfile ResponseProfile */
		$dbResponseProfile->setPartnerId($this->getPartnerId());
		$dbResponseProfile->setStatus(ResponseProfileStatus::ENABLED);
		$dbResponseProfile->save();
		
		$addResponseProfile = new VidiunResponseProfile();
		$addResponseProfile->fromObject($dbResponseProfile, $this->getResponseProfile());
		return $addResponseProfile;
	}
	
	/**
	 * Get response profile by id
	 * 
	 * @action get
	 * @param bigint $id
	 * @return VidiunResponseProfile
	 * 
	 * @throws VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbResponseProfile = ResponseProfilePeer::retrieveByPK($id);
		if (!$dbResponseProfile)
			throw new VidiunAPIException(VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND, $id);
			
		$responseProfile = new VidiunResponseProfile();
		$responseProfile->fromObject($dbResponseProfile, $this->getResponseProfile());
		return $responseProfile;
	}
	
	/**
	 * Update response profile by id
	 * 
	 * @action update
	 * @param bigint $id
	 * @param VidiunResponseProfile $updateResponseProfile
	 * @return VidiunResponseProfile
	 * 
	 * @throws VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND
	 */
	function updateAction($id, VidiunResponseProfile $updateResponseProfile)
	{
		$dbResponseProfile = ResponseProfilePeer::retrieveByPK($id);
		if (!$dbResponseProfile)
			throw new VidiunAPIException(VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND, $id);
		
		$updateResponseProfile->toUpdatableObject($dbResponseProfile);
		$dbResponseProfile->save();
		
		$updateResponseProfile = new VidiunResponseProfile();
		$updateResponseProfile->fromObject($dbResponseProfile, $this->getResponseProfile());
		return $updateResponseProfile;
	}

	/**
	 * Update response profile status by id
	 * 
	 * @action updateStatus
	 * @param bigint $id
	 * @param VidiunResponseProfileStatus $status
	 * @return VidiunResponseProfile
	 * 
	 * @throws VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND
	 */
	function updateStatusAction($id, $status)
	{
		$dbResponseProfile = ResponseProfilePeer::retrieveByPK($id);
		if (!$dbResponseProfile)
			throw new VidiunAPIException(VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND, $id);

		if($status == VidiunResponseProfileStatus::ENABLED)
		{
			//Check uniqueness of new object's system name
			$systemNameProfile = ResponseProfilePeer::retrieveBySystemName($dbResponseProfile->getSystemName(), $id);
			if ($systemNameProfile)
				throw new VidiunAPIException(VidiunErrors::RESPONSE_PROFILE_DUPLICATE_SYSTEM_NAME, $dbResponseProfile->getSystemName());
		}	
		
		$dbResponseProfile->setStatus($status);
		$dbResponseProfile->save();
	
		$responseProfile = new VidiunResponseProfile();
		$responseProfile->fromObject($dbResponseProfile, $this->getResponseProfile());
		return $responseProfile;
	}
	
	/**
	 * Delete response profile by id
	 * 
	 * @action delete
	 * @param bigint $id
	 * 
	 * @throws VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND
	 */
	function deleteAction($id)
	{
		$dbResponseProfile = ResponseProfilePeer::retrieveByPK($id);
		if (!$dbResponseProfile)
			throw new VidiunAPIException(VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND, $id);

		$dbResponseProfile->setStatus(ResponseProfileStatus::DELETED);
		$dbResponseProfile->save();
	}
	
	/**
	 * List response profiles by filter and pager
	 * 
	 * @action list
	 * @param VidiunFilterPager $filter
	 * @param VidiunResponseProfileFilter $pager
	 * @return VidiunResponseProfileListResponse
	 */
	function listAction(VidiunResponseProfileFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunResponseProfileFilter();
		
		//Add partner 0 to filter only in case systemNmae or Id are provided in the filter to avoid returning it by default
		if(isset($filter->systemNameEqual) || isset($filter->idEqual)) {
			$this->partnerGroup .= ",0";
		}
		$this->applyPartnerFilterForClass('ResponseProfile');

		if (!$pager)
			$pager = new VidiunFilterPager();
			
		$responseProfileFilter = new ResponseProfileFilter();
		$filter->toObject($responseProfileFilter);

		$c = new Criteria();
		$responseProfileFilter->attachToCriteria($c);
		
		$totalCount = ResponseProfilePeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = ResponseProfilePeer::doSelect($c);
		
		$list = VidiunResponseProfileArray::fromDbArray($dbList, $this->getResponseProfile());
		$response = new VidiunResponseProfileListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	 * Recalculate response profile cached objects
	 * 
	 * @action recalculate
	 * @param VidiunResponseProfileCacheRecalculateOptions $options
	 * @return VidiunResponseProfileCacheRecalculateResults
	 */
	function recalculateAction(VidiunResponseProfileCacheRecalculateOptions $options)
	{
		return VidiunResponseProfileCacher::recalculateCacheBySessionType($options);
	}
	
	/**
	 * Clone an existing response profile
	 * 
	 * @action clone
	 * @param bigint $id
	 * @param VidiunResponseProfile $profile
	 * @throws VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND
	 * @throws VidiunErrors::RESPONSE_PROFILE_DUPLICATE_SYSTEM_NAME
	 * @return VidiunResponseProfile
	 */
	function cloneAction ($id, VidiunResponseProfile $profile)
	{
		$origResponseProfileDbObject = ResponseProfilePeer::retrieveByPK($id);
		if (!$origResponseProfileDbObject)
			throw new VidiunAPIException(VidiunErrors::RESPONSE_PROFILE_ID_NOT_FOUND, $id);
			
		$newResponseProfileDbObject = $origResponseProfileDbObject->copy();
		
		if ($profile)
			$newResponseProfileDbObject = $profile->toInsertableObject($newResponseProfileDbObject);
				
		$newResponseProfileDbObject->save();
		
		$newResponseProfile = new VidiunResponseProfile();
		$newResponseProfile->fromObject($newResponseProfileDbObject, $this->getResponseProfile());
		return $newResponseProfile;
	}
}