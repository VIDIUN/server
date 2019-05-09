<?php
/**
 * Reach Profile Service
 *
 * @service reachProfile
 * @package plugins.reach
 * @subpackage api.services
 * @throws VidiunErrors::SERVICE_FORBIDDEN
 */

class ReachProfileService extends VidiunBaseService
{

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if (!ReachPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, ReachPlugin::PLUGIN_NAME);

		$this->applyPartnerFilterForClass('reachProfile');
	}

	/**
	 * Allows you to add a partner specific reach profile
	 *
	 * @action add
	 * @param VidiunReachProfile $reachProfile
	 * @return VidiunReachProfile
	 */
	public function addAction(VidiunReachProfile $reachProfile)
	{
		$dbReachProfile = $reachProfile->toInsertableObject();

		/* @var $dbReachProfile ReachProfile */
		$dbReachProfile->setPartnerId(vCurrentContext::getCurrentPartnerId());
		$dbReachProfile->setStatus(VidiunReachProfileStatus::ACTIVE);
		$credit = $dbReachProfile->getCredit();
		if ( $credit && $credit instanceof vReoccurringVendorCredit)
		{
			/* @var $credit vReoccurringVendorCredit */
			$credit->setPeriodDates();
			$dbReachProfile->setCredit($credit);
		}

		$dbReachProfile->save();

		// return the saved object
		$reachProfile->fromObject($dbReachProfile, $this->getResponseProfile());
		return $reachProfile;
	}

	/**
	 * Retrieve specific reach profile by id
	 *
	 * @action get
	 * @param int $id
	 * @return VidiunReachProfile
	 * @throws VidiunReachErrors::REACH_PROFILE_NOT_FOUND
	 */
	function getAction($id)
	{
		$dbReachProfile = ReachProfilePeer::retrieveByPK($id);
		if (!$dbReachProfile)
			throw new VidiunAPIException(VidiunReachErrors::REACH_PROFILE_NOT_FOUND, $id);
		
		$reachProfile = new VidiunReachProfile();
		$reachProfile->fromObject($dbReachProfile, $this->getResponseProfile());
		return $reachProfile;
	}

	/**
	 * List VidiunReachProfile objects
	 *
	 * @action list
	 * @param VidiunReachProfileFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunReachProfileListResponse
	 */
	public function listAction(VidiunReachProfileFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunReachProfileFilter();

		if (!$pager)
			$pager = new VidiunFilterPager();

		return $filter->getListResponse($pager, $this->getResponseProfile());
	}

	/**
	 * Update an existing reach profile object
	 *
	 * @action update
	 * @param int $id
	 * @param VidiunReachProfile $reachProfile
	 * @return VidiunReachProfile
	 *
	 * @throws VidiunReachErrors::REACH_PROFILE_NOT_FOUND
	 */
	public function updateAction($id, VidiunReachProfile $reachProfile)
	{
		// get the object
		$dbReachProfile = ReachProfilePeer::retrieveByPK($id);
		if (!$dbReachProfile)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $id);

		// save the object
		$dbReachProfile = $reachProfile->toUpdatableObject($dbReachProfile);
		$credit = $dbReachProfile->getCredit();
		if ($credit && $credit instanceof vReoccurringVendorCredit)
		{
			/* @var $credit vReoccurringVendorCredit */
			$credit->setPeriodDates();
			$dbReachProfile->setCredit($credit);
		}
		
		$dbReachProfile->save();

		// return the saved object
		$reachProfile = new VidiunReachProfile();
		$reachProfile->fromObject($dbReachProfile, $this->getResponseProfile());
		return $reachProfile;
	}

	/**
	 * Update reach profile status by id
	 *
	 * @action updateStatus
	 * @param int $id
	 * @param VidiunReachProfileStatus $status
	 * @return VidiunReachProfile
	 *
	 * @throws VidiunReachErrors::REACH_PROFILE_NOT_FOUND
	 */
	function updateStatusAction($id, $status)
	{
		// get the object
		$dbReachProfile = ReachProfilePeer::retrieveByPK($id);
		if (!$dbReachProfile)
			throw new VidiunAPIException(VidiunReachErrors::CATALOG_ITEM_NOT_FOUND, $id);
		
		$dbReachProfile->setStatus($status);
		$credit = $dbReachProfile->getCredit();
		if ($status == VidiunReachProfileStatus::ACTIVE && $credit && $credit instanceof vReoccurringVendorCredit)
        {
	        /* @var $credit vReoccurringVendorCredit */
			$credit->setPeriodDates();
			$dbReachProfile->setCredit($credit);
		}
		
		// save the object
		$dbReachProfile->save();

		// return the saved object
		$reachProfile = new VidiunReachProfile();
		$reachProfile->fromObject($dbReachProfile, $this->getResponseProfile());
		return $reachProfile;
	}

	/**
	 * Delete vednor profile by id
	 *
	 * @action delete
	 * @param int $id
	 *
	 * @throws VidiunReachErrors::REACH_PROFILE_NOT_FOUND
	 */
	public function deleteAction($id)
	{
		// get the object
		$dbReachProfile = ReachProfilePeer::retrieveByPK($id);
		if (!$dbReachProfile)
			throw new VidiunAPIException(VidiunReachErrors::REACH_PROFILE_NOT_FOUND, $id);

		// set the object status to deleted
		$dbReachProfile->setStatus(VidiunReachProfileStatus::DELETED);
		$dbReachProfile->save();
	}

	/**
	 * sync vednor profile credit
	 *
	 * @action syncCredit
	 * @param int $reachProfileId
	 * @return VidiunReachProfile
	 * @throws VidiunReachErrors::REACH_PROFILE_NOT_FOUND
	 */
	public function syncCredit($reachProfileId)
	{
		$dbReachProfile = ReachProfilePeer::retrieveByPK($reachProfileId);
		if (!$dbReachProfile)
			throw new VidiunAPIException(VidiunReachErrors::REACH_PROFILE_NOT_FOUND, $reachProfileId);

		// set the object status to deleted
		if( $dbReachProfile->shouldSyncCredit())
		{
			$dbReachProfile->syncCredit();
			$dbReachProfile->save();
		}

		// return the saved object
		$reachProfile = new VidiunReachProfile();
		$reachProfile->fromObject($dbReachProfile, $this->getResponseProfile());
		return $reachProfile;
	}
}
