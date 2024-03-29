<?php
/**
 * @package api
 * @subpackage ps2
 */
class handlemoderationAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "handleModeration",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"moderation_id" => array ("type" => "integer", "desc" => ""),
						"moderation_status" => array ("type" => "integer", "desc" => "")
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
		return self::REQUIED_TICKET_ADMIN;
	}

	// ask to fetch the vuser from puser_vuser - so we can tel the difference between a
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_NO_VUSER;
	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$moderation_id = $this->getPM ( "moderation_id" );
		$moderation_status = $this->getPM ( "moderation_status" );

		$moderation = moderationPeer::retrieveByPK($moderation_id);
		if ( $moderation )
			$moderation->updateStatus($moderation_status);
	}
}
?>