<?php

abstract class WSBaseObject extends SoapObject {
	
	abstract function getVidiunObject();
	
	public function toVidiunObject() {
		$vidiunObj = $this->getVidiunObject();
		self::cloneObject($this, $vidiunObj);
		return $vidiunObj;
	}
	
	public function fromVidiunObject($vidiunObj) {
		self::cloneObject($vidiunObj, $this);
	}
	
	protected static function cloneObject($objA, $objB) {
		$reflect = new ReflectionClass($objA);
		foreach($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop)
		{
			$name = $prop->getName();
			$value = $prop->getValue($objA);
			
			if ($value instanceof WSBaseObject) {
				$value = $value->toVidiunObject();
			} else if($value instanceof SoapArray) {
				/**
				 * @var SoapArray $value
				 */
				$arr = $value->toArray();
				$newObj = array();
				foreach($arr as $val) {
					if ($val instanceof WSBaseObject) {
						$newObj[] = $val->toVidiunObject();
					} else {
						$newObj[] = $val;
					}
				} 
				$value = $newObj;
			}
			
			$objB->$name = $value; 
		}
	}
}

