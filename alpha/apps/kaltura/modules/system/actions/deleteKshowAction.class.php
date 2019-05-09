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
class deleteVshowAction extends vidiunSystemAction
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
		$this->forceSystemAuthentication();
		
		$vshow_id = $this->getRequestParameter( "vshow_id" , null );
		$band_id = $this->getRequestParameter( "band_id" , null );
		$vuser_name = $this->getRequestParameter( "vuser_name" , null );
		
		$this->other_vshows_by_producer = null;
		
		$error = "";
		
		$vshow = null;
		$vuser = null;
		$entries = null;
		
		$this->vuser_count = 0;
		
		$should_delete = $this->getRequestParameter( "deleteme" , "false" ) == "true" ;
		if ( $vuser_name )
		{
			$c = new Criteria();
			$c->add ( vuserPeer::SCREEN_NAME , "%" . $vuser_name . "%" , Criteria::LIKE );
			$this->vuser_count = vuserPeer::doCount ( $c );
			$vuser = vuserPeer::doSelectOne ( $c );
			
			if ( $vuser )
			{
				$this->other_vshows_by_producer = $this->getVshowsForVuser ( $vuser , null );
			}
			else
			{
				$error .= "Cannot find vuser with name [$vuser_name]<br>";
			}
			
			$other_vshow_count = count ( $this->other_vshows_by_producer );
			if (  $other_vshow_count < 1 )
			{
				// vuser has no vshow - delete him !
				if ( $should_delete )
				{
					$vuser->delete();
				}
			}
			else if ( $other_vshow_count == 1 )
			{
				$vshow_id = $this->other_vshows_by_producer[0]->getId();
			}
			else
			{
				// vuser has more than one vshow - let user choose 
				$error .= "[$vuser_name] has ($other_vshow_count) shows.<br>";
			}
		}
		
		if ( $band_id )
		{
			$c = new Criteria();
			$c->add ( vshowPeer::INDEXED_CUSTOM_DATA_1 , $band_id );
			$c->add ( vshowPeer::PARTNER_ID , 5 );
			$vshow = vshowPeer::doSelectOne( $c );
		}
		else if ( $vshow_id )
		{
			$vshow = vshowPeer::retrieveByPK( $vshow_id ); 
		}
		
		if ( $vshow )
		{
			if ( ! $vuser )		$vuser = vuserPeer::retrieveByPK( $vshow->getProducerId() );
			if ( $vuser )
			{
				$this->other_vshows_by_producer = $this->getVshowsForVuser ( $vuser , $vshow );
				
				if ( $should_delete )
				{
					if ( count ( $this->other_vshows_by_producer ) == 0 )
					{
						$vuser->delete();
					}
				}
			}
			
			$entries = $vshow->getEntrys ();
			
			if ( $should_delete )
			{
				$id_list = array();
				foreach ( $entries as $entry )
				{
					$id_list[] = $entry->getId();
				}
				
				if ( $id_list )
				{
					$d = new Criteria();
					$d->add ( entryPeer::ID , $id_list , Criteria::IN );
					entryPeer::doDelete( $d );
				}
			}
			
			if ( $should_delete )
			{
				$vshow->delete();
			}
			
		}
		else
		{
			$error .= "Cannot find vshow [$vshow_id]<br>";
		}
		
		
		$this->vshow_id = $vshow_id;
		$this->vuser_name = $vuser_name;
		$this->vshow = $vshow;
		$this->vuser = $vuser;
		$this->entries = $entries; 	
		$this->should_delete = $should_delete;	

		$this->error = $error; 
	}
	
	private function getVshowsForVuser ( $vuser , $vshow )
	{
		
		$c = new Criteria();
		$c->add ( vshowPeer::PRODUCER_ID , $vuser->getId() );
		if ( $vshow ) $c->add ( vshowPeer::ID , $vshow->getId(), Criteria::NOT_EQUAL );
		$other_vshows_by_producer = vshowPeer::doSelect( $c );
		
		return $other_vshows_by_producer;
						
	}
}
?>