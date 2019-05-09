<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.objects
 */
class VidiunDropFolderFileArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDropFolderFileArray();
		foreach ( $arr as $obj )
		{
			$nObj = VidiunDropFolderFile::getInstanceByType($obj->getType());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
		 
	}
	
	public function __construct( )
	{
		return parent::__construct ( 'VidiunDropFolderFile' );
	}
}
