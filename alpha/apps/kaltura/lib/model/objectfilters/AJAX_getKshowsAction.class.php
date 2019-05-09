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
class AJAX_getVshowsAction extends AJAX_getObjectsAction
{
	public function getPagerName( ) 		{ return "vshow" ; }
	public function getFiler () 			{ return new vshowFilter() ;	}
	public function getComlumnNames () 		{ return vshow::getColumnNames() ; } // alter table vshow add FULLTEXT ( name , description , tags );
	public function getSearchableColumnName () 		{ return vshow::getSearchableColumnName(); } //  alter table entry add FULLTEXT ( name , tags );
	public function getFilterPrefix ( ) 	{ return "vshow_filter_" ; }
	public function getPeerMethod ()		{ return "doSelectJoinvuser" ; }
	public function getPeerCountMethod () 	{ return "doCountWithLimit" ; } //"doCountJoinvuser" ; }

	public function getOrCriterion( Criteria $c )
	{
		$res = null;
		if ( $this->or_category ) // use the category with OR with tagword-complex-criteria
		{
			if ( $this->category != NULL && $this->category >= 0  )
			{
				$res =  $c->getNewCriterion ( vshowPeer::TYPE , $this->category );
			}
		}
		
		return $res;
	}

	public function modifyCriteria ( Criteria $c )
	{
//		$c->addJoin( vshowPeer::PRODUCER_ID , vuserPeer::ID , Criteria::JOIN);
		$c->addJoin( vshowPeer::PRODUCER_ID , vuserPeer::ID , Criteria::LEFT_JOIN);

		if ( !$this->or_category ) // use the category with AND with tagword-complex-criteria
		{
			if ( $this->category != NULL && $this->category >= 0  )
			{
				$c->add ( vshowPeer::TYPE , $this->category );
			}
		}
/*
		// always filter out all those partner_ids that are not public
		$c->addAnd ( vshowPeer::ID , vshow::MINIMUM_ID_TO_DISPLAY , Criteria::GREATER_THAN );
		$c->addAnd ( vshowPeer::PARTNER_ID, myPartnerUtils::PUBLIC_PARTNER_INDEX , Criteria::LESS_EQUAL );
*/
	}


	public function getSortArray ( )
	{
		//date | rating | views | type
		$sort_aliases = array (
		"date" => "-created_at" ,
		"rank" => "-rank" ,
		"views" => "-views" ,
		"type" => "+type" ,
		"comments" => "-comments" ,
		"ids" => "+id" );
		return $sort_aliases;
	}
	public function getDefaultSort ( )
	{
		return "-created_at";
	}

/*
	public function getTopImpl ()
	{
		return vshowPeer::getTopVshows();
	}
*/
}
?>