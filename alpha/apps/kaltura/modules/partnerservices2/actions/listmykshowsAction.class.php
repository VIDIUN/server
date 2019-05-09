<?php
/**
 * @package api
 * @subpackage ps2
 */
require_once 'listvshowsAction.class.php';

/**
 * @package api
 * @subpackage ps2
 */
class listmyvshowsAction extends listvshowsAction
{
	public function describe()
	{
		return 	
			array (
				"display_name" => "listMyVShows",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"filter" => array ("type" => "vshowFilter", "desc" => "")
						),
					"optional" => array (
						"detailed" => array ("type" => "boolean", "desc" => ""),
						"page_size" => array ("type" => "integer", "default" => 10, "desc" => ""),
						"page" => array ("type" => "boolean", "default" => 1, "desc" => ""),
						"use_filter_puser_id" => array ("type" => "boolean", "desc" => ""),
						)
					),
				"out" => array (
					"count" => array ("type" => "integer", "desc" => ""),
					"page_size" => array ("type" => "integer", "desc" => ""),
					"page" => array ("type" => "integer", "desc" => ""),
					"vshows" => array ("type" => "*vshow", "desc" => ""),
					"user" => array ("type" => "vuser", "desc" => ""),
					),
				"errors" => array (
				)
			); 
	}
	
	protected function ticketType()
	{
		return self::REQUIED_TICKET_REGULAR;
	}
		
	// for this specific vshow list - the ticket is regular and the filter is for all
	// vshows for the current user only 
	protected function setExtraFilters ( vshowFilter &$fields_set )
	{
		$fields_set->set( "_eq_producer_id" , $this->puser_id );
	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$this->puser_id = $puser_id;
		parent::executeImpl( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser );
	}
}
?>