<?php
/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
require_once ( __DIR__ . "/vidiunSystemAction.class.php" );

/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
class bandsAction extends vidiunSystemAction
{
	/**
	 * 
select vshow.id,concat('http://www.vidiun.com/index.php/browse/bands?band_id=',indexed_custom_data_1),concat('http://profile.myspace.com/index.cfm?fuseaction=user.viewpr
ofile&friendID=',indexed_custom_data_1) ,  vuser.screen_name , indexed_custom_data_1  from vshow ,vuser where vshow.partner_id=5 AND vuser.id=vshow.producer_id AND vshow.
id>=10815  order by vshow.id ;
~

	 */
	public function execute()
	{
	//	$this->forceSystemAuthentication();
		
		$from = $this->getRequestParameter( "from" , null );
		$to = $this->getRequestParameter( "to" , null );
		$limit = $this->getRequestParameter( "limit" , 100 );
		$c = new Criteria();
		$c->setLimit( $limit );
		$c->add ( vshowPeer::PARTNER_ID , 5 ); // myspace
		
		$c->addAscendingOrderByColumn( vshowPeer::ID );
		
		if ( !empty ( $from ) )
		{
			$c->addAnd( vshowPeer::ID , $from , Criteria::GREATER_EQUAL );
		}
		if ( ! empty ( $to ) )
		{
			$c->addAnd( vshowPeer::ID , $to , Criteria::LESS_EQUAL );
		}
		
		$this->band_list = vshowPeer::doSelectJoinvuser ( $c );
				
	}
}
?>