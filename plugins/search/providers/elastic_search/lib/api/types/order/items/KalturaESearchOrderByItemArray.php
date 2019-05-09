<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchOrderByItemArray extends VidiunTypedArray
{

    public function __construct()
    {
        return parent::__construct("VidiunESearchOrderByItem");
    }
	
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		VidiunLog::debug(print_r($arr, true));
		$newArr = new VidiunESearchOrderByItemArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj)
		{
			switch (get_class($obj))
			{
				case 'ESearchEntryOrderByItem':
					$nObj = new VidiunESearchEntryOrderByItem();
					break;
				
				case 'ESearchCategoryOrderByItem':
					$nObj = new VidiunESearchCategoryOrderByItem();
					break;
				
				case 'ESearchUserOrderByItem':
					$nObj = new VidiunESearchUserOrderByItem();
					break;
				
				case 'ESearchUserOrderByItem':
					$nObj = new VidiunESearchGrou();
					break;
				
				default:
					$nObj = VidiunPluginManager::loadObject('VidiunESearchOrderByItem', get_class($obj));
					break;
			}
			
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
}
