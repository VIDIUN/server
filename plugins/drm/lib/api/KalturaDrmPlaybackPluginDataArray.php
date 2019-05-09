<?php
/**
 * @package plugins.drm
 * @subpackage api.objects
 */
class VidiunDrmPlaybackPluginDataArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDrmPlaybackPluginDataArray();
		foreach ( $arr as $obj )
		{
			$nObj = VidiunPluginManager::loadObject('VidiunDrmPlaybackPluginData', get_class($obj));
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
		 
	}
	
	public function __construct( )
	{
		return parent::__construct ( 'VidiunDrmPlaybackPluginData' );
	}
}
