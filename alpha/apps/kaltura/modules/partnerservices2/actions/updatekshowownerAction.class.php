<?php
/**
 * @package api
 * @subpackage ps2
 */
class updatevshowownerAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "updateVshowOwner",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"vshow_id" => array ("type" => "string", "desc" => "")
						),
					"optional" => array (
						"detailed" => array ("type" => "boolean", "desc" => "")
						)
					),
				"out" => array (
					"vshow" => array ("type" => "vshow", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_VSHOW_ID,
					APIErrors::INVALID_USER_ID,
				)
			); 
	}
	
	protected function ticketType ()
	{
		return self::REQUIED_TICKET_ADMIN;
	}

	// check to see if already exists in the system = ask to fetch the puser & the vuser
	// don't ask for  VUSER_DATA_VUSER_DATA - because then we won't tell the difference between a missing vuser and a missing puser_vuser
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_VUSER_ID_ONLY;
	}

	protected function addUserOnDemand ( )
	{
		return self::CREATE_USER_FROM_PARTNER_SETTINGS;
	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$vshow_id = $this->getPM ( "vshow_id" );
		$target_puser_id = $this->getPM ( "user_id" );
		$detailed = $this->getP ( "detailed" , false );
		$vshow_indexedCustomData3 = $this->getP ( "indexedCustomData3" );
		$vshow = null;
		if ( $vshow_id )
		{
			$vshow = vshowPeer::retrieveByPK( $vshow_id );
		}
		elseif ( $vshow_indexedCustomData3 )
		{
			$vshow = vshowPeer::retrieveByIndexedCustomData3( $vshow_indexedCustomData3 );
		}

		if ( ! $vshow )
		{
			$this->addError ( APIErrors::INVALID_VSHOW_ID , $vshow_id );
		}
		else
		{
			$new_puser_vuser = PuserVuserPeer::retrieveByPartnerAndUid( $partner_id , null , $target_puser_id );
			if ( ! $new_puser_vuser )
			{
				$this->addError ( APIErrors::INVALID_USER_ID , $target_puser_id );
				return;
			}
 			$vshow->setProducerId ( $new_puser_vuser->getVuserId() );
 			$vshow->save();

 			$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR );
			$wrapper = objectWrapperBase::getWrapperClass( $vshow , $level );
			// TODO - remove this code when cache works properly when saving objects (in their save method)
			$wrapper->removeFromCache( "vshow" , $vshow->getId() );
			$this->addMsg ( "vshow" , $wrapper ) ;
		}
	}
}
?>