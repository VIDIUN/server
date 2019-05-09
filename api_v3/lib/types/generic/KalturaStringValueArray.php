<?php
/**
 * An array of VidiunStringValue
 * 
 * @package api
 * @subpackage objects
 */
class VidiunStringValueArray extends VidiunTypedArray
{
	/**
	 * @param array<string|vStringValue> $strings
	 * @return VidiunStringValueArray
	 */
	public static function fromDbArray(array $strings = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$stringArray = new VidiunStringValueArray();
		if($strings && is_array($strings))
		{
			foreach($strings as $string)
			{
				$stringObject = new VidiunStringValue();
				
				if($string instanceof vValue)
				{
					$stringObject->fromObject($string, $responseProfile);;
				}
				else
				{					
					$stringObject->value = $string;
				}
				
				$stringArray[] = $stringObject;
			}
		}
		return $stringArray;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunStringValue");
	}
}
