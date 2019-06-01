<?php
/**
 * An array of VidiunKeyBooleanValue
 * 
 * @package api
 * @subpackage objects
 */
class VidiunKeyBooleanValueArray extends VidiunTypedArray
{
	public static function fromDbArray(array $pairs = null)
	{
		return self::fromKeyValueArray($pairs);
	}
	
	protected function appendFromArray(array $pairs, $prefix = '')
	{
		foreach($pairs as $key => $value)
		{
			if(is_array($value))
			{
				$this->appendFromArray($value, "$key.");
				continue;
			}
			
			$pairObject = new VidiunKeyBooleanValue();
			$pairObject->key = $prefix . $key;
			$pairObject->value = (bool)$value;
			$this[] = $pairObject;
		}
	}
	
	public static function fromKeyValueArray(array $pairs = null)
	{
		$pairsArray = new VidiunKeyBooleanValueArray();
		if($pairs && is_array($pairs))
		{
			foreach($pairs as $key => $value)
			{
				if(is_array($value))
				{
					$pairsArray->appendFromArray($value, "$key.");
					continue;
				}
				
				$pairObject = new VidiunKeyBooleanValue();
				$pairObject->key = $key;
				$pairObject->value = (bool)$value;
				$pairsArray[] = $pairObject;
			}
		}
		return $pairsArray;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunKeyBooleanValue");
	}
	
	public function toObjectsArray()
	{
		$ret = array();
		foreach ($this->toArray() as $keyValueObject)
		{
			/* @var $keyValueObject VidiunKeyBooleanValue */
			$ret[$keyValueObject->key] = $keyValueObject->value;
		}
		
		return $ret;
	}
}
