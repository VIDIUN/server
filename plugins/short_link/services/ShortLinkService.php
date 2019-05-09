<?php
/**
 * Short link service
 *
 * @service shortLink
 * @package plugins.shortLink
 * @subpackage api.services
 */
class ShortLinkService extends VidiunBaseService
{
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'goto')
			return false;
			
		return parent::partnerRequired($actionName);
	}
	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if($actionName != 'goto')
		{
			$this->applyPartnerFilterForClass('ShortLink');
			$this->applyPartnerFilterForClass('vuser');
		}
	}
	
	/**
	 * List short link objects by filter and pager
	 * 
	 * @action list
	 * @param VidiunShortLinkFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunShortLinkListResponse
	 */
	function listAction(VidiunShortLinkFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunShortLinkFilter;
			
		$shortLinkFilter = $filter->toFilter($this->getPartnerId());
		
		$c = new Criteria();
		$shortLinkFilter->attachToCriteria($c);
		$count = ShortLinkPeer::doCount($c);
		
		if (! $pager)
			$pager = new VidiunFilterPager ();
		$pager->attachToCriteria ( $c );
		$list = ShortLinkPeer::doSelect($c);
		
		$response = new VidiunShortLinkListResponse();
		$response->objects = VidiunShortLinkArray::fromDbArray($list, $this->getResponseProfile());
		$response->totalCount = $count;
		
		return $response;
	}
	
	/**
	 * Allows you to add a short link object
	 * 
	 * @action add
	 * @param VidiunShortLink $shortLink
	 * @return VidiunShortLink
	 */
	function addAction(VidiunShortLink $shortLink)
	{
		$shortLink->validatePropertyNotNull('systemName');
		$shortLink->validatePropertyMinLength('systemName', 3);
		$shortLink->validatePropertyNotNull('fullUrl');
		$shortLink->validatePropertyMinLength('fullUrl', 10);
		
		if(!$shortLink->status)
			$shortLink->status = VidiunShortLinkStatus::ENABLED;
			
		if(!$shortLink->userId)
			$shortLink->userId = $this->getVuser()->getPuserId();
			
		$dbShortLink = new ShortLink();
		$dbShortLink = $shortLink->toInsertableObject($dbShortLink, array('userId'));
		$dbShortLink->setPartnerId($this->getPartnerId());
		$dbShortLink->setPuserId(is_null($shortLink->userId) ? $this->getVuser()->getPuserId() : $shortLink->userId);
		$dbShortLink->save();
		
		$shortLink = new VidiunShortLink();
		$shortLink->fromObject($dbShortLink, $this->getResponseProfile());
		
		return $shortLink;
	}
	
	/**
	 * Retrieve an short link object by id
	 * 
	 * @action get
	 * @param string $id 
	 * @return VidiunShortLink
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	function getAction($id)
	{
		$dbShortLink = ShortLinkPeer::retrieveByPK($id);
		
		if(!$dbShortLink)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);
			
		$shortLink = new VidiunShortLink();
		$shortLink->fromObject($dbShortLink, $this->getResponseProfile());
		
		return $shortLink;
	}


	/**
	 * Update existing short link
	 * 
	 * @action update
	 * @param string $id
	 * @param VidiunShortLink $shortLink
	 * @return VidiunShortLink
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */	
	function updateAction($id, VidiunShortLink $shortLink)
	{
		$dbShortLink = ShortLinkPeer::retrieveByPK($id);
	
		if (!$dbShortLink)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);
		
		$dbShortLink = $shortLink->toUpdatableObject($dbShortLink);
		$dbShortLink->save();
	
		$shortLink->fromObject($dbShortLink, $this->getResponseProfile());
		
		return $shortLink;
	}

	/**
	 * Mark the short link as deleted
	 * 
	 * @action delete
	 * @param string $id 
	 * @return VidiunShortLink
	 *
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	function deleteAction($id)
	{
		$dbShortLink = ShortLinkPeer::retrieveByPK($id);
	
		if (!$dbShortLink)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);
		
		$dbShortLink->setStatus(VidiunShortLinkStatus::DELETED);
		$dbShortLink->save();
			
		$shortLink = new VidiunShortLink();
		$shortLink->fromObject($dbShortLink, $this->getResponseProfile());
		
		return $shortLink;
	}

	/**
	 * Serves short link
	 * 
	 * @action goto
	 * @param string $id
	 * @param bool $proxy proxy the response instead of redirect
	 * @return file
	 * @vsIgnored
	 * 
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 */		
	function gotoAction($id, $proxy = false)
	{
		VidiunResponseCacher::disableCache();
		
		$dbShortLink = ShortLinkPeer::retrieveByPK($id);
	
		if (!$dbShortLink)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);

		if($proxy)
			vFileUtils::dumpUrl($dbShortLink->getFullUrl(), true, true);
		
		header('Location: ' . $dbShortLink->getFullUrl());
		die;
	}
}
