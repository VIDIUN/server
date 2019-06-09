<?php

/**
 * Subclass for performing query and update operations on the 'vvote' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class vvotePeer extends BasevvotePeer
{
    public static function setDefaultCriteriaFilter()
    {
        if ( self::$s_criteria_filter == null )
		{
			self::$s_criteria_filter = new criteriaFilter ();
		}

		$c = new myCriteria();
		$c->add ( vvotePeer::STATUS, VVoteStatus::REVOKED, Criteria::NOT_EQUAL );
		
		self::$s_criteria_filter->setFilter ( $c );
    }
    
    
    public static function doSelectByEntryIdAndPuserId ($entryId, $partnerId, $puserId)
    {
        $vuser = self::getVuserFromPuserAndPartner($puserId, $partnerId);
        if (!$vuser)
        {
            return null;
        }
        
        $c = new Criteria(); 
        $c->addAnd(vvotePeer::VUSER_ID, $vuser->getId(), Criteria::EQUAL);
        $c->addAnd(vvotePeer::ENTRY_ID, $entryId, Criteria::EQUAL);
        
        return self::doSelectOne($c);
    }
    
    protected static function getVuserFromPuserAndPartner($puserId, $partnerId, $shouldCreate = false)
	{
		$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $puserId, true);
    		
		return $vuser;
	}
	
	public static function enableExistingVVote ($entryId, $partnerId, $puserId)
	{
	    self::setUseCriteriaFilter(false);
	    
	    $vvote = self::doSelectByEntryIdAndPuserId($entryId, $partnerId, $puserId);
	    if ($vvote)
	    {
	        $vvote->setStatus(VVoteStatus::VOTED);
	        $affectedLines = $vvote->save();
	    }
	    
	    return isset($affectedLines) ? $affectedLines : 0;
	}
	
    public static function disableExistingVVote ($entryId, $partnerId, $puserId)
	{
	    $vvote = self::doSelectByEntryIdAndPuserId($entryId, $partnerId, $puserId);
	    if ($vvote)
	    {
            $vvote->setStatus(VVoteStatus::REVOKED);
    	    $affectedLines = $vvote->save();
	    }
	    
	    return isset($affectedLines) ? $affectedLines : 0;
	    
	}
	
	public static function createVvote ($entryId, $partnerId, $puserId, $rank, $type=VVoteType::RANK)
	{
	    $vvote = new vvote();
		$vvote->setEntryId($entryId);
		$vvote->setStatus(VVoteStatus::VOTED);
		$vvote->setPartnerId($partnerId);
		$vvote->setVvoteType($type);
		$vuser = self::getVuserFromPuserAndPartner($puserId, $partnerId);
		if (!$vuser)
		{
		    $vuser = new vuser();
		    $vuser->setPuserId($puserId);
		    $vuser->setStatus(VuserStatus::ACTIVE);
		    $vuser->save();
		}
		$vvote->setPuserId($puserId);
		$vvote->setVuserId($vuser->getId());
		$vvote->setRank($rank);
		$vvote->save();
	}
}
