<?php
/**
 * @package api
 * @subpackage ps2
 */
require_once 'addvshowAction.class.php';

/**
 * 1. Will create a vshow with name and summary for a specific partner.
 * 2. Will generate widget-html for this vshow.
 * 
 * @package api
 * @subpackage ps2
 */
class generatewidgetAction extends addvshowAction
{
	public function describe()
	{
		return 
			array (); 
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

	protected function ticketType ()
	{
		// validate for all partners that are not vidiun (partner_id=0)
		$partner_id = $this->getP ( "partner_id");
		return ( $partner_id != 0 ? self::REQUIED_TICKET_ADMIN : self::REQUIED_TICKET_NONE );
	}
	/*
	public function execute( $add_extra_debug_data = true )
	{
		// will inject data so the base class will act as it the partner_id is 0
		$this->injectIfEmpty ( array (
			"partner_id" => "0" ,
			"subp_id" => "0" ,
			"uid" => "_00" ));

		return parent::execute();
	}
	*/
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$vshow_id = $this->getP ( "vshow_id");
		$detailed = $this->getP ( "detailed" , false );
		$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR );

		$widget_size = $this->getP ( "size" );

		$vshow_from_db = null;
		if ( $vshow_id )
		{
			$vshow_from_db = vshowPeer::retrieveByPK( $vshow_id );
		}

		if ( $vshow_from_db )
		{
			$this->addMsg ( "vshow" , objectWrapperBase::getWrapperClass( $vshow_from_db ,  $level  ) );
			$this->addMsg ( "already_exists_objects" , 1 );
			$this->addDebug ( "already_exists_objects" , 1 );
		}
		else
		{
			// no vshow to be found - creae a new one
			parent::executeImpl(  $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser );
		}

		// create widget code for the new vshow
		$vshow = $this->getMsg ( "vshow" );
		$vshow_id = $vshow->id;

		list ($genericWidget, $myspaceWidget) = myVshowUtils::getEmbedPlayerUrl ($vshow_id,null , false , "" );
		$code = array ( "generic_code" => $genericWidget , "myspace_code" => $myspaceWidget );
		$this->addMsg ( "widget_code" , $code );

	}



}
?>