<?php
/**
 * @package api
 * @subpackage ps2
 */
abstract class addentrybaseAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "addEntryBase",
				"desc" => "Create a new entry" ,
				"in" => array (
					"mandatory" => array ( 
						"entry" => array ("type" => "entry", "desc" => ""),
						),
					"optional" => array (
						)
					),
				"out" => array (
					"entry" => array ("type" => "entry", "desc" => "")
					),
				"errors" => array (
					APIErrors::NO_FIELDS_SET_FOR_GENERIC_ENTRY ,
					APIErrors::INVALID_VSHOW_ID
				)
			); 
	}
	
	protected function getDetailed()
	{
		return $this->getP ( "detailed" , false );
	}
	
	protected function getObjectPrefix () {  return "entry"; }

	abstract protected function setTypeAndMediaType ( $entry ) ;
	
	protected function validateEntry ( $entry ) {}

	protected function getVshow ( $partner_id, $subp_id , $puser_vuser , $vshow_id , $entry )
	{
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
            if ( $vshow ) $vshow_id = $vshow->getId();
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
        return $vshow;	
	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$detailed = $this->getDetailed() ; //$this->getP ( "detailed" , false );
		$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR );
		
		// get the new properties for the vuser from the request
		$entry = new entry();
		
		// this is called for the first time to set the type and media type for fillObjectFromMap
		$this->setTypeAndMediaType ( $entry );
		
		// important to set type before the auto-fill so the setDataContent will work properly
		$entry->setLengthInMsecs( 0 );
		
		$obj_wrapper = objectWrapperBase::getWrapperClass( $entry , 0 );
		
		$field_level = $this->isAdmin() ? 2 : 1;
		$updateable_fields = $obj_wrapper->getUpdateableFields( $field_level );
		
		$fields_modified = baseObjectUtils::fillObjectFromMap ( $this->getInputParams() , $entry , $this->getObjectPrefix() . "_" , $updateable_fields );
		// check that mandatory fields were set
		// TODO
		if ( count ( $fields_modified ) > 0 )
		{
			
			$vshow_id = $this->getP ( "vshow_id" , vshow::VSHOW_ID_USE_DEFAULT );						
			$vshow = $this->getVshow ( $partner_id , $subp_id , $puser_vuser , $vshow_id , $entry );
	        
			// force the type and media type
			// TODO - set the vshow to some default vshow of the partner - maybe extract it from the custom_data of this specific partner
			$entry->setVshowId ( $vshow_id );
			$entry->setStatus( entryStatus::READY );
			$entry->setPartnerId( $partner_id );
			$entry->setSubpId( $subp_id );
			$entry->setVuserId($puser_vuser->getVuserId() );
			$entry->setCreatorVuserId($puser_vuser->getVuserId() );

			// this is now called for the second time to force the type and media type
			$this->setTypeAndMediaType ( $entry );

			$this->validateEntry ( $entry );
			
			$entry->save();
										
			$this->addMsg ( $this->getObjectPrefix() , objectWrapperBase::getWrapperClass( $entry , $level ) );
			$this->addDebug ( "added_fields" , $fields_modified );
		}
		else
		{
			$this->addError( APIErrors::NO_FIELDS_SET_FOR_GENERIC_ENTRY , $this->getObjectPrefix() );
		}
	}
}
?>