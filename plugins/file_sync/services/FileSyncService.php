<?php
/**
 * System user service
 *
 * @service fileSync
 * @package plugins.fileSync
 * @subpackage api.services
 */
class FileSyncService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		// since plugin might be using VS impersonation, we need to validate the requesting
		// partnerId from the VS and not with the $_POST one
		if(!FileSyncPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, FileSyncPlugin::PLUGIN_NAME);
	}
	
	/**
	 * List file syce objects by filter and pager
	 *
	 * @action list
	 * @param VidiunFileSyncFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunFileSyncListResponse
	 */
	function listAction(VidiunFileSyncFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunFileSyncFilter();

		if (!$pager)
			$pager = new VidiunFilterPager();
			
		$fileSyncFilter = new FileSyncFilter();
		
		$filter->toObject($fileSyncFilter);

		$c = new Criteria();
		$fileSyncFilter->attachToCriteria($c);
		
		$totalCount = FileSyncPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = FileSyncPeer::doSelect($c);
		
		$list = VidiunFileSyncArray::fromDbArray($dbList, $this->getResponseProfile());
		$response = new VidiunFileSyncListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;
	}

	/**
	 * Update file sync by id
	 * 
	 * @action update
	 * @param int $id
	 * @param VidiunFileSync $fileSync
	 * @return VidiunFileSync
	 * 
	 * @throws FileSyncErrors::FILESYNC_ID_NOT_FOUND
	 */
	function updateAction($id, VidiunFileSync $fileSync)
	{
		$dbFileSync = FileSyncPeer::retrieveByPK($id);
		if (!$dbFileSync)
		{
			throw new VidiunAPIException(FileSyncErrors::FILESYNC_ID_NOT_FOUND, $id);
		}

		$fileSync->toUpdatableObject($dbFileSync);
		$dbFileSync->save();
		$dbFileSync->encrypt();
		
		$fileSync = new VidiunFileSync();
		$fileSync->fromObject($dbFileSync, $this->getResponseProfile());
		return $fileSync;
	}
}
