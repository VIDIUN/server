<?php
/**
 * @package Core
 * @subpackage ExternalServices
 */
class myVidiunVshowServices extends myBaseMediaSource implements IMediaSource
{
	const VIDIUN_SERVICE_CRITERIA_FROM_VSHOW = 1;
	const VIDIUN_SERVICE_CRITERIA_FROM_ROUGHCUT = 2;
	
	static $s_default_count_limit = 100;
	
	const AUTH_SALT = "myVidiunServices:gogog123";
	const AUTH_INTERVAL = 3600;
	
	const MAX_PAGE_SIZE = 30;
	
	protected $supported_media_types = 7; // support all media//self::SUPPORT_MEDIA_TYPE_VIDEO + (int)self::SUPPORT_MEDIA_TYPE_IMAGE;  
	protected $source_name = "Vidiun";
	protected $auth_method = array ( self::AUTH_METHOD_PUBLIC );//, self::AUTH_METHOD_USER_PASS);
	protected $search_in_user = true; 
	protected $logo = "http://www.vidiun.com/images/wizard/logo_vidiun.gif";
	protected $id = entry::ENTRY_MEDIA_SOURCE_VIDIUN_VSHOW;
	
	private static $NEED_MEDIA_INFO = "0";
	
	protected function getVshowFilter ( $extraData )
	{
		return new vshowFilter ();	
	}

	/**
	 * 
		return array('status' => $status, 'message' => $message, 'objectInfo' => $objectInfo);
	*/
	public function getMediaInfo( $media_type ,$objectId)
	{
		return "";		
	}
	
	
	/**
		return array('status' => $status, 'message' => $message, 'objects' => $objects);
			objects - array of
					'thumb' 
					'title'  
					'description' 
					'id' - unique id to be passed to getMediaInfo 

		this service will first return the relevant vshows, then find the relevant roughcuts and finally fetch the entries
	*/
	public function searchMedia( $media_type , $searchText, $page, $pageSize, $authData = null , $extraData = null)
	{
		myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;

		// this bellow will bypass the partner filter - at the end of the code the filter will return to be as was before
		$vshow_criteria = vshowPeer::getCriteriaFilter()->getFilter();		
		$original_vshow_partner_to_filter = $vshow_criteria->get( vshowPeer::PARTNER_ID );
		$vshow_criteria->remove (vshowPeer::PARTNER_ID  );
		
		$entry_criteria = entryPeer::getCriteriaFilter()->getFilter();		
		$original_entry_partner_to_filter = $entry_criteria->get( entryPeer::PARTNER_ID );
		$entry_criteria->remove (entryPeer::PARTNER_ID  );
		
		$page_size = $pageSize > self::MAX_PAGE_SIZE ? self::MAX_PAGE_SIZE : $pageSize ;

		$status = "ok";
		$message = '';

		$vshow_filter = $this->getVshowFilter( $extraData );

		$limit = $pageSize;
		$offset = $pageSize * ($page-1); // $page starts from 1
		
//		$keywords_array = mySearchUtils::getKeywordsFromStr ( $searchText );

		// TODO_ change mechanism !
		//$search_mechanism = self::VIDIUN_SERVICE_CRITERIA_FROM_VSHOW;
		$search_mechanism = self::VIDIUN_SERVICE_CRITERIA_FROM_ROUGHCUT;
		
		// TODO - optimize the first part of the entry_id search
		// cache once we know the vshow_ids / roughcuts - this will make paginating much faster
		$vshow_crit = new Criteria();
		$vshow_crit->clearSelectColumns()->clearOrderByColumns();
		$vshow_crit->addSelectColumn(vshowPeer::ID);
		$vshow_crit->addSelectColumn(vshowPeer::SHOW_ENTRY_ID);
		$vshow_crit->setLimit( self::$s_default_count_limit );
		$vshow_filter->addSearchMatchToCriteria( $vshow_crit , $searchText , vshow::getSearchableColumnName() );
		
		if( $search_mechanism == self::VIDIUN_SERVICE_CRITERIA_FROM_VSHOW )
		{
			$vshow_crit->add ( vshowPeer::ENTRIES , 1 , Criteria::GREATER_EQUAL ) ;
		}						
		
		$rs = vshowPeer::doSelectStmt( $vshow_crit );
		
		
		$vshow_arr = array();
		$roughcut_arr = array(); 
	
		$res = $rs->fetchAll();
		foreach($res as $record) 
		{
			$vshow_arr[] = $record[0];
			$roughcut_arr[] = $record[1];
		}
		
//		// old code from doSelectRs
//		while($rs->next())
//		{
//			$vshow_arr[] = $rs->getString(1);
//			$roughcut_arr[] = $rs->getString(2);
//		}
			

		$crit = new Criteria();
		$crit->setOffset( $offset );
		$crit->setLimit( $limit );
		$crit->add ( entryPeer::TYPE ,  entryType::MEDIA_CLIP );
		$crit->add ( entryPeer::MEDIA_TYPE , $media_type );
		if( $search_mechanism == self::VIDIUN_SERVICE_CRITERIA_FROM_VSHOW )
		{
			$crit->add ( entryPeer::VSHOW_ID , $vshow_arr , Criteria::IN );
			$entry_results = entryPeer::doSelect ( $crit );
		}
		elseif (  $search_mechanism == self::VIDIUN_SERVICE_CRITERIA_FROM_ROUGHCUT )
		{
//			$entry_results  = roughcutEntryPeer::retrievByRoughcutIds ( $crit , $roughcut_arr , true );
			$entry_results  = roughcutEntryPeer::retrievEntriesByRoughcutIds ( $crit , $roughcut_arr  );
		}
		
		
		
		// after the query - return the filter to what it was before
		$entry_criteria->addAnd ( entryPeer::PARTNER_ID , $original_entry_partner_to_filter );
		$vshow_criteria->addAnd ( vshowPeer::PARTNER_ID , $original_vshow_partner_to_filter );
		
		
		$objects = array();
		
		// add thumbs when not image or video
		$should_add_thumbs = $media_type != entry::ENTRY_MEDIA_TYPE_AUDIO;
		foreach ( $entry_results as $obj )
		{
			if ( $search_mechanism == self::VIDIUN_SERVICE_CRITERIA_FROM_VSHOW )
			{
				$entry = $obj;
			}
			else
			{
				//$entry = $obj->getEntry();
				$entry = $obj;
			}
			/* @var $entry entry */
			
			// use the id as the url - it will help using this entry id in addentry
			$object = array ( "id" => $entry->getId() ,
				"url" => $entry->getDataUrl() , 
				"tags" => $entry->getTags() ,
				"title" => $entry->getName() , 
				"description" => $entry->getTags() ,
				"flash_playback_type" => $entry->getMediaTypeName() ,
//				"partnerId" => $entry->getPartnerId() 
			);
				
			if ( $should_add_thumbs )
			{
				$object["thumb"] = $entry->getThumbnailUrl() ;				
			}
			
			$objects[] = $object;
		}
		
		return array('status' => $status, 'message' => $message, 'objects' => $objects , "needMediaInfo" => self::$NEED_MEDIA_INFO);
	}
	
	
	/**
	*/
	public function getAuthData( $vuserId, $userName, $password, $token)
	{
		$status = 'error';
		$message = '';
		$authData = null;
		
		$vuser = vuserPeer::getVuserByScreenName( $userName );
		if ( $vuser )
		{
			$loginData = $vuser->getLoginData();
			if ($loginData && $loginData->isPasswordValid($password))
			{
				$authData= self::createHashString ( $vuser->getId() );
				
				$status = "ok";
			}
		}
		
		return array('status' => $status, 'message' => $message, 'authData' => $authData );
	}
	
	

	private static function createHashString ( $vuser_id )	
	{
		$hash = vString::expiryHash($vuser_id , self::AUTH_SALT  , self::AUTH_INTERVAL  ) ;
		$authData= $vuser_id . "I" . $hash;
		return $authData;
	}
	
	
}
