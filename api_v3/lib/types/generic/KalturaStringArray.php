<?php
/**
 * An array of VidiunString
 * 
 * @package api
 * @subpackage objects
 */
class VidiunStringArray extends VidiunTypedArray
{
	public static function fromDbArray(array $strings = null)
	{
		return self::fromStringArray($strings);
	}
	
	public static function fromStringArray(array $strings = null)
	{
		$stringArray = new VidiunStringArray();
		if($strings && is_array($strings))
		{
			foreach($strings as $string)
			{
				$stringObject = new VidiunString();
				$stringObject->value = $string;
				$stringArray[] = $stringObject;
			}
		}
		return $stringArray;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunString");
	}

}
