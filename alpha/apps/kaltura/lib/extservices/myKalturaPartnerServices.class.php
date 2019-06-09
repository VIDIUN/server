<?php
/**
 * @package Core
 * @subpackage ExternalServices
 */
class myVidiunPartnerServices extends myVidiunServices implements IMediaSource
{
	const AUTH_SALT = "myVidiunPartnerServices:gogog123";
	const AUTH_INTERVAL = 3600;
	
	protected $id = entry::ENTRY_MEDIA_SOURCE_VIDIUN_PARTNER;
	
	private static $NEED_MEDIA_INFO = "0";
	
	public function __construct()
	{
		parent::__construct();
		self::$s_clazz = get_class();
	}
	
	// assume the extraData is the partner_id to be searched 
	protected function getEntryFilter ( $extraData )
	{
		$entry_filter = new entryFilter ();
		// This is the old way to search within a partner - allow both
		$entry_filter->setByName ( "_eq_partner_id" , $extraData );

		// this is the better way -
		$entry_filter->setPartnerSearchScope( $extraData );
		return $entry_filter;
	}
}
