<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPlayerDeliveryTypesArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunPlayerDeliveryType");
	}

	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$ret = new VidiunPlayerDeliveryTypesArray();
		foreach($arr as $id => $item)
		{
			$obj = new VidiunPlayerDeliveryType();
			$obj->id = $id;
			$obj->fromArray($item);
			$obj->enabledByDefault = (bool)$obj->enabledByDefault;
				
			if(isset($item['flashvars']))
				$obj->flashvars = VidiunKeyValueArray::fromDbArray($item['flashvars']);
				
			$ret[] = $obj;
		}
		return $ret;
	}
}