<?php
/**
 * @package api
 * @subpackage ps2
 */
class addroughcutentryAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "addRoughcutEntry",
				"desc" => "Create a new roughcut entry" ,
				"in" => array (
					"mandatory" => array (
						"vshow_id" => array ("type" => "integer"), 
						"entry" => array ("type" => "entry", "desc" => "Entry of type ENTRY_TYPE_SHOW"),
						),
					"optional" => array (
						)
					),
				"out" => array (
					"entry" => array ("type" => "entry", "desc" => "Entry of type ENTRY_TYPE_SHOW")
					),
				"errors" => array (
					APIErrors::INVALID_VSHOW_ID
				)
			); 
	}
	
	protected function addUserOnDemand () { return self::CREATE_USER_FORCE; }
	
	protected function ticketType()			{	return self::REQUIED_TICKET_REGULAR;	} // TODO - and admin ticket

	protected function getObjectPrefix () { return "entry"; }

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$vshow_id = $this->getP ( "vshow_id" , vshow::VSHOW_ID_USE_DEFAULT );

		$entry = null;
       	if ( $vshow_id == vshow::VSHOW_ID_USE_DEFAULT )
        {
            // see if the partner has some default vshow to add to
            $vshow = myPartnerUtils::getDefaultVshow ( $partner_id, $subp_id , $puser_vuser  );
            if ( $vshow ) $vshow_id = $vshow->getId();
        }
		elseif ( $vshow_id == vshow::VSHOW_ID_CREATE_NEW )
        {
            // if the partner allows - create a new vshow 
            $vshow = myPartnerUtils::getDefaultVshow ( $partner_id, $subp_id , $puser_vuser , null , true );
            if ( $vshow )
            {
            	$vshow_id = $vshow->getId();
       	        $entry = $vshow->getShowEntry(); // use the newly created vshow's roughcut
            }
        }   
		else
        {
            $vshow = vshowPeer::retrieveByPK( $vshow_id );
        }

        if ( ! $vshow )
        {
            // the partner is attempting to add an entry to some invalid or non-existing vwho
            $this->addError( APIErrors::INVALID_VSHOW_ID, $vshow_id );
            return;
        }
        
		if (!$entry)
		{
			$entry = $vshow->createEntry( entry::ENTRY_MEDIA_TYPE_SHOW , $vshow->getProducerId() , "&auto_edit.jpg" , "" ); 
		}
           
        $obj_wrapper = objectWrapperBase::getWrapperClass( $entry , 0 );
		
		$fields_modified = baseObjectUtils::fillObjectFromMap ( $this->getInputParams() , $entry , $this->getObjectPrefix() . "_" , 
			array ( "name"  , "tags" , "groupId" , "partnerData", "permissions" , "screenName",  "description", "indexedCustomData1") );
        
		$entry->save();
									
		$this->addMsg ( $this->getObjectPrefix() , objectWrapperBase::getWrapperClass( $entry , objectWrapperBase::DETAIL_LEVEL_REGULAR ) );
		$this->addDebug ( "added_fields" , $fields_modified );
	}
}
?>