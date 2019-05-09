<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchHighlightArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunESearchHighlight");
	}

	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunESearchHighlightArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunESearchHighlight();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}
}
