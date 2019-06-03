<?php
/**
 * @package plugins.playReady
 * @subpackage api.objects
 */
class VidiunPlayReadyRightArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunPlayReadyRightArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = self::getInstanceByDbObject($obj);
			if($nObj)
			{
				$nObj->fromObject($obj, $responseProfile);
				$newArr[] = $nObj;
			}
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunPlayReadyRight");	
	}
	
	private static function getInstanceByDbObject($obj)
	{
		if($obj instanceof PlayReadyCopyRight)
			return new VidiunPlayReadyCopyRight();
		if($obj instanceof PlayReadyPlayRight)
			return new VidiunPlayReadyPlayRight();
			
		return null;
	}
}