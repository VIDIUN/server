<?php
/**
 * Playlist service lets you create,manage and play your playlists
 * Playlists could be static (containing a fixed list of entries) or dynamic (based on a filter)
 *
 * @service playlist
 *
 * @package api
 * @subpackage services
 */
class PlaylistService extends VidiunEntryService
{
	/* (non-PHPdoc)
	 * @see VidiunBaseService::globalPartnerAllowed()
	 */
	protected function vidiunNetworkAllowed($actionName)
	{
		if ($actionName === 'executeFromContent') {
			return true;
		}
		if ($actionName === 'executeFromFilters') {
			return true;
		}
		if ($actionName === 'getStatsFromContent') {
			return true;
		}
		return parent::vidiunNetworkAllowed($actionName);
	}

	protected function partnerRequired($actionName)
	{
		if ($actionName === 'executeFromContent') {
			return false;
		}
		if ($actionName === 'executeFromFilters') {
			return false;
		}
		if ($actionName === 'execute') {
			return false;
		}
		return parent::partnerRequired($actionName);
	}
	
	/**
	 * Add new playlist
	 * Note that all entries used in a playlist will become public and may appear in VidiunNetwork
	 *
	 * @action add
	 * @param VidiunPlaylist $playlist
	 * @param bool $updateStats indicates that the playlist statistics attributes should be updated synchronously now
	 * @return VidiunPlaylist
	 *
	 * @disableRelativeTime $playlist
	 */
	function addAction( VidiunPlaylist $playlist , $updateStats = false)
	{
		$dbPlaylist = $playlist->toInsertableObject();

		$this->checkAndSetValidUserInsert($playlist, $dbPlaylist);
		$this->checkAdminOnlyInsertProperties($playlist);
		$this->validateAccessControlId($playlist);
		$this->validateEntryScheduleDates($playlist, $dbPlaylist);
		
		$dbPlaylist->setPartnerId ( $this->getPartnerId() );
		$dbPlaylist->setStatus ( entryStatus::READY );
		$dbPlaylist->setVshowId ( null ); // this is brave !!
		$dbPlaylist->setType ( entryType::PLAYLIST );

		myPlaylistUtils::validatePlaylist($dbPlaylist);

		$dbPlaylist->save();
		
		if ( $updateStats )
			myPlaylistUtils::updatePlaylistStatistics( $dbPlaylist->getPartnerId() , $dbPlaylist );
		
		$trackEntry = new TrackEntry();
		$trackEntry->setEntryId($dbPlaylist->getId());
		$trackEntry->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$trackEntry->setDescription(__METHOD__ . ":" . __LINE__ . "::ENTRY_PLAYLIST");
		TrackEntry::addTrackEntry($trackEntry);
		
		$playlist = new VidiunPlaylist(); // start from blank
		$playlist->fromObject($dbPlaylist, $this->getResponseProfile());
		
		return $playlist;
	}
	

	/**
	 * Retrieve a playlist
	 *
	 * @action get
	 * @param string $id
	 * @param int $version Desired version of the data
	 * @return VidiunPlaylist
	 *
	 * @throws APIErrors::INVALID_ENTRY_ID
	 * @throws APIErrors::INVALID_PLAYLIST_TYPE
	 */
	function getAction( $id, $version = -1 )
	{
		$dbPlaylist = entryPeer::retrieveByPK( $id );
		
		if ( ! $dbPlaylist )
			throw new VidiunAPIException ( APIErrors::INVALID_ENTRY_ID , "Playlist" , $id  );
		if ( $dbPlaylist->getType() != entryType::PLAYLIST )
			throw new VidiunAPIException ( APIErrors::INVALID_PLAYLIST_TYPE );
			
		if ($version !== -1)
			$dbPlaylist->setDesiredVersion($version);
			
		$playlist = new VidiunPlaylist(); // start from blank
		$playlist->fromObject($dbPlaylist, $this->getResponseProfile());
		
		return $playlist;
	}

	/**
	 * Update existing playlist
	 * Note - you cannot change playlist type. Updated playlist must be of the same type.
	 *
	 * @action update
	 * @param string $id
	 * @param VidiunPlaylist $playlist
	 * @param bool $updateStats
	 * @return VidiunPlaylist
	 * @throws VidiunAPIException
	 * @validateUser entry id edit
	 *
	 * @disableRelativeTime $playlist
	 */
	function updateAction($id , VidiunPlaylist $playlist , $updateStats = false )
	{
		$dbPlaylist = entryPeer::retrieveByPK($id);

		if(!$dbPlaylist)
		{
			throw new VidiunAPIException (APIErrors::INVALID_ENTRY_ID, "Playlist", $id);
		}

		if ($dbPlaylist->getType() != entryType::PLAYLIST )
		{
			throw new VidiunAPIException (APIErrors::INVALID_PLAYLIST_TYPE);
		}

		$dbPlaylist = $playlist->toUpdatableObject($dbPlaylist);
		$this->checkAndSetValidUserUpdate($playlist, $dbPlaylist);
		$this->checkAdminOnlyUpdateProperties($playlist);
		$this->validateAccessControlId($playlist);
		$this->validateEntryScheduleDates($playlist, $dbPlaylist);

		if(!is_null($dbPlaylist->getDataContent(true)))
		{
			myPlaylistUtils::validatePlaylist($dbPlaylist);
		}

		if ( $updateStats )
		{
			myPlaylistUtils::updatePlaylistStatistics($this->getPartnerId(), $dbPlaylist);//, $extra_filters , $detailed );
		}

		$dbPlaylist->save();
		$playlist->fromObject($dbPlaylist, $this->getResponseProfile());
		return $playlist;
	}

	/**
	 * Delete existing playlist
	 *
	 * @action delete
	 * @param string $id
	 *
	 * @throws APIErrors::INVALID_ENTRY_ID
	 * @throws APIErrors::INVALID_PLAYLIST_TYPE
	 * @validateUser entry id edit
	 */
	function deleteAction($id)
	{
		if (!vPermissionManager::isPermitted(PermissionName::PLAYLIST_DELETE))
		{
			$entry = entryPeer::retrieveByPK($id);
			if(!$entry || $entry->getMediaType() != VidiunPlaylistType::STATIC_LIST)
			{
				throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_MEDIA_TYPE, $id, $entry->getMediaType(), VidiunPlaylistType::STATIC_LIST);
			}
		}

		$this->deleteEntry($id, VidiunEntryType::PLAYLIST);
	}
	
	
	/**
	 * Clone an existing playlist
	 *
	 * @action clone
	 * @param string $id  Id of the playlist to clone
	 * @param VidiunPlaylist $newPlaylist Parameters defined here will override the ones in the cloned playlist
	 * @return VidiunPlaylist
	 *
	 * @throws APIErrors::INVALID_ENTRY_ID
	 * @throws APIErrors::INVALID_PLAYLIST_TYPE
	 */
	function cloneAction( $id, VidiunPlaylist $newPlaylist = null)
	{
		$dbPlaylist = entryPeer::retrieveByPK( $id );
		
		if ( !$dbPlaylist )
			throw new VidiunAPIException ( APIErrors::INVALID_ENTRY_ID , "Playlist" , $id  );
			
		if ( $dbPlaylist->getType() != entryType::PLAYLIST )
			throw new VidiunAPIException ( APIErrors::INVALID_PLAYLIST_TYPE );
			
		if ($newPlaylist->playlistType && ($newPlaylist->playlistType != $dbPlaylist->getMediaType()))
			throw new VidiunAPIException ( APIErrors::CANT_UPDATE_PARAMETER, 'playlistType' );
		
		$oldPlaylist = new VidiunPlaylist();
		$oldPlaylist->fromObject($dbPlaylist, $this->getResponseProfile());
			
		if (!$newPlaylist) {
			$newPlaylist = new VidiunPlaylist();
		}
		
		$reflect = new ReflectionClass($newPlaylist);
		$props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop) {
			$propName = $prop->getName();
			// do not override new parameters
			if ($newPlaylist->$propName) {
				continue;
			}
			// do not copy read only parameters
			if (stristr($prop->getDocComment(), '@readonly')) {
				continue;
			}
			// copy from old to new
			$newPlaylist->$propName = $oldPlaylist->$propName;
		}

		// add the new entry
		return $this->addAction($newPlaylist, true);
	}
	
	/**
	 * List available playlists
	 *
	 * @action list
	 * @param VidiunPlaylistFilter // TODO
	 * @param VidiunFilterPager $pager
	 * @return VidiunPlaylistListResponse
	 */
	function listAction( VidiunPlaylistFilter $filter=null, VidiunFilterPager $pager=null )
	{
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;
		

	    if (!$filter)
			$filter = new VidiunPlaylistFilter();
			
	    $filter->typeEqual = VidiunEntryType::PLAYLIST;
	    list($list, $totalCount) = parent::listEntriesByFilter($filter, $pager);
	    
	    $newList = VidiunPlaylistArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunPlaylistListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	
	/**
	 * Retrieve playlist for playing purpose
	 * @disableTags TAG_WIDGET_SESSION
	 *
	 * @action execute
	 * @param string $id
	 * @param string $detailed
	 * @param VidiunContext $playlistContext
	 * @param VidiunMediaEntryFilterForPlaylist $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunBaseEntryArray
	 * @vsOptional
	 */
	function executeAction( $id , $detailed = false, VidiunContext $playlistContext = null, $filter = null, $pager = null )
	{
		myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;

		if (in_array($id, vConf::get('partner_0_static_playlists', 'local', array())))
				$playlist = entryPeer::retrieveByPKNoFilter($id);
		else
			$playlist = entryPeer::retrieveByPK($id);

		if (!$playlist)
			throw new VidiunAPIException ( APIErrors::INVALID_ENTRY_ID , "Playlist" , $id  );

		if ($playlist->getType() != entryType::PLAYLIST)
			throw new VidiunAPIException ( APIErrors::INVALID_PLAYLIST_TYPE );

		$entryFilter = null;
		if ($filter)
		{
			$coreFilter = new entryFilter();
			$filter->toObject($coreFilter);
			$entryFilter = $coreFilter;
		}
			
		if ($this->getVs() && is_object($this->getVs()) && $this->getVs()->isAdmin())
			myPlaylistUtils::setIsAdminVs(true);

	    $corePlaylistContext = null;
	    if ($playlistContext)
	    {
	        $corePlaylistContext = $playlistContext->toObject();
	        myPlaylistUtils::setPlaylistContext($corePlaylistContext);
	    }
	    
		// the default of detrailed should be true - most of the time the kuse is needed
		if (is_null($detailed))
			 $detailed = true ;

		try
		{
			$entryList = myPlaylistUtils::executePlaylist( $this->getPartnerId() , $playlist , $entryFilter , $detailed, $pager);
		}
		catch (vCoreException $ex)
		{   		
			throw $ex;
		}

		myEntryUtils::updatePuserIdsForEntries ( $entryList );
			
		return VidiunBaseEntryArray::fromDbArray($entryList, $this->getResponseProfile());
	}
	

	/**
	 * Retrieve playlist for playing purpose, based on content
	 * @disableTags TAG_WIDGET_SESSION
	 *
	 * @action executeFromContent
	 * @param VidiunPlaylistType $playlistType
	 * @param string $playlistContent
	 * @param string $detailed
	 * @param VidiunFilterPager $pager
	 * @return VidiunBaseEntryArray
	 */
	function executeFromContentAction($playlistType, $playlistContent, $detailed = false, $pager = null)
	{
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;
	    
		if ($this->getVs() && is_object($this->getVs()) && $this->getVs()->isAdmin())
			myPlaylistUtils::setIsAdminVs(true);

		$entryList = array();
		if ($playlistType == VidiunPlaylistType::DYNAMIC)
			$entryList = myPlaylistUtils::executeDynamicPlaylist($this->getPartnerId(), $playlistContent, null, true, $pager);
		else if ($playlistType == VidiunPlaylistType::STATIC_LIST)
			$entryList = myPlaylistUtils::executeStaticPlaylistFromEntryIdsString($playlistContent, null, true, $pager);
			
		myEntryUtils::updatePuserIdsForEntries($entryList);
		
		return VidiunBaseEntryArray::fromDbArray($entryList, $this->getResponseProfile());
	}
	
	/**
	 * Retrieve playlist for playing purpose, based on media entry filters
	 * @disableTags TAG_WIDGET_SESSION
	 * @action executeFromFilters
	 * @param VidiunMediaEntryFilterForPlaylistArray $filters
	 * @param int $totalResults
	 * @param string $detailed
	 * @param VidiunFilterPager $pager
	 * @return VidiunBaseEntryArray
	 */
	function executeFromFiltersAction(VidiunMediaEntryFilterForPlaylistArray $filters, $totalResults, $detailed = true, $pager = null)
	{
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;
	    
		$tempPlaylist = new VidiunPlaylist();
		$tempPlaylist->playlistType = VidiunPlaylistType::DYNAMIC;
		$tempPlaylist->filters = $filters;
		$tempPlaylist->totalResults = $totalResults;
		$tempPlaylist->filtersToPlaylistContentXml();
		return $this->executeFromContentAction($tempPlaylist->playlistType, $tempPlaylist->playlistContent, true, $pager);
	}
	
	
	/**
	 * Retrieve playlist statistics
	 * @deprecated
	 * @action getStatsFromContent
	 * @param VidiunPlaylistType $playlistType
	 * @param string $playlistContent
	 * @return VidiunPlaylist
	 */
	function getStatsFromContentAction( $playlistType , $playlistContent )
	{
		die;

	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL3;
	    
		$dbPlaylist = new entry();
		$dbPlaylist->setId( -1 ); // set with some dummy number so the getDataContent will later work properly
		$dbPlaylist->setType ( entryType::PLAYLIST ); // prepare the playlist type before filling from request
		$dbPlaylist->setMediaType ( $playlistType );
		$dbPlaylist->setDataContent( $playlistContent );
				
		myPlaylistUtils::updatePlaylistStatistics ( $this->getPartnerId() , $dbPlaylist );//, $extra_filters , $detailed );
		
		$playlist = new VidiunPlaylist(); // start from blank
		$playlist->fromObject($dbPlaylist, $this->getResponseProfile());
		
		return $playlist;
	}
	

}
