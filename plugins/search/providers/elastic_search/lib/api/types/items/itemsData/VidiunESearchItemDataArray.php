<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchItemDataArray extends VidiunTypedArray
{

    public function __construct()
    {
        return parent::__construct("VidiunESearchItemData");
    }

	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunESearchItemDataArray();
		foreach ( $arr as $obj )
		{
			$nObj = VidiunPluginManager::loadObject('VidiunESearchItemData', $obj->getType());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

}
