<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUserEntryArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunUserEntryArray();
		foreach($arr as $obj)
		{
			/* @var $obj UserEntry */
			$nObj = VidiunUserEntry::getInstanceByType($obj->getType());
			if (!$nObj)
			{
				throw new VidiunAPIException(VidiunErrors::USER_ENTRY_OBJECT_TYPE_ERROR, $obj->getType(), $obj->getId());
			}
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

	public function __construct( )
	{
		return parent::__construct ( "VidiunUserEntry" );
	}
}
