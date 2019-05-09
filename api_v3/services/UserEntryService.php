<?php

/**
 * @service userEntry
 * @package api
 * @subpackage services
 */
class UserEntryService extends VidiunBaseService {

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('userEntry');
	}

	/**
	 * Adds a user_entry to the Vidiun DB.
	 *
	 * @action add
	 * @param VidiunUserEntry $userEntry
	 * @return VidiunUserEntry
	 */
	public function addAction(VidiunUserEntry $userEntry)
	{
		$dbUserEntry = $userEntry->toInsertableObject(null, array('type'));
		$lockUser = $userEntry->userId ? $userEntry->userId : vCurrentContext::getCurrentVsVuserId();
		$lockKey = "userEntry_add_" . $this->getPartnerId() . $userEntry->entryId . $lockUser;
		$dbUserEntry = vLock::runLocked($lockKey, array($this, 'addUserEntryImpl'), array($dbUserEntry));
		$userEntry->fromObject($dbUserEntry, $this->getResponseProfile());

		return $userEntry;
	}
	
	public function addUserEntryImpl($dbUserEntry)
	{
		if($dbUserEntry->checkAlreadyExists())
		{
			throw new VidiunAPIException(VidiunErrors::USER_ENTRY_ALREADY_EXISTS);
		}
		$dbUserEntry->save();
		
		return $dbUserEntry;
	}

	/**
	 *
	 * @action update
	 * @param int $id
	 * @param VidiunUserEntry $userEntry
	 * @return VidiunUserEntry
	 * @throws VidiunAPIException
	 */
	public function updateAction($id, VidiunUserEntry $userEntry)
	{
		$dbUserEntry = UserEntryPeer::retrieveByPK($id);
		if (!$dbUserEntry)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);

		$dbUserEntry = $userEntry->toUpdatableObject($dbUserEntry);
		$dbUserEntry->save();
		
		$userEntry->fromObject($dbUserEntry);
		
		return $userEntry;
	}

	/**
	 * @action delete
	 * @param int $id
	 * @return VidiunUserEntry The deleted UserEntry object
 	 * @throws VidiunAPIException
	 */
	public function deleteAction($id)
	{
		$dbUserEntry = UserEntryPeer::retrieveByPK($id);
		if (!$dbUserEntry)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);
		$dbUserEntry->setStatus(VidiunUserEntryStatus::DELETED);
		$dbUserEntry->save();

		$userEntry = VidiunUserEntry::getInstanceByType($dbUserEntry->getType());
		$userEntry->fromObject($dbUserEntry, $this->getResponseProfile());

		return $userEntry;

	}

	/**
	 * @action list
	 * @param VidiunUserEntryFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunUserEntryListResponse
	 */
	public function listAction(VidiunUserEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
		{
			$filter = new VidiunUserEntryFilter();
		}
		
		if (!$pager)
		{
			$pager = new VidiunFilterPager();
		}
		// return empty list when userId was not given
		if ( $this->getVs() && !$this->getVs()->isAdmin() && !vCurrentContext::$vs_uid )
		{
			return $filter->getEmptyListResponse();
		}
		return $filter->getListResponse($pager, $this->getResponseProfile());
	}

	/**
	 * @action get
	 * @param string $id
	 * @return VidiunUserEntry
	 * @throws VidiunAPIException
	 */
	public function getAction($id)
	{
		$dbUserEntry = UserEntryPeer::retrieveByPK( $id );
		if(!$dbUserEntry)
			throw new VidiunAPIException(VidiunErrors::USER_ENTRY_NOT_FOUND, $id);

		$userEntry = VidiunUserEntry::getInstanceByType($dbUserEntry->getType());
		if (!$userEntry)
			return null;
		$userEntry->fromObject($dbUserEntry);
		return $userEntry;
	}
}
