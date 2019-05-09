<?php
/**
 * @package plugins.group
 * @subpackage api.objects
 */

class VidiunGroupArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunGroupArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunGroup();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

	public function __construct( )
	{
		return parent::__construct ( "VidiunGroup" );
	}
}