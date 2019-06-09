<?php
/**
 * @package api
 * @subpackage ps2
 */
class getuserAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "getUser",
				"desc" => "",
				"in" => array (
					"mandatory" => array ( 
						"user_id" => array ("type" => "integer", "desc" => ""),
						),
					"optional" => array (
						"detailed" => array("type" => "boolean", "desc" => "")
						)
					),
				"out" => array (
					"user" => array ("type" => "PuserVuser", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_USER_ID ,
				)
			); 
	}
	
	protected function ticketType()	{		return self::REQUIED_TICKET_ADMIN;	}
	public function needVuserFromPuser ( )	{		return self::VUSER_DATA_NO_VUSER;	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		// the relevant puser_vuser is the one from the user_id NOT the uid (which is the logged in user investigationg
		$user_id = $this->getPM ( "user_id" );
		$target_puser_vuser = PuserVuserPeer::retrieveByPartnerAndUid($partner_id , null , $user_id , true ); 
		$detailed = $this->getP ( "detailed" , false );
		
		if ( ! $target_puser_vuser )
		{
			$this->addError ( APIErrors::INVALID_USER_ID , $user_id );
		}
		else
		{
			$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_DETAILED );
			$this->addMsg ( "user" , objectWrapperBase::getWrapperClass( $target_puser_vuser , $level ) );
		}
	}
}
?>