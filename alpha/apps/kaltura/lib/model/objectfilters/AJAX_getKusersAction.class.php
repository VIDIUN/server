<?php
/**
 * @package Core
 * @subpackage ajax
 */ 
require_once( __DIR__ . '/AJAX_getObjectsAction.class.php');

/**
 * @package Core
 * @subpackage ajax
 */ 
class AJAX_getVusersAction extends AJAX_getObjectsAction
{
	public function getPagerName( ) 		{ return "vuser" ; }
	public function getFiler () 			{ return new vuserFilter() ;	}
	public function getComlumnNames () 		{ return vuser::getColumnNames(); } // alter table vuser add FULLTEXT ( screen_name , full_name , url_list , tags , about_me , network_highschool , network_college ,network_other ) ;
	public function getSearchableColumnName () 		{   return vuser::getSearchableColumnName() ; } //  alter table entry add FULLTEXT ( name , tags );
	public function getFilterPrefix ( ) 	{ return "vuser_filter_" ; }
	public function getPeerMethod ()		{ return NULL ; }
	public function getPeerCountMethod () 	{ return "doCountWithLimit" ; }

	public function modifyCriteria ( Criteria $c )
	{
/*		
		$c->addAnd ( vuserPeer::ID , vuser::MINIMUM_ID_TO_DISPLAY , Criteria::GREATER_THAN );
		$c->addAnd ( vuserPeer::STATUS , VuserStatus::ACTIVE );

		// always filter out all those partner_ids that are not null  
		$c->addAnd ( vuserPeer::PARTNER_ID, myPartnerUtils::PUBLIC_PARTNER_INDEX , Criteria::LESS_EQUAL );
*/
	}
	
	public function getSortArray ( )
	{
		//screen_name | last_update | views | num_of_media
		$sort_aliases = array (
		 	"screen_name" => "+screen_name" ,
		 	"date" => "-updated_at" , 
			"views" => "-views" ,
			"num_of_media" => "-entries" ,
			"num_of_vidiuns" => "-produced_vshows" ,
			"ids" => "+id" );
		return $sort_aliases;			
	}
	public function getDefaultSort ( )
	{
		return "-views";
	}	
	
/*
	public function getTopImpl ()
	{
		return vuserPeer::getTopVusers ();
	}
	*/
}
?>