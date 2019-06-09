<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.objects
 */
class VidiunDropFolderArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDropFolderArray();
		foreach ( $arr as $obj )
		{
		    $nObj = VidiunDropFolder::getInstanceByType($obj->getType());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
		 
	}
	
	public function __construct( )
	{
		return parent::__construct ( 'VidiunDropFolder' );
	}
}
