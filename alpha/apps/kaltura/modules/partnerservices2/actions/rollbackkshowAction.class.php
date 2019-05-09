<?php
/**
 * @package api
 * @subpackage ps2
 */
class rollbackvshowAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "rollbackVShow",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"vshow_id" => array ("type" => "string", "desc" => ""),
						"vshow_version" => array ("type" => "integer", "desc" => "")
						),
					"optional" => array (
						)
					),
				"out" => array (
					"vshow" => array ("type" => "vshow", "desc" => "")
					),
				"errors" => array (
					APIErrors::ERROR_VSHOW_ROLLBACK , 
					APIErrors::INVALID_USER_ID , 
					APIErrors::INVALID_VSHOW_ID ,
				)
			); 
	}
	
	// ask to fetch the vuser from puser_vuser
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_VUSER_ID_ONLY;
	}

	// TODO - merge with updatevshow and add the functionality of rollbackVersion
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		if ( ! $puser_vuser )
		{
			$this->addError ( APIErrors::INVALID_USER_ID ,  $puser_id);
			return;
		}

		$vshow_id = $this->getPM ( "vshow_id");
		
		$vshow = vshowPeer::retrieveByPK( $vshow_id );

		// even in case of an error - return the vshow object
		if ( ! $vshow )
		{
			$this->addError ( APIErrors::INVALID_VSHOW_ID , $vshow_id );
			return;
		}
		else
		{
			$desired_version = $this->getPM ( "vshow_version");
			$result = $vshow->rollbackVersion ( $desired_version );
		
			if ( ! $result )
			{
				$this->addError ( APIErrors::ERROR_VSHOW_ROLLBACK , $vshow_id ,$desired_version );
				return ;
			}
		}

		// after calling this method - most probably the state of the vshow has changed in the cache
		$wrapper = objectWrapperBase::getWrapperClass( $vshow , objectWrapperBase::DETAIL_LEVEL_REGULAR ) ;
		$wrapper->removeFromCache( "vshow" , $vshow_id );
		$this->addMsg ( "vshow" , $wrapper );
	}
}
?>