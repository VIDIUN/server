<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchCategoryBaseItemArray extends VidiunTypedArray
{
	
	public function __construct()
	{
		return parent::__construct("VidiunESearchCategoryBaseItem");
	}
	
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		VidiunLog::debug(print_r($arr, true));
		$newArr = new VidiunESearchCategoryBaseItemArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj)
		{
			switch (get_class($obj))
			{
				case 'ESearchCategoryItem':
					$nObj = new VidiunESearchCategoryItem();
					break;
				
				default:
					$nObj = VidiunPluginManager::loadObject('VidiunESearchCategoryBaseItem', get_class($obj));
					break;
			}
			
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
}
