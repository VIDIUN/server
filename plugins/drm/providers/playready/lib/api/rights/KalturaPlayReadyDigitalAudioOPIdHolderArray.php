<?php
/**
 * @package plugins.playReady
 * @subpackage api.objects
 */
class VidiunPlayReadyDigitalAudioOPIdHolderArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunPlayReadyDigitalAudioOPIdHolderArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $type)
		{
    		$nObj = new VidiunPlayReadyDigitalAudioOPIdHolder();
			$nObj->type = $type;
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("VidiunPlayReadyDigitalAudioOPIdHolder");	
	}
}