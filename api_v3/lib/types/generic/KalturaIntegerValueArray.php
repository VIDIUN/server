<?php
/**
 * An array of VidiunIntegerValue
 * 
 * @package api
 * @subpackage objects
 */
class VidiunIntegerValueArray extends VidiunTypedArray
{
	/**
	 * @param array<string|vIntegerValue> $strings
	 * @return VidiunIntegerValueArray
	 */
	public static function fromDbArray(array $ints = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$intArray = new VidiunIntegerValueArray();
		if($ints && is_array($ints))
		{
			foreach($ints as $int)
			{
				$intObject = new VidiunIntegerValue();
				
				if($int instanceof vValue)
				{
					$intObject->fromObject($int, $responseProfile);;
				}
				else
				{					
					$intObject->value = $int;
				}
				
				$intArray[] = $intObject;
			}
		}
		return $intArray;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunIntegerValue");
	}
}
