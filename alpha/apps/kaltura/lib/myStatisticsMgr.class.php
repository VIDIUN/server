<?php
class myStatisticsMgr
{
	private static $s_dirty_objects = array();

	public static function incVuserViews ( vuser $vuser , $delta = 1 )
	{
		$v = $vuser->getViews();
		if ( self::shouldModify ( $vuser , vuserPeer::VIEWS ) );
		{
			self::inc ( $v , $delta);
			$vuser->setViews( $v );
		}
		return $v;
	}

	// will increment either fans or favorites for vshow or entry according to favorite.subject_type
	/**
	const SUBJECT_TYPE_VSHOW = '1';
	const SUBJECT_TYPE_ENTRY = '2';
	const SUBJECT_TYPE_USER = '3';

	*/
	public static function addFavorite ( favorite $favorite )
	{
		self::add ( $favorite );

		$type = $favorite->getSubjectType();
		$id = $favorite->getSubjectId();
		if ( $type == favorite::SUBJECT_TYPE_ENTRY )
		{
			$obj = entryPeer::retrieveByPK( $id );
			if ( $obj ) 
			{
				$v = $obj->getFavorites () 	;
				self::inc ( $v );
				$obj->setFavorites ( $v );
			}
		}
		elseif ( $type == favorite::SUBJECT_TYPE_VSHOW )
		{
			$obj = vshowPeer::retrieveByPK( $id );
			if ( $obj )
			{			
				$v = $obj->getFavorites () 	;
				self::inc ( $v );
				$obj->setFavorites ( $v );
			}
		}
		elseif (  $type == favorite::SUBJECT_TYPE_USER )
		{
			$obj = vuserPeer::retrieveByPK( $id );
			if ( $obj )
			{
				$v = $obj->getFans () 	;
				self::inc ( $v );
				$obj->setFans ( $v );
			}
		}
		// don't forget to save the modified object
		self::add ( $obj );
	}

	//- will increment vuser.entries, vshow.entries & vshow.contributors
	public static function addEntry ( entry $entry )
	{
		return;
		/*$vshow = $entry->getvshow();
		if ( $vshow )
		{
			$v = $vshow->getEntries();
			self::inc ( $v );
			$vshow->setEntries ( $v );
		}

		$c = new Criteria();
		myCriteria::addComment( $c , __METHOD__  );
		$c->add ( entryPeer::VSHOW_ID , $entry->getVshowId() );
		$c->add ( entryPeer::VUSER_ID , $entry->getVuserId() );
		$c->setLimit ( 2 );
		$res = entryPeer::doCount( $c );
		if ( $res < 1 && $vshow != null )
		{
			// vuser didn't contribute to this vshow - increment
			$v = $vshow->getContributors();
			self::inc ( $v );
			$vshow->setContributors( $v );
		}

		$vuser = $entry->getvuser();
		if ( $vuser )
		{
			$v = $vuser->getEntries();
			self::inc ( $v );
			$vuser->setEntries ( $v );
		}

		self::add ( $vshow );
		self::add ( $vuser );*/
	}

	//- will increment vuser.entries, vshow.entries & vshow.contributors
	public static function deleteEntry ( entry $entry )
	{
		return;
		/*$vshow = $entry->getvshow();
		if ($vshow)
		{
			$v = $vshow->getEntries();
			self::dec ( $v );
			$vshow->setEntries ( $v );
	
			$c = new Criteria();
			myCriteria::addComment( $c , __METHOD__  );
			$c->add ( entryPeer::VSHOW_ID , $entry->getVshowId() );
			$c->add ( entryPeer::VUSER_ID , $entry->getVuserId() );
			$c->setLimit ( 2 );
			$res = entryPeer::doCount( $c );
			if ( $res == 1 )
			{
				// if $res > 1 -  this vuser contributed more than one entry, deleting this one should still leave him a contributor 
				// if $res < 1 -  this vuser never contributed - strange! but no need to dec the contributors
				// vuser did contribute to this vshow - decrement
				$v = $vshow->getContributors();
				self::dec ( $v );
				$vshow->setContributors( $v );
			}
	
			$vuser = $entry->getvuser();
			if ( $vuser )
			{
				$v = $vuser->getEntries();
				self::dec ( $v );
				$vuser->setEntries ( $v );
			}
	
			self::add ( $vshow );
			self::add ( $vuser );
		}*/
	}
	
	//- will increment vuser.produced_vshows
	public static function addVshow ( vshow $vshow )
	{
		$vuser = $vshow->getVuser();
		// this might happen when creating a temp vshow without setting its producer 
		if ( $vuser == NULL ) return;
		
		$v = $vuser->getProducedVshows ();
		self::inc ( $v );
		$vuser->setProducedVshows ( $v );
		self::add ( $vuser );
	}

	//- will decrement vuser.produced_vshows
	public static function deleteVshow ( vshow $vshow )
	{
		$vuser = $vshow->getVuser();
		// this might happen when creating a temp vshow without setting its producer 
		if ( $vuser == NULL ) return;
		
		$v = $vuser->getProducedVshows ();
		self::dec( $v );
		$vuser->setProducedVshows ( $v );
		self::add ( $vuser );
	}
		
	public static function incVshowViews ( vshow $vshow , $delta = 1 )
	{
		$v = $vshow->getViews();
		if ( self::shouldModify ( $vshow , vshowPeer::VIEWS ) );
		{
			self::inc ( $v , $delta);
			$vshow->setViews( $v );
		}
		return $v;
	}

	public static function incVshowPlays ( vshow $vshow , $delta = 1 )
	{
		$v = $vshow->getPlays();
		
VidiunLog::log ( __METHOD__ . ": " . $vshow->getId() . " plays: $v");
 
		if ( self::shouldModify ( $vshow , vshowPeer::PLAYS ) );
		{
			self::inc ( $v , $delta);
			$vshow->setPlays( $v );
		}
		
VidiunLog::log ( __METHOD__ . ": " . $vshow->getId() . " plays: $v");		
		return $v;
	}
	
/*	
	// - do we vote for vshows ??? - this should be derived from the roughcut
	public static function incVshowVotes ( vshow $vshow )
	{
	}
*/

	// - will increment vshow.comments or entry.comments according to comment_type
	/**
	* 	const COMMENT_TYPE_VSHOW = 1;
	const COMMENT_TYPE_DISCUSSION = 2;
	const COMMENT_TYPE_USER = 3;
	const COMMENT_TYPE_SHOUTOUT = 4;
	*
	*/
	public static function addComment ( comment $comment )
	{
		$obj = NULL;
		$type = $comment->getCommentType();
		$id = $comment->getSubjectId();
		if ( $type == comment::COMMENT_TYPE_VSHOW || 
			$type == comment::COMMENT_TYPE_SHOUTOUT ||
			$type == comment::COMMENT_TYPE_DISCUSSION )
		{
			$obj = vshowPeer::retrieveByPK( $id );
			if ( $obj )
			{
				$v = $obj->getComments () 	;
				self::inc ( $v );
				$obj->setComments ( $v );
			}
		}
		elseif ( $type == comment::COMMENT_TYPE_USER )
		{
/*			$obj = vuserPeer::retrieveByPK( $id );
			$v = $obj->getComments () 	;
			self::inc ( $v );
			$obj->setComments ( $v );
*/
		}

		// TODO - what about the other types ?
		if ( $obj != NULL )	self::add ( $obj );
	}

	public static function addSubscriber ( VshowVuser $vushow_vuser )
	{
		$type = $vushow_vuser->getAlertType();

		if ( $type == VshowVuser::VSHOW_SUBSCRIPTION_NORMAL )
		{
			$vshow = $vushow_vuser->getvshow();
			if ( $vshow )
			{
				$v = $vshow->getSubscribers() 	;
				self::inc ( $v );
				$vshow->setSubscribers ( $v );
			}

			self::add ( $vshow );
		}
	}

	// - will increment vshow.number_of_updates
	public static function incVshowUpdates ( vshow $vshow, $delta = 1 )
	{
		$v = $vshow->getNumberOfUpdates();
		if ( self::shouldModify( $vshow , vshowPeer::NUMBER_OF_UPDATES ) )
		{
			self::inc ( $v , $delta);
			$vshow->setNumberOfUpdates( $v );
		}
		return $v;
	}

	public static function incEntryViews ( entry $entry , $delta = 1 )
	{
		$v = $entry->getViews();
		if ( $delta == 0 ) return $v;
		if ( self::shouldModify ( $entry , entryPeer::VIEWS ) );
		{
			self::inc ( $v , $delta);
			$entry->setViews( $v );
		}
		
		if ( $entry->getType() == entryType::MIX )
		{
			$enclosing_vshow = $entry->getVshow();
			if ( $enclosing_vshow  )
			{
				$vshow_views = $enclosing_vshow->getViews() ;
				$enclosing_vshow->setViews ( ++$vshow_views );
				self::add( $enclosing_vshow );
			}
		}		
		return $v;
	}


	public static function incEntryPlays ( entry $entry , $delta = 1 )
	{
		$v = $entry->getPlays();
		if ( $delta == 0 ) return $v;
		if ( self::shouldModify ( $entry , entryPeer::PLAYS ) );
		{
			self::inc ( $v , $delta);
			$entry->setPlays( $v );
		}
		
		if ( $entry->getType() == entryType::MIX )
		{
			$enclosing_vshow = $entry->getVshow();
			if ( $enclosing_vshow  )
			{
				$vshow_views = $enclosing_vshow->getPlays() ;
				$enclosing_vshow->setPlays ( ++$vshow_views );
				self::add( $enclosing_vshow );
			}
		}		
		return $v;
	}
	
	
	public static function addVvote ( vvote $vvote )
	{
		$entry = $vvote->getEntry();
		$res = self::modifyEntryVotes($entry, $vvote->getRank(), VVoteStatus::VOTED);
		return $res; 
	}
	
    public static function modifyEntryVotesByvVote (vvote $vvote)
	{
		$entry = $vvote->getEntry();
		$res = self::modifyEntryVotes($entry, $vvote->getRank(), $vvote->getStatus());
		return $res; 
	}

	// - will update votes , total_rank & rank
	// if the ebtry is of type roughcut -0 will update the vshow's rank too
	private static function modifyEntryVotes ( entry $entry , $delta_rank, $vvoteStatus )
	{
		$res = array();
		
		$votes = $entry->getVotes();
		if ( self::shouldModify ( $entry , entryPeer::VOTES ) );
		{
		    if ($vvoteStatus == VVoteStatus::VOTED)
			    self::inc ($votes);
			else 
			    self::dec($votes);
			$entry->setVotes( $votes );
				
			$total_rank = $entry->getTotalRank();
			if ($vvoteStatus == VVoteStatus::VOTED)
			    self::inc ($total_rank, $delta_rank);
			else 
			    self::dec($total_rank, $delta_rank);
			$entry->setTotalRank( $total_rank );
				
			$res ["entry"] = $entry;
			// can assume $votes > 0
			$rank = $entry->setRank ( ( $total_rank / $votes ) * 1000 );
				
			// if rouhcut - update the vshow's rank too
			if ( $entry->getType() == entryType::MIX )
			{
				$enclosing_vshow = $entry->getVshow();
				if ( $enclosing_vshow  )
				{
					$vshow_votes = $enclosing_vshow->getVotes() ;
					$enclosing_vshow->setVotes ( ++$vshow_votes );
					if ( true ) //if ( $enclosing_vshow->getRank() <  $entry->getRank() ) // rank the show 
					{
						$enclosing_vshow->setRank ( $entry->getRank() );
						self::add( $enclosing_vshow );
						$res ["vshow"] = $enclosing_vshow;
					}
				}
			}
		}
		return $res;
	}


	// TODO - might be duplicates in the list- try to avoid redundant saves
	// (although won't commit to DB because there will be no internal dirty flags)
	public static function saveAllModified ()
	{
		foreach ( self::$s_dirty_objects as $id => $dirty_obj )
		{
			self::log ( "saving: [$id]" );
			$dirty_obj->save();
		}
		
		// free all the object - create a new empty array
		self::$s_dirty_objects = array();
	}

	private static function shouldModify ( BaseObject $baseObject , $col )
	{
		if ( ! $baseObject->isColumnModified($col ) )
		{
			self::add ( $baseObject );
			return true;
		}

		// this object should not be updated twice
		return false;
	}

	private static function add ( /*BaseObject*/ $baseObject )
	{
		if ( $baseObject != null )
		{
			$id = get_class ( $baseObject ) . $baseObject->getId();
			self::log ( "adding: [$id]" );
			self::$s_dirty_objects[$id] = $baseObject;
		}
	}

	private static function inc ( &$num , $delta = 1 )
	{
		if ( ! is_numeric ( $num )) $num = 0;
		$num += $delta;
	}
	
	private static function dec ( &$num , $delta = 1 )
	{
		if ( ! is_numeric ( $num )) $num = 0;
		$num -= $delta;
		
		if($num < 0)
			$num = 0;
	}	
	
	
	private static function log ( $str )
	{
	}
}
?>