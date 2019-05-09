<?php

/**
 * Subclass for representing a row from the 'conversion_profile' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class ConversionProfile extends BaseConversionProfile implements IBaseObject
{
	const GLOBAL_PARTNER_PROFILE = 0; 					// every profile that belongs to partner_id 0 is global and can be shared between partners
	const CONVERSION_PROFILE_UNKNOWN = -1; 			// vidiun's default conversion profile id
	const DEFAULT_COVERSION_PROFILE_ID = 0; 			// vidiun's default conversion profile id
	const DEFAULT_COVERSION_PROFILE_TYPE = "med"; 		// vidiun's default conversion profile type
	const DEFAULT_DOWNLOAD_PROFILE_ID = 1; 				// vidiun's default download profile id
	const DEFAULT_DOWNLOAD_PROFILE_TYPE = "download"; 	// vidiun's default download profile type
	
	const DEFAULT_TRIAL_COVERSION_PROFILE_TYPE = 1001;	// vidiun's default conversion profile for trial accounts

	
	const CONVERSION_PROFILE_CREATION_MODE_MANUAL = 1;
	const CONVERSION_PROFILE_CREATION_MODE_VMC = 2;
	const CONVERSION_PROFILE_CREATION_MODE_AUTOMATIC = 3;
	
	public function getConversionParams( &$fallback_mode = null )
	{
		$fallback_mode = "";
		return ConversionParamsPeer::retrieveByConversionProfile( $this , $fallback_mode );
	}
}
