<?php
/**
 * @package api
 * @subpackage ps2
 */
class reporterrorAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "reportError",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						),
					"optional" => array (
						"reporting_obj" 				=> array ("type" => "string", "desc" => ""),
						"error_code" 				=> array ("type" => "string", "desc" => ""),
						"error_description" 				=> array ("type" => "string", "desc" => ""),
						)
					),
				"out" => array (
					),
				"errors" => array (
					APIErrors::DUPLICATE_VSHOW_BY_NAME
				)
			);
	}

	protected function ticketType ()	{		return self::REQUIED_TICKET_NONE;	}

	// check to see if already exists in the system = ask to fetch the puser & the vuser
	// don't ask for  VUSER_DATA_VUSER_DATA - because then we won't tell the difference between a missing vuser and a missing puser_vuser
	public function needVuserFromPuser ( )	{		return self::VUSER_DATA_NO_VUSER;	}

	protected function addUserOnDemand ( )	{		return self::CREATE_USER_FALSE;	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		// TODO - store to some error filefile
		$reporting_obj = $this->getP ( "reporting_obj" );
		$error_code = $this->getP ( "error_code" );
		$error_desc = $this->getP ( "error_description" );
	}
}
?>