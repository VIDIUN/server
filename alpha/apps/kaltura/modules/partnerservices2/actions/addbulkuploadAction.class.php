<?php
/**
 * @package api
 * @subpackage ps2
 */
class addbulkuploadAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "addBulkUpload",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"profile_id" => array ("type" => "integer", "desc" => "")
						),
					"optional" => array (
						)
					),
				"out" => array (
					
					),
				"errors" => array (
					)
			);
	}

	protected function ticketType()
	{
		return self::REQUIED_TICKET_REGULAR;
	}

	// ask to fetch the vuser from puser_vuser - so we can tel the difference between a
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_VUSER_DATA;
	}

    protected function getObjectPrefix()
    {
    	return "";
    }
    
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		die("This action is no longer supported");
	}
}
