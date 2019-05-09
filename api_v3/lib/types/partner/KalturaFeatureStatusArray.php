<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFeatureStatusArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunFeatureStatusArray();
		foreach($arr as $obj)
		{
			if ($obj){
				$nObj = new VidiunFeatureStatus();
				$nObj->fromObject($obj, $responseProfile);
				$newArr[] = $nObj;
			}
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunFeatureStatus" );
	}
}