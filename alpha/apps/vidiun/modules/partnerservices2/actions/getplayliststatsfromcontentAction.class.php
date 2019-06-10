<?php
/**
 * @package api
 * @subpackage ps2
 */
class getplayliststatsfromcontentAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "getPlaylistStatsFromContent",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"playlist" => array ("type" => "entry", "desc" => "a playlist object filled with at least the mediaType & dataContent")
						),
					"optional" => array (
						"fp" => array ("type" => "string", "desc" => "filter prefix used for all the following filters") ,
						"filter1" => array ("type" => "entryFilter", "desc" => "") ,
						"filter2" => array ("type" => "entryFilter", "desc" => "") ,
						"filter3" => array ("type" => "entryFilter", "desc" => "") ,
						"filter4" => array ("type" => "entryFilter", "desc" => "") ,
						"detailed" => array ("type" => "boolean", "desc" => ""),
						"page_size" => array ("type" => "integer", "default" => 10, "desc" => ""),
						"page" => array ("type" => "boolean", "default" => 1, "desc" => ""),
						"use_filter_puser_id" => array ("type" => "boolean", "desc" => ""),
						)
					),
				"out" => array (
					"playlist" => array ("type" => "entry" , "desc" => "a parsial playlist object with at leaset the count,countData and duration"),
					),
				"errors" => array (
				)
			);
	}

	const MAX_FILTER_COUNT = 10;
	
	protected function setExtraFilters ( entryFilter &$fields_set )	{	}
	
	protected function joinOnDetailed () { return true;}

	protected function getObjectPrefix () { return "entries"; } 

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		// TODO -  verify permissions for viewing lists

		$detailed = $this->getP ( "detailed" , false );

		// fill the playlist (infact only the mediaType and contentData are important
		$playlist = new entry();
		$playlist->setType ( entryType::PLAYLIST ); // prepare the playlist type before filling from request
		$obj_wrapper = objectWrapperBase::getWrapperClass( $playlist , 0 );
		
		$playlist->setMediaType ( $this->getP ( "playlist_mediaType" ) );
		$data_content = $this->getP ( "playlist_dataContent" );
		
		$playlist->setDataContent( $data_content );
		
		myPlaylistUtils::updatePlaylistStatistics ( $partner_id , $playlist );
		
		$level = $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR ;
		$wrapper =  objectWrapperBase::getWrapperClass( $playlist  , $level );
		$this->addMsg ( "playlist" , $wrapper ) ;
	}
}
?>