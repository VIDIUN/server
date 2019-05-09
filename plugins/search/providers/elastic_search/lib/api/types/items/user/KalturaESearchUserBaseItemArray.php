<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchUserBaseItemArray extends VidiunTypedArray
{
	
	public function __construct()
	{
		return parent::__construct("VidiunESearchUserBaseItem");
	}
	
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		VidiunLog::debug(print_r($arr, true));
		$newArr = new VidiunESearchUserBaseItemArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj)
		{
			switch (get_class($obj))
			{
				case 'ESearchUserItem':
					$nObj = new VidiunESearchUserItem();
					break;
				
				default:
					$nObj = VidiunPluginManager::loadObject('VidiunESearchUserBaseItem', get_class($obj));
					break;
			}
			
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
}
