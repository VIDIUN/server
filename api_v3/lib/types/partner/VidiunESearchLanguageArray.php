<?php
/**
 * @package api
 * @subpackage objects
 */

class VidiunESearchLanguageArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunESearchLanguageItem");
	}

	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunESearchLanguageArray();
		if($arr && is_array($arr))
		{
			foreach($arr as $item)
			{
				$arrayObject = new VidiunESearchLanguageItem();
				$arrayObject->eSerachLanguage = $item;
				$newArr[] = $arrayObject;
			}
		}
		return $newArr;
	}

	public function toObjectsArray()
	{
		$ret = array();
		foreach ($this->toArray() as $item)
		{
			/* @var $item VidiunESearchLanguageItem */
			$ret[] = $item->eSerachLanguage;
		}

		return array_unique($ret);
	}
}


