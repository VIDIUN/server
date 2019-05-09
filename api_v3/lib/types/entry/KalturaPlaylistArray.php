<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPlaylistArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunPlaylistArray();
		if ( $arr == null ) return $newArr;
		foreach ( $arr as $obj )
		{
    		$nObj = VidiunEntryFactory::getInstanceByType($obj->getType());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunPlaylist");	
	}
}