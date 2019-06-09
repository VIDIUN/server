<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunServerNodeFilter extends VidiunServerNodeBaseFilter
{
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new ServerNodeFilter();
	}
	
	public function getTypeListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null, $type = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager, $type);
		$response = new VidiunServerNodeListResponse();
		$response->objects = VidiunServerNodeArray::fromDbArray($list, $responseProfile);
		$response->totalCount = $totalCount;
	
		return $response;
	}
	
	protected function doGetListResponse(VidiunFilterPager $pager, $type = null)
	{
		$c = new Criteria();
			
		if($type)
			$c->add(ServerNodePeer::TYPE, $type);
			
		$serverNodeFilter = $this->toObject();
		$serverNodeFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);
			
		$list = ServerNodePeer::doSelect($c);
		$totalCount = count($list);
	
		return array($list, $totalCount);
	}

	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		return $this->getTypeListResponse($pager, $responseProfile);
	}
}
