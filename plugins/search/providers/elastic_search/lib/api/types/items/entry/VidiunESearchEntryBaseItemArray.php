<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchEntryBaseItemArray extends VidiunTypedArray
{
	
	public function __construct()
	{
		return parent::__construct("VidiunESearchEntryBaseItem");
	}
	
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		VidiunLog::debug(print_r($arr, true));
		$newArr = new VidiunESearchEntryBaseItemArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj)
		{
			switch (get_class($obj))
			{
				case 'ESearchEntryItem':
					$nObj = new VidiunESearchEntryItem();
					break;
				
				case 'ESearchOperator':
					$nObj = new VidiunESearchEntryOperator();
					break;
				
				case 'ESearchMetadataItem':
					$nObj = new VidiunESearchEntryMetadataItem();
					break;
				
				case 'ESearchCuePointItem':
					$nObj = new VidiunESearchCuePointItem();
					break;
				
				case 'ESearchCaptionItem':
					$nObj = new VidiunESearchCaptionItem();
					break;
				
				case 'ESearchCategoryEntryNameItem':
					$nObj = new VidiunESearchCategoryEntryItem();
					break;
				
				case 'ESearchUnifiedItem':
					$nObj = new VidiunESearchUnifiedItem();
					break;
				
				case 'ESearchNestedOperator':
					$nObj = new VidiunESearchNestedOperator();
					break;
					
				default:
					$nObj = VidiunPluginManager::loadObject('VidiunESearchEntryBaseItem', get_class($obj));
					break;
			}
			
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
}
