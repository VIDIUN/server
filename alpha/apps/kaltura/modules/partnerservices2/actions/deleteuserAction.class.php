<?php
/**
 * @package api
 * @subpackage ps2
 */
class deleteuserAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "deleteUser",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"user_id" => array ("type" => "integer", "desc" => "")
						),
					"optional" => array (
						)
					),
				"out" => array (
					"deleted_user" => array ("type" => "PuserVuser", "desc" => "")
					),
				"errors" => array (
				)
			);
	}

	protected function ticketType()
	{
		return self::REQUIED_TICKET_ADMIN;
	}

	// ask to fetch the vuser from puser_vuser - so we can tel the difference between a
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_VUSER_ID_ONLY;
	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$puser_id_to_delete = $this->getPM ( "user_id" );

		$puser_vuser_to_delete = PuserVuserPeer::retrieveByPartnerAndUid ( $partner_id , null /*$subp_id*/,  $puser_id_to_delete , true );
		if ( !$puser_vuser_to_delete )
		{
			$this->addError( APIErrors::INVALID_USER_ID , $puser_id_to_delete );
			return;
		}

		$vuser = $puser_vuser_to_delete->getVuser();
		if ( $vuser )
		{
//			$this->addMsg ( "deleted_vuser" , objectWrapperBase::getWrapperClass( $vuser , objectWrapperBase::DETAIL_LEVEL_REGULAR ) );

			try {
				$vuser->setStatus(VuserStatus::DELETED);
			}
			catch (vUserException $e) {
				$code = $e->getCode();
				if ($code == vUserException::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER) {
					$this->addException( APIErrors::CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER);
					return null;
				}
				throw $e;			
			}	
		}
		$puser_vuser_to_delete->delete();

		$this->addMsg ( "deleted_user" , objectWrapperBase::getWrapperClass( $puser_vuser_to_delete , objectWrapperBase::DETAIL_LEVEL_DETAILED) );

	}
}
