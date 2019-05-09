<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunMediaEntryFilterForPlaylistArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunMediaEntryFilterForPlaylist();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunMediaEntryFilterForPlaylist" );
	}
}
