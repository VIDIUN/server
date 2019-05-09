<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.filters
 */
class VidiunDropFolderFileFilter extends VidiunDropFolderFileBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new DropFolderFileFilter();
	}
	
	/**
	 * @param VidiunFilterPager $pager
	 * @param VidiunDetachedResponseProfile $responseProfile
	 * @return VidiunListResponse
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$dropFolderFileFilter = $this->toObject();
		
		$c = new Criteria();
		$dropFolderFileFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		$list = DropFolderFilePeer::doSelect($c);
		
		$totalCount = 0;
		$resultCount = count($list);
		if (($pager->pageIndex == 1 || $resultCount) && $resultCount < $pager->pageSize)
		{
			$totalCount = ($pager->pageIndex - 1) * $pager->pageSize + $resultCount;
		}
		else
		{
			VidiunFilterPager::detachFromCriteria($c);
			$totalCount = DropFolderFilePeer::doCount($c);
		}
		
		$response = new VidiunDropFolderFileListResponse();
		$response->objects = VidiunDropFolderFileArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
		return $response;
	}
}
