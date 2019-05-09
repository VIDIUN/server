<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunEffectsArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunEffectsArray();
		if(is_null($arr))
			return $newArr;

		foreach($arr as $obj)
		{
			$nObj = new VidiunEffect();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

	public function __construct()
	{
		parent::__construct("VidiunEffect");
	}
}