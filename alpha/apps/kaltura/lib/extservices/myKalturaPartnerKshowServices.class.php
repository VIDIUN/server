<?php
/**
 * @package Core
 * @subpackage ExternalServices
 */
class myVidiunPartnerVshowServices extends myVidiunVshowServices implements IMediaSource
{
	const AUTH_SALT = "myVidiunPartnerVshowServices:gogog123";
	const AUTH_INTERVAL = 3600;
	
	protected $id = entry::ENTRY_MEDIA_SOURCE_VIDIUN_PARTNER_VSHOW;
	
	private static $NEED_MEDIA_INFO = "0";
	
	// assume the extraData is the partner_id to be searched 
	protected function getVshowFilter ( $extraData )
	{
		$filter = new vshowFilter ();
		// This is the old way to search within a partner
//		$entry_filter->setByName ( "_eq_partner_id" , $extraData );

		// this is the better way -
		$filter->setPartnerSearchScope( $extraData );
		return $filter;
	}
}
