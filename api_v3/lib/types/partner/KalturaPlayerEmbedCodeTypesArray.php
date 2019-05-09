<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPlayerEmbedCodeTypesArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunPlayerEmbedCodeType");
	}
	
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$ret = new VidiunPlayerEmbedCodeTypesArray();
		foreach($arr as $id => $item)
		{
			$obj = new VidiunPlayerEmbedCodeType();
			$obj->id = $id;
			$obj->fromArray($item);
			$ret[] = $obj;
		}
		return $ret;
	}
}