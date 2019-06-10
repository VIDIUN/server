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
class AJAX_getEntriesAction extends AJAX_getObjectsAction
{
	private $vuser_id = null;
	
	private $public_only = false;
	
	private $media_type = null;
	
	//private static 
	public function getPagerName( ) 		{ return "entry" ; }
	public function getFiler () 			{ return new entryFilter() ;	}
	public function getComlumnNames () 		{ return entry::getColumnNames() ; } //  alter table entry add FULLTEXT ( name , tags );
	public function getSearchableColumnName () 		{ return entry::getSearchableColumnName(); } //  alter table entry add FULLTEXT ( name , tags );
	public function getFilterPrefix ( ) 	{ return "entry_filter_" ; }
	public function getPeerMethod ()		{ return "doSelect"; } //return "doSelectJoinvuser" ; }
	public function getPeerCountMethod () 	{ return "doCountWithLimit" ; } //"doCountJoinAll" ; } //return "doCountJoinvuser" ; }

	public function setPublicOnly ( $v )
	{
		$this->public_only = $v;
	}

	public function setOnlyForVuser ( $vuser_id )
	{
		$this->vuser_id = $vuser_id;
	}
	
	public function setMediaType ( $media_type )
	{
		$this->media_type = $media_type;
	}
	
	
	public function modifyCriteria ( Criteria $c )
	{
//		entryPeer::setUseCriteriaFilter( false );
		
//		$c->addJoin( entryPeer::VSHOW_ID , vshowPeer::ID , Criteria::LEFT_JOIN);
//		$c->addJoin( entryPeer::VUSER_ID , vuserPeer::ID , Criteria::LEFT_JOIN);

		if ( $this->vuser_id  )
		{
			$c->addAnd ( entryPeer::VUSER_ID ,  $this->vuser_id );
		}
		
		if ( $this->media_type )
		{
			$c->addAnd ( entryPeer::MEDIA_TYPE , $this->media_type );
		}
	}
	
	public function getSortArray ( )
	{
		//rating | date | views
		$sort_aliases = array ( 	
			"rank" => "-rank" , 
			"date" => "-created_at" , 
			"views" => "-views" ,
			"ids" => "+id" );
		return $sort_aliases;			
	}
	public function getDefaultSort ( )
	{
		return "-views" ; //"-created_at";
	}
	
/*	public function getTopImpl ()
	{
		return entryPeer::getTopEntries ();
	}
*/
}
?>