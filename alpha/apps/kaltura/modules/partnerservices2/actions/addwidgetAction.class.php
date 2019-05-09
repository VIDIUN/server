<?php
/**
 * @package api
 * @subpackage ps2
 */
class addwidgetAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "addWidget",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"widget" => array ("type" => "widget", "desc" => ""),
					),
					"optional" => array (
					)
				),
				"out" => array (
					"widget" => array ("type" => "widget", "desc" => "")
				),
				"errors" => array (
					APIErrors::NO_FIELDS_SET_FOR_WIDGET  ,
				)
			);
	}

	//protected function ticketType()			{	return self::REQUIED_TICKET_ADMIN;	} // TODO - and admin ticket

	public function needVuserFromPuser ( )	{	return self::VUSER_DATA_NO_VUSER;	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		// get the new properties for the vuser from the request
		$widget = new widget();

		$obj_wrapper = objectWrapperBase::getWrapperClass( $widget , 0 );

		$fields_modified = baseObjectUtils::fillObjectFromMap ( $this->getInputParams() , $widget , "widget_" , $obj_wrapper->getUpdateableFields() );
		// check that mandatory fields were set
		// TODO
		$new_widget = null;
		if ( count ( $fields_modified ) > 0 )
		{
			// see if to create a widget from a widget or from a vshow
			if ( $widget->getSourceWidgetId() )
			{
				$widget_from_db = widgetPeer::retrieveByPK( $widget->getSourceWidgetId() );
					
				$new_widget = widget::createWidgetFromWidget( $widget_from_db , $widget->getVshowId(), $widget->getEntryId(), $widget->getUiConfId() ,
					$widget->getCustomData() , $widget->getPartnerData() , $widget->getSecurityType() );
				if ( !$new_widget )
				{
					 $this->addError( APIErrors::INVALID_VSHOW_AND_ENTRY_PAIR , $widget->getVshowId(), $widget->getEntryId() );
					 return;
				}
			}
			else
			{
				$vshow_id = $widget->getVshowId();

				if ( $vshow_id )
				{
					$vshow = vshowPeer::retrieveByPK( $vshow_id );
					if ( ! $vshow )
					{
						$this->addError( APIErrors::VSHOW_DOES_NOT_EXISTS ) ;// This field in unique. Please change ");
						return;
					}
				}
				else
				{
					$vshow = new vshow();
					$vshow->setId(0);
					$vshow->setPartnerId($partner_id);
					$vshow->setSubpId($subp_id);
				}
				$new_widget = widget::createWidget( $vshow , $widget->getEntryId() , null  , $widget->getUiConfId() ,
					$widget->getCustomData() , $widget->getPartnerData() , $widget->getSecurityType() );
			}

			$this->addMsg ( "widget" , objectWrapperBase::getWrapperClass( $new_widget , objectWrapperBase::DETAIL_LEVEL_DETAILED) );
			$this->addDebug ( "added_fields" , $fields_modified );
		}
		else
		{
			$this->addError( APIErrors::NO_FIELDS_SET_FOR_WIDGET ) ;
		}
	}
}
?>