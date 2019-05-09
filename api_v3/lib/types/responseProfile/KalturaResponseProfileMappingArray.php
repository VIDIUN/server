<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunResponseProfileMappingArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunResponseProfileMappingArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$dbClass = get_class($obj);
			if ($dbClass == 'vResponseProfileMapping')
				$nObj = new VidiunResponseProfileMapping();
			else
				$nObj = VidiunPluginManager::loadObject('VidiunResponseProfileMapping', $dbClass);

			if (is_null($nObj))
				VidiunLog::err('Failed to load api object for '.$dbClass);

			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunResponseProfileMapping");	
	}
}