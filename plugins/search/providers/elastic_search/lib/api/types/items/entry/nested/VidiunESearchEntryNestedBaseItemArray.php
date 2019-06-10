<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchEntryNestedBaseItemArray extends VidiunTypedArray
{
	
	public function __construct()
	{
		return parent::__construct("VidiunESearchEntryNestedBaseItem");
	}
	
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		VidiunLog::debug(print_r($arr, true));
		$newArr = new VidiunESearchEntryNestedBaseItemArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj)
		{
			switch (get_class($obj))
			{
				case 'ESearchMetadataItem':
					$nObj = new VidiunESearchEntryMetadataItem();
					break;
				
				case 'ESearchCuePointItem':
					$nObj = new VidiunESearchCuePointItem();
					break;
				
				case 'ESearchCaptionItem':
					$nObj = new VidiunESearchCaptionItem();
					break;
				
				case 'ESearchNestedOperator':
					$nObj = new VidiunESearchNestedOperator();
					break;
				
				default:
					$nObj = VidiunPluginManager::loadObject('VidiunESearchEntryNestedBaseItem', get_class($obj));
					break;
			}
			
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
}
