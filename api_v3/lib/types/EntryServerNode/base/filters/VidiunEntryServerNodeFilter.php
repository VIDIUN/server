<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunEntryServerNodeFilter extends VidiunEntryServerNodeBaseFilter
{
	/**
	 * @return baseObjectFilter
	 */
	protected function getCoreFilter()
	{
		return new EntryServerNodeFilter();
	}

	/**
	 * @param VidiunFilterPager $pager
	 * @param VidiunDetachedResponseProfile $responseProfile
	 * @return VidiunListResponse
	 * @throws VidiunAPIException
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		if($this->entryIdEqual)
		{
			$entry = entryPeer::retrieveByPK($this->entryIdEqual);
			if(!$entry)
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryIdEqual);
		} 
		else if ($this->entryIdIn)
		{
			$entryIds = explode(',', $this->entryIdIn);
			$entries = entryPeer::retrieveByPKs($entryIds);
			
			$validEntryIds = array();
			foreach ($entries as $entry)
				$validEntryIds[] = $entry->getId();
			
			if (!count($validEntryIds))
			{
				return array(array(), 0);
			}
			
			$entryIds = implode($validEntryIds, ',');
			$this->entryIdIn = $entryIds;
		}

		$c = new Criteria();
		$entryServerNodeFilter = $this->toObject();
		$entryServerNodeFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);

		$dbEntryServerNodes = EntryServerNodePeer::doSelect($c);

		$entryServerNodeList = VidiunEntryServerNodeArray::fromDbArray($dbEntryServerNodes, $responseProfile);
		$response = new VidiunEntryServerNodeListResponse();
		$response->objects = $entryServerNodeList;
		$response->totalCount = count($dbEntryServerNodes);
		return $response;
	}
}
