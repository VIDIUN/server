<?php
/**
 * This is a utility service that helps describes the fields of our objects - not the data itself
 * 
 * @package api
 * @subpackage ps2
 */
class objdetailsAction extends defPartnerservices2Action
{
	public function describe()
	{
		return array();		
	}
	
	protected function ticketType ()
	{
		return self::REQUIED_TICKET_NONE;
	}

	// ask to fetch the vuser from puser_vuser
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_NO_VUSER;
	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$clazz_name = $this->getP ( "clazz" );
		if ( $clazz_name == "vshow" ) $obj = new vshow();
		else if ( $clazz_name == "vuser" ) $obj = new vuser();
		else if ( $clazz_name == "entry" ) $obj = new entry();
		else if ( $clazz_name == "PuserVuser" ) $obj = new PuserVuser();

		$obj = new $clazz_name();

		$detailed = $this->getP ( "detailed" );
		$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR );
		$wrapper = objectWrapperBase::getWrapperClass( $obj , $level ) ;

		if ( $wrapper )
		{
			$this->addMsg ( "regular" , $wrapper->getRegularFields() );
			$this->addMsg ( "detailed" , $wrapper->getDetailedFields() );
			$this->addMsg ( "objects" , $wrapper->getObjectTypes() );
		}
		else
		{
			$this->addError( APIErrors::ERROR_CREATING_NOTIFICATION, $clazz_name );
		}
	}
}
?>