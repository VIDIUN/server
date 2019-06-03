<?php
/**
 * @package api
 * @subpackage ps2
 */
class searchmediaprovidersAction extends defPartnerservices2Action
{
	public function describe()
	{
		return array();		
	}
	
	// TODO - remove so this service will validate the session
	protected function ticketType ()
	{
		return self::REQUIED_TICKET_NONE;
	}
	
	// check to see if already exists in the system = ask to fetch the puser & the vuser
	// don't ask for  VUSER_DATA_VUSER_DATA - because then we won't tell the difference between a missing vuser and a missing puser_vuser
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_NO_VUSER;
	}

	/**
		the puser might not be a vuser in the system
	 */
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		self::$escape_text = true;
		
		$service_provider_list = myPartnerUtils::getMediaServiceProviders ( $partner_id , $subp_id );
		
		$this->addMsg( "config_" , $service_provider_list );
	}
}
?>