<?php
/**
 * @package plugins.searchHistory
 * @subpackage api.objects
 */
class VidiunESearchHistoryArray extends VidiunTypedArray
{

    public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
    {
        $newArr = new VidiunESearchHistoryArray();
        if ($arr == null)
            return $newArr;

        foreach ($arr as $obj)
        {
            $nObj = new VidiunESearchHistory();
            $nObj->fromObject($obj);
            $newArr[] = $nObj;
        }

        return $newArr;
    }

    public function __construct()
    {
        parent::__construct("VidiunESearchHistory");
    }

}
