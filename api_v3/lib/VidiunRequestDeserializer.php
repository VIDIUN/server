<?php
/**
 * @package api
 * @subpackage v3
 */
class VidiunRequestDeserializer
{
	private $params = null;
	private $paramsGrouped = array();
	private $objects = array();
	private $extraParams = array("format", "vs", "fullObjects");
	private $disableRelativeTime = false;

	const PREFIX = ":";

	public function __construct($params)
	{
		$this->params = $params;
		$this->groupParams();
	}

	public function groupParams()
	{
		// group the params by prefix
		foreach($this->params as $key => $value)
		{
			$path = explode(self::PREFIX, $key);
			$this->setElementByPath($this->paramsGrouped, $path, $value);
		}
	}
	
	private function setElementByPath(&$array, $path, $value)
	{
		$tmpArray = &$array;
		while(($key = array_shift($path)) !== null)
		{
			if ($key == '-' && count($path) == 0)
				break;
			
			if (!isset($tmpArray[$key]) || !is_array($tmpArray[$key]))
				$tmpArray[$key] = array();
				
			if (count($path) == 0)
				$tmpArray[$key] = $value;
			else
				$tmpArray = &$tmpArray[$key];	
		}
		
		$array = &$tmpArray;
	}
	
	function set_element(&$path, $data) {
	    return ($key = array_pop($path)) ? $this->set_element($path, array($key=>$data)) : $data;
	}

	public function buildActionArguments(&$actionParams)
	{
		
		$serviceArguments = array();
		foreach($actionParams as &$actionParam)
		{
			/* @var VidiunParamInfo $actionParam */
			$type = $actionParam->getType();
			$name = $actionParam->getName();

			$this->disableRelativeTime = $actionParam->getDisableRelativeTime();
			
			if ($actionParam->isSimpleType($type))
			{
				if (array_key_exists($name, $this->paramsGrouped))
				{
					$value = $this->castSimpleType($type, $this->paramsGrouped[$name]);
					if(!vXml::isXMLValidContent($value))
						throw new VidiunAPIException(VidiunErrors::INVALID_PARAMETER_CHAR, $name);
						
					$this->validateParameter($name, $value, $actionParam);
					$serviceArguments[] = $value;
					continue;
				}
				
				if ($actionParam->isOptional())
				{
					$serviceArguments[] = $this->castSimpleType($type, $actionParam->getDefaultValue());
					continue;
				}
			}
			
			if ($actionParam->isFile()) // File
			{
				if (array_key_exists($name, $this->paramsGrouped)) 
				{
					$fileData = $this->paramsGrouped[$name];
					self::validateFile($fileData);
					$serviceArguments[] = $fileData;
					continue;
				}
				
				if ($actionParam->isOptional()) 
				{
					$serviceArguments[] = null;
					continue;
				} 	
			}
			
			if ($actionParam->isEnum()) // enum
			{
				if (array_key_exists($name, $this->paramsGrouped))
				{
					$enumValue = $this->paramsGrouped[$name];
					if(strtolower($enumValue) == 'true')
						$enumValue = 1;
					if(strtolower($enumValue) == 'false')
						$enumValue = 0;
						
					if (!$actionParam->getTypeReflector()->checkEnumValue($enumValue))
						throw new VidiunAPIException(VidiunErrors::INVALID_ENUM_VALUE, $enumValue, $name, $actionParam->getType());
					
					if($type == 'VidiunNullableBoolean')
					{
						$serviceArguments[] = VidiunNullableBoolean::toBoolean($enumValue);
						continue;
					}
					
					$serviceArguments[] = $this->castSimpleType("int", $enumValue);
					continue;
				}
				
				if ($actionParam->isOptional())
				{
					$serviceArguments[] = $this->castSimpleType("int", $actionParam->getDefaultValue());
					continue;
				}
			}
			
			if ($actionParam->isStringEnum()) // string enum or dynamic
			{
				if (array_key_exists($name, $this->paramsGrouped))
				{
					$enumValue = $this->paramsGrouped[$name];
					if (!$actionParam->getTypeReflector()->checkStringEnumValue($enumValue))
						throw new VidiunAPIException(VidiunErrors::INVALID_ENUM_VALUE, $enumValue, $name, $actionParam->getType());
					
					$serviceArguments[] = $enumValue;
					continue;
				}
				
				if ($actionParam->isOptional())
				{
					$serviceArguments[] = $actionParam->getDefaultValue();
					continue;
				}
			}
			
			if ($actionParam->isArray()) // array
			{
				$arrayObj = new $type();
				if (isset($this->paramsGrouped[$name]) && is_array($this->paramsGrouped[$name]))
				{	
					if ($actionParam->isAssociativeArray())
					{
						foreach($this->paramsGrouped[$name] as $arrayItemKey => $arrayItemParams)
						{
							$arrayObj[$arrayItemKey] = $this->buildObject($actionParam->getArrayTypeReflector(), $arrayItemParams, $name);
						}
					}
					else
					{
						ksort($this->paramsGrouped[$name]);
						foreach($this->paramsGrouped[$name] as $arrayItemParams)
						{
							$arrayObj[] = $this->buildObject($actionParam->getArrayTypeReflector(), $arrayItemParams, $name);
						}
					}
				}
				$serviceArguments[] = $arrayObj;
				continue;
			}
			
			if (isset($this->paramsGrouped[$name])) // object 
			{
				$serviceArguments[] = $this->buildObject($actionParam->getTypeReflector(), $this->paramsGrouped[$name], $name);
				continue;
			}
			
			if ($actionParam->isOptional()) // object that is optional
			{
				$serviceArguments[] = null;
				continue;
			}

			throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, $name);
		}
		return $serviceArguments;
	}
	
	/**
	 * @return VidiunDetachedResponseProfile
	 */
	public function getResponseProfile($paramName = 'responseProfile') {
		if(!isset($this->paramsGrouped[$paramName])){
			return null;
		}
		
		$partnerId = vCurrentContext::getCurrentPartnerId();
		myPartnerUtils::addPartnerToCriteria('ResponseProfile', $partnerId, true, "$partnerId,0");
		
		$responseProfile = null;
		if(isset($this->paramsGrouped[$paramName]['id'])){
			$responseProfile = ResponseProfilePeer::retrieveByPK($this->paramsGrouped[$paramName]['id']);
		}
		if(isset($this->paramsGrouped[$paramName]['systemName'])){
			$responseProfile = ResponseProfilePeer::retrieveBySystemName($this->paramsGrouped[$paramName]['systemName']);
		}
		if($responseProfile){
			return new VidiunResponseProfile($responseProfile);
		}
		
		$typeReflector = VidiunTypeReflectorCacher::get('VidiunDetachedResponseProfile');
		return $this->buildObject($typeReflector, $this->paramsGrouped[$paramName], $paramName);
	}
	
	protected function validateParameter($name, $value, $constraintsObj) {
		$constraints = $constraintsObj->getConstraints();
		if(array_key_exists(VidiunDocCommentParser::MIN_LENGTH_CONSTRAINT, $constraints))
			$this->validateMinLength($name, $value, $constraints[VidiunDocCommentParser::MIN_LENGTH_CONSTRAINT]);
		if(array_key_exists(VidiunDocCommentParser::MAX_LENGTH_CONSTRAINT, $constraints))
			$this->validateMaxLength($name, $value, $constraints[VidiunDocCommentParser::MAX_LENGTH_CONSTRAINT]);
		if(array_key_exists(VidiunDocCommentParser::MIN_VALUE_CONSTRAINT, $constraints))
			$this->validateMinValue($name, $value, $constraints[VidiunDocCommentParser::MIN_VALUE_CONSTRAINT]);
		if(array_key_exists(VidiunDocCommentParser::MAX_VALUE_CONSTRAINT, $constraints))
			$this->validateMaxValue($name, $value, $constraints[VidiunDocCommentParser::MAX_VALUE_CONSTRAINT]);
	}
	
	protected function validateMinLength($name, $objectValue, $constraint) {
		if(strlen($objectValue) < $constraint)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_MIN_LENGTH, $name, $constraint);
	}
	
	protected function validateMaxLength($name, $objectValue, $constraint) {
		if(strlen($objectValue) > $constraint)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_MAX_LENGTH, $name, $constraint);
	}
	
	protected function validateMinValue($name, $objectValue, $constraint) {
		if($objectValue < $constraint)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_MIN_VALUE, $name, $constraint);
	}
	
	protected function validateMaxValue($name, $objectValue, $constraint) {
		if($objectValue > $constraint)
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_MAX_VALUE, $name, $constraint);
	}
	
	private function validateFile($fileData) 
	{
		if (!isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
			$msg = "An error occured while uploading file.";
			VidiunLog::log($msg . ' ' . print_r($fileData, true));
			throw new VidiunAPIException(VidiunErrors::UPLOAD_ERROR);
		}
	}

	private function buildObject(VidiunTypeReflector $typeReflector, &$params, $objectName)
	{
		if (!is_array($params))
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_WRONG_FORMAT, $objectName, 'array' );
		// if objectType was specified, we will use it only if the anotation type is it's base type
		if (array_key_exists("objectType", $params))
		{
            $possibleType = $params["objectType"];
            if (strtolower($possibleType) !== strtolower($typeReflector->getType())) // reflect only if type is different
            {
                if ($typeReflector->isParentOf($possibleType)) // we know that the objectType that came from the user is right, and we can use it to initiate the object\
                {
                    $newTypeReflector = VidiunTypeReflectorCacher::get($possibleType);
                    if($newTypeReflector)
                    	$typeReflector = $newTypeReflector;
                }
            }
		}
		
		if($typeReflector->isAbstract())
			throw new VidiunAPIException(VidiunErrors::OBJECT_TYPE_ABSTRACT, $typeReflector->getType());
		 
	    $class = $typeReflector->getType();
		$obj = new $class;
		$properties = $typeReflector->getProperties();
		
		foreach($params as $name => $value)
		{
			$isNull = false;
			if (vString::endsWith($name, '__null'))
			{
				$name = str_replace('__null', '', $name);
				$isNull = true;
			}
			
			if (!array_key_exists($name, $properties))
			{
				continue;
			}
			
			$property = $properties[$name];
			/* @var $property VidiunPropertyInfo */
			$type = $property->getType();
			
			if ($isNull && !$property->isArray())
			{
				$obj->$name = new VidiunNullField();
				continue;
			}
							
			if ($property->isSimpleType())
			{
                if ($property->isTime())
                    $type = "time";
				$value = $this->castSimpleType($type, $value);
				if(!vXml::isXMLValidContent($value))
					throw new VidiunAPIException(VidiunErrors::INVALID_PARAMETER_CHAR, $name);
				$this->validateParameter($name, $value, $property);
				$obj->$name = $value;
				continue;
			}
			
			if ($property->isEnum())
			{
				if(strtolower($value) == 'true')
					$value = 1;
				if(strtolower($value) == 'false')
					$value = 0;
				if (!$property->getTypeReflector()->checkEnumValue($value))
					throw new VidiunAPIException(VidiunErrors::INVALID_ENUM_VALUE, $value, $name, $property->getType());
			
				if($type == 'VidiunNullableBoolean')
				{
					$obj->$name = VidiunNullableBoolean::toBoolean($value);
					continue;
				}
				
				$obj->$name = $this->castSimpleType("int", $value);
				continue;
			}
			
			if ($property->isStringEnum())
			{
				if (!$property->getTypeReflector()->checkStringEnumValue($value))
					throw new VidiunAPIException(VidiunErrors::INVALID_ENUM_VALUE, $value, $name, $property->getType());
					
				$value = $this->castSimpleType("string", $value);
				if(!vXml::isXMLValidContent($value))
					throw new VidiunAPIException(VidiunErrors::INVALID_PARAMETER_CHAR, $name);
				$obj->$name = $value;
				continue;
			}
			
			if ($property->isArray() && is_array($value))
			{
				$arrayObj = new $type();
				if($property->isAssociativeArray())
				{
					foreach($value as $arrayItemKey => $arrayItemParams)
					{
						if($arrayItemKey === '-')
							break;
						$arrayObj[$arrayItemKey] = $this->buildObject($property->getArrayTypeReflector(), $arrayItemParams, "{$objectName}:$name");
					}
				}
				else
				{
					ksort($value);
					foreach($value as $arrayItemKey => $arrayItemParams)
					{
						if($arrayItemKey === '-')
							break;
						$arrayObj[] = $this->buildObject($property->getArrayTypeReflector(), $arrayItemParams, "{$objectName}:$name");
					}
				}
				$obj->$name = $arrayObj;
				continue;
			}
			
			if ($property->isComplexType() && is_array($value))
			{
				$obj->$name = $this->buildObject($property->getTypeReflector(), $value, "{$objectName}:$name");
				continue;
			}
			
			if ($property->isFile())
			{
				$obj->$name = $value;
				continue;
			}
		}
		return $obj;
	}
	
	private function castSimpleType($type, $var)
	{
		switch($type)
		{
			case "int":
				return (int)$var;
			case "string":
				return vString::stripUtf8InvalidChars((string)$var);
			case "bool":
				if (strtolower($var) === "false")
					return false;
				else
					return (bool)$var;
			case "float":
				return (float)$var;
			case "bigint":
				return (double)$var;
			case "time":
				if (!$this->disableRelativeTime)
					$var = vTime::getRelativeTime($var);

				return $var;
		}
		
		return null;
	}
}
