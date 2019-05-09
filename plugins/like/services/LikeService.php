<?php
/**
 * Allows user to 'like' or 'unlike' and entry
 *
 * @service like
 * @package plugins.like
 * @subpackage api.services
 */
class LikeService extends VidiunBaseService
{
    const VVOTE_LIKE_RANK_VALUE = 1;
    const VVOTE_UNLIKE_RANK_VALUE = 0;
    
    public function initService($serviceId, $serviceName, $actionName)
    {
        parent::initService($serviceId, $serviceName, $actionName);
		
		if(!LikePlugin::isAllowedPartner($this->getPartnerId()))
		{
		    throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, LikePlugin::PLUGIN_NAME);
		}	
		
		if ((!vCurrentContext::$vs_uid || vCurrentContext::$vs_uid == "") && $actionName != "list")
		{
		    throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
		}
    }
    
    /**
     * @action like
     * Action for current vuser to mark the entry as "liked".
     * @param string $entryId
     * @throws VidiunLikeErrors::USER_LIKE_FOR_ENTRY_ALREADY_EXISTS
     * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
     * @return bool
     */
    public function likeAction ( $entryId )
    {
        if (!$entryId)
	    {
	        throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, "entryId");
	    }
	    
	    //Check if a vvote for current entryId and vuser already exists. If it does - throw exception
	    $existingVVote = vvotePeer::doSelectByEntryIdAndPuserId($entryId, $this->getPartnerId(), vCurrentContext::$vs_uid);
	    if ($existingVVote)
	    {
	        throw new VidiunAPIException(VidiunLikeErrors::USER_LIKE_FOR_ENTRY_ALREADY_EXISTS);
	    }
	    
	    $dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		if (vvotePeer::enableExistingVVote($entryId, $this->getPartnerId(), vCurrentContext::$vs_uid))
		{
		    return true;
		}
		
		vvotePeer::createVvote($entryId, $this->getPartnerId(), vCurrentContext::$vs_uid, self::VVOTE_LIKE_RANK_VALUE, VVoteType::LIKE);
	    
	    return true;
    }
    
    /**
     * @action unlike
     * Action for current vuser to revoke a previously added "like" from an entry
     * @param string $entryId
     * @return bool
     */
    public function unlikeAction ( $entryId )
    {
        if (!$entryId)
	    {
	        throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, "entryId");
	    }
	    
	    $existingVVote = vvotePeer::doSelectByEntryIdAndPuserId($entryId, $this->getPartnerId(), vCurrentContext::$vs_uid);
	    if (!$existingVVote)
	    {
	        throw new VidiunAPIException(VidiunLikeErrors::USER_LIKE_FOR_ENTRY_NOT_FOUND);
	    }
	    
	    $dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
        
		if (vvotePeer::disableExistingVVote($entryId, $this->getPartnerId(), vCurrentContext::$vs_uid))
		    return true;
		
		return false;
    
    }
    
    /**
     * @action checkLikeExists
     * Action to check whether a user likes a specific entry
     * @param string $entryId
     * @param string $userId
     * @return bool
     */
    public function checkLikeExistsAction ( $entryId , $userId = null )
    {
        if (!$entryId)
	    {
	        throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, "entryId");
	    }
        
	    if (!$userId)
	    {
	        $userId = vCurrentContext::$vs_uid;
	    }
	    
	    $existingVVote = vvotePeer::doSelectByEntryIdAndPuserId($entryId, $this->getPartnerId(), $userId);
	    if (!$existingVVote || !count($existingVVote))
	    {
	        return false;
	    }
	    
	    return true;
        	    
    }

	/**
	 * @action list
	 * @param VidiunLikeFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunLikeListResponse
	 */
	public function listAction(VidiunLikeFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
			$filter = new VidiunLikeFilter();
		else	
		{			
			if($filter->entryIdEqual && !entryPeer::retrieveByPK($filter->entryIdEqual))			
				throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $filter->entryIdEqual);			
		}
		
		if(!$pager)
			$pager = new VidiunFilterPager();

		return $filter->getListResponse($pager, null);
	}
	
}
