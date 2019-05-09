<?php
/**
 * Subclass for performing query and update operations on the 'vshow' table.
 *
 *
 *
 * @package Core
 * @subpackage model
 */
class vshowPeer extends BasevshowPeer 
{
	private static $s_default_count_limit = 301;

	/**
	 * This function sets the requested order of vshows to the given criteria object.
	 * we can use an associative array to hold the ordering fields instead of the
	 * switch statement being used now
	 *
	 * @param $c = given criteria object
	 * @param int = $order the requested sort order
	 */
	private static function setOrder($c, $order)
	{
		switch ($order) {
		case vshow::VSHOW_SORT_MOST_VIEWED:
			//$c->hints = array(vshowPeer::TABLE_NAME => "views_index");
			$c->addDescendingOrderByColumn(self::VIEWS);

			break;

		case vshow::VSHOW_SORT_MOST_RECENT:
			//$c->hints = array(vshowPeer::TABLE_NAME => "created_at_index");
			$c->addDescendingOrderByColumn(self::CREATED_AT);
			break;

		case vshow::VSHOW_SORT_MOST_COMMENTS:
			$c->addDescendingOrderByColumn(self::COMMENTS);
			break;

		case vshow::VSHOW_SORT_MOST_FAVORITES:
			$c->addDescendingOrderByColumn(self::FAVORITES);
			break;

		case vshow::VSHOW_SORT_END_DATE:
			$c->addDescendingOrderByColumn(self::END_DATE);
			break;

		case vshow::VSHOW_SORT_MOST_ENTRIES:
			$c->addDescendingOrderByColumn(self::ENTRIES);
			break;

		case vshow::VSHOW_SORT_NAME:
			$c->addAscendingOrderByColumn(self::NAME);
			break;

		case vshow::VSHOW_SORT_RANK:
			$c->addDescendingOrderByColumn(self::RANK);
			break;
		case vshow::VSHOW_SORT_MOST_UPDATED:
			$c->addDescendingOrderByColumn(self::UPDATED_AT);
			break;
		case vshow::VSHOW_SORT_MOST_CONTRIBUTORS:
			$c->addDescendingOrderByColumn(self::CONTRIBUTORS);
			break;

		}
	}

	/**
	 * This function returns a pager object holding vshows sorted by a given sort order.
	 * each vshow holds the vuser object of its host.
	 *
	 * @param int $order = the requested sort order
	 * @param int $pageSize = number of vshows in each page
	 * @param int $page = the requested page
	 * @return the pager object
	 */
	public static function getOrderedPager($order, $pageSize, $page, $producer_id = null, $vidiun_part_of_flag = null )
	{
		$c = new Criteria();
		self::setOrder($c, $order);

		$c->addJoin(self::PRODUCER_ID, vuserPeer::ID, Criteria::INNER_JOIN);

		if( $vidiun_part_of_flag )
		{
			// in this case we get the user-id in the $producer_id field
			$c->addJoin(self::ID, entryPeer::VSHOW_ID, Criteria::INNER_JOIN);
			$c->add(entryPeer::VUSER_ID, $producer_id);
			$c->add( self::PRODUCER_ID, $producer_id, Criteria::NOT_EQUAL );
			$c->setDistinct();
		}
		else if( $producer_id > 0 ) $c->add( self::PRODUCER_ID, $producer_id );

		$pager = new sfPropelPager('vshow', $pageSize);
	    $pager->setCriteria($c);
	    $pager->setPage($page);
	    $pager->setPeerMethod('doSelectJoinAll');
	    $pager->setPeerCountMethod('doCountJoinAll');
	    $pager->init();

	    return $pager;
	}

	public static function getVshowsByName( $name )
	{
		$c = new Criteria();
		$c->add ( vshowPeer::NAME , $name );
		return vshowPeer::doSelect( $c );
	}

	public static function getFirstVshowByName( $name )
	{
		$vshows = self::getVshowsByName ( $name );
		if( $vshows != null )
			return $vshows[0];
		return null;
	}

	public static function retrieveByIndexedCustomData3 ( $name )
	{
		$c = new Criteria();
		$c->add ( vshowPeer::INDEXED_CUSTOM_DATA_3 , $name );
		$vshows =  vshowPeer::doSelect( $c );
		if( $vshows != null )
			return $vshows[0];
		return null;
	}

	/**
	 * This function returns a pager object holding the given user's favorite entries
	 * each entry holds the vuser object of its host.
	 *
	 * @param int $vuserId = the requested user
	 * @param int $type = the favorite type (currently only SUBJECT_TYPE_ENTRY will match)
	 * @param int $privacy = the privacy filter
	 * @param int $pageSize = number of vshows in each page
	 * @param int $page = the requested page
	 * @return the pager object
	 */
	public static function getUserFavorites($vuserId, $type, $privacy, $pageSize, $page)
	{
		$c = new Criteria();
		$c->addJoin(self::PRODUCER_ID, vuserPeer::ID, Criteria::INNER_JOIN);
		$c->addJoin(self::ID, favoritePeer::SUBJECT_ID, Criteria::INNER_JOIN);
		$c->add(favoritePeer::VUSER_ID, $vuserId);
		$c->add(favoritePeer::SUBJECT_TYPE, $type);
		$c->add(favoritePeer::PRIVACY, $privacy);

		$c->setDistinct();

		// our assumption is that a request for private favorites should include public ones too
		if( $privacy == favorite::PRIVACY_TYPE_USER )
		{
			$c->addOr( favoritePeer::PRIVACY, favorite::PRIVACY_TYPE_WORLD );
		}


		$c->addAscendingOrderByColumn(self::NAME);

	    $pager = new sfPropelPager('vshow', $pageSize);
	    $pager->setCriteria($c);
	    $pager->setPage($page);
	    $pager->setPeerMethod('doSelectJoinvuser');
	    $pager->setPeerCountMethod('doCountJoinvuser');
	    $pager->init();

	    return $pager;
	}



	/**
	 * This function returns a pager object holding the given user's shows, for which he or she is the producer.
	 *
	 * @param int $vuserId = the requested user
	 * @param int $pageSize = number of vshows in each page
	 * @param int $page = the requested page
	 * @param int $order = the requested sort order
	 * @param int $currentVshowId = the current vshow id (e.g. in the browse page) not to be shown again in the other user shows
	 * @return the pager object
	 */
	public static function getUserShows($vuserId, $pageSize, $page, $order, $currentVshowId = 0)
	{

		$c = new Criteria();

		$c->addJoin(self::PRODUCER_ID, vuserPeer::ID, Criteria::INNER_JOIN);
		$c->add(self::PRODUCER_ID, $vuserId);
		if ($currentVshowId)
			$c->add(self::ID, $currentVshowId, Criteria::NOT_EQUAL);

		self::setOrder($c, $order);

	    $pager = new sfPropelPager('vshow', $pageSize);
	    $pager->setCriteria($c);
	    $pager->setPage($page);
	    $pager->setPeerMethod('doSelectJoinvuser');
	    $pager->setPeerCountMethod('doCountJoinvuser');
	    $pager->init();

	    return $pager;
	}


		/**
	 * This function returns a pager object holding the set of shows for which given user contributed media.
	 *
	 * @param int $vuserId = the requested user
	 * @param int $pageSize = number of vshows in each page
	 * @param int $page = the requested page
	 * @return the pager object
	 */
	public static function getUserShowsPartOf($vuserId, $pageSize, $page, $order)
	{

		$c = new Criteria();

		$c->addJoin(self::ID, entryPeer::VSHOW_ID, Criteria::INNER_JOIN);
		$c->add(entryPeer::VUSER_ID, $vuserId);
		$c->add( self::PRODUCER_ID, $vuserId, Criteria::NOT_EQUAL );
		self::setOrder($c, $order);
		$c->setDistinct();

	    $pager = new sfPropelPager('vshow', $pageSize);
	    $pager->setCriteria($c);
	    $pager->setPage($page);
	    $pager->setPeerMethod('doSelectJoinvuser');
	    $pager->setPeerCountMethod('doCountJoinvuser');
	    $pager->init();

	    return $pager;
	}


	public static function selectIdsForCriteria ( Criteria $c )
	{
		$c->addSelectColumn(self::ID);
		$rs = self::doSelectStmt($c);
		$id_list = Array();

		while($rs->next())
		{
			$id_list[] = $rs->getInt(1);
		}

		$rs->close();

		return $id_list;
	}

	public static function getVshowsByEntryIds($entry_ids)
	{
		$c = new Criteria();
		//$c->addSelectColumn(vshowPeer::ID);
		//$c->addSelectColumn(vshowPeer::NAME);
		vshowPeer::addSelectColumns($c);
		$c->addJoin(vshowPeer::ID, roughcutEntryPeer::ROUGHCUT_VSHOW_ID);
		$c->add(roughcutEntryPeer::ENTRY_ID, $entry_ids, Criteria::IN);
		$results = vshowPeer::populateObjects(self::doSelectStmt($c));
		vshowPeer::addInstancesToPool($results);
		return $results;
	}

	// this function deletes a VSHOW
	// users can only delete their own entries
	public static function deleteVShow( $vshow_id, $vuser_id  )
	{
		$vshow = self::retrieveByPK( $vshow_id );
		if( $vshow == null ) return false;
		if( $vshow->getProducerId() != $vuser_id ) return false;
		else
		{
			$vshow->delete();

			// now delete the subscriptions
			$c = new Criteria();
			$c->add(VshowVuserPeer::VSHOW_ID, $vshow_id ); // the current user knows they just favorited
			$c->add(VshowVuserPeer::SUBSCRIPTION_TYPE, VshowVuser::VSHOW_SUBSCRIPTION_NORMAL); // this table stores other relations too
			$subscriptions = VshowVuserPeer::doSelect( $c );
			foreach ( $subscriptions as $subscription )
			{
					$subscription->delete();
			}

			return true;
		}

	}

	public static function doCountWithLimit (Criteria $criteria, $distinct = false, $con = null)
	{
		$criteria = clone $criteria;
		$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn("DISTINCT ".vshowPeer::ID);
		} else {
			$criteria->addSelectColumn(vshowPeer::ID);
		}

		$criteria->setLimit( self::$s_default_count_limit );

		$rs = self::doSelectStmt($criteria, $con);
		$count = 0;
		while($rs->next())
			$count++;

		return $count;
	}

	public static function doStubCount (Criteria $criteria, $distinct = false, $con = null)
	{
		return 0;
	}	
	
	/**
	 * Retrieve a single object by pkey.
	 *
	 * @param      string $pk the primary key.
	 * @param      PropelPDO $con the connection to use
	 * @return     vshow
	 */
	public static function retrieveByPKNoFilter($pk, PropelPDO $con = null)
	{
		self::setUseCriteriaFilter ( false );
		$res = self::retrieveByPK($pk, $con);
		self::setUseCriteriaFilter ( true );
		return $res;
	}
	
}
