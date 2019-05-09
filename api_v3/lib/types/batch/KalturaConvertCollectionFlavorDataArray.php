<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunConvertCollectionFlavorDataArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunConvertCollectionFlavorDataArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunConvertCollectionFlavorData();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunConvertCollectionFlavorData" );
	}
}
