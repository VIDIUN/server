<?php
/**
 * @package api
 * @subpackage ps2
 */
class getvshowAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "getVShow",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"vshow_id" => array ("type" => "string", "desc" => "")
						),
					"optional" => array (
						"detailed" => array ("type" => "boolean", "desc" => "")
						)
					),
				"out" => array (
					"vshow" => array ("type" => "vshow", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_VSHOW_ID ,
				)
			); 
	}

	// ask to fetch the vuser from puser_vuser 
	public function needVuserFromPuser ( )	
	{	
		$vshow_id = $this->getPM ( "vshow_id" );
		if ( $vshow_id == vshow::VSHOW_ID_USE_DEFAULT )			return parent::VUSER_DATA_VUSER_ID_ONLY ;
		return self::VUSER_DATA_NO_VUSER;	
	}
		
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$vshow_id = $this->getPM ( "vshow_id" );
		$detailed = $this->getP ( "detailed" , false );
		$vshow_indexedCustomData3 = $this->getP ( "indexedCustomData3" );
		$vshow = null;
        
		if ( $vshow_id == vshow::VSHOW_ID_USE_DEFAULT )
        {
            // see if the partner has some default vshow to add to
            $vshow = myPartnerUtils::getDefaultVshow ( $partner_id, $subp_id , $puser_vuser );
            if ( $vshow ) $vshow_id = $vshow->getId();
        }
		elseif ( $vshow_id )
		{
			$vshow = vshowPeer::retrieveByPK( $vshow_id );
		}
		elseif ( $vshow_indexedCustomData3 )
		{
			$vshow = vshowPeer::retrieveByIndexedCustomData3( $vshow_indexedCustomData3 );
		}

		if ( ! $vshow )
		{
			$this->addError ( APIErrors::INVALID_VSHOW_ID , $vshow_id );
		}
		else
		{
			$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR );
			$wrapper = objectWrapperBase::getWrapperClass( $vshow , $level );
			// TODO - remove this code when cache works properly when saving objects (in their save method)
			$wrapper->removeFromCache( "vshow" , $vshow_id );
			$this->addMsg ( "vshow" , $wrapper ) ;
		}
	}
}
?>