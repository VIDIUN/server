<?php
/**
 * An array of VidiunBooleanValue
 * 
 * @package api
 * @subpackage objects
 */
class VidiunBooleanValueArray extends VidiunTypedArray
{
	/**
	 * @param array<string|vBooleanValue> $strings
	 * @return VidiunBooleanValueArray
	 */
	public static function fromDbArray(array $bools = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$boolArray = new VidiunBooleanValueArray();
		if($bools && is_array($bools))
		{
			foreach($bools as $bool)
			{
				$boolObject = new VidiunBooleanValue();
				
				if($bool instanceof vValue)
				{
					$boolObject->fromObject($bool, $responseProfile);;
				}
				else
				{					
					$boolObject->value = $bool;
				}
				
				$boolArray[] = $boolObject;
			}
		}
		return $boolArray;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunBooleanValue");
	}
}
