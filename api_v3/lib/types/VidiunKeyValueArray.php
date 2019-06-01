<?php
/**
 * An array of VidiunKeyValue
 * 
 * @package api
 * @subpackage objects
 */
class VidiunKeyValueArray extends VidiunTypedArray
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
			
			$pairObject = new VidiunKeyValue();
			$pairObject->key = $prefix . $key;
			$pairObject->value = $value;
			$this[] = $pairObject;
		}
	}
	
	public static function fromKeyValueArray(array $pairs = null)
	{
		$pairsArray = new VidiunKeyValueArray();
		if($pairs && is_array($pairs))
		{
			foreach($pairs as $key => $value)
			{
				if(is_array($value))
				{
					$pairsArray->appendFromArray($value, "$key.");
					continue;
				}
				
				$pairObject = new VidiunKeyValue();
				$pairObject->key = $key;
				$pairObject->value = $value;
				$pairsArray[] = $pairObject;
			}
		}
		return $pairsArray;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunKeyValue");
	}
	
	public function toObjectsArray()
	{
		$ret = array();
		foreach ($this->toArray() as $keyValueObject)
		{
			/* @var $keyValueObject VidiunKeyValue */
			$ret[$keyValueObject->key] = $keyValueObject->value;
		}
		
		return $ret;
	}
}
