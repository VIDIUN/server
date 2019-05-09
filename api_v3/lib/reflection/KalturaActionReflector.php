<?php
/**
 * Class is used for reflecting actions in a service class whether or not it is currently generated into the 
 * VidiunServiceMap.cache, so long as the service class can be found. Internal use only.
 * 
 * @package api
 * @subpackage v3
 */
class VidiunActionReflector extends VidiunReflector
{
	/**
	 * @var string
	 */
	protected $_actionMethodName;
	
	/**
	 * @var string
	 */
	protected $_actionId;
	
	/**
	 * @var string
	 */
	protected $_actionClass;
	
	/**
	 * @var string
	 */
	protected $_serviceId;
	
	/**
	 * @var string
	 */
	protected $_actionServiceId;
	
	/**
	 * @var string
	 */
	protected $_actionName;
	
	/**
	 * @var VidiunDocCommentParser
	 */
	protected $_actionInfo;
	
	/**
	 * @var VidiunDocCommentParser
	 */
	protected $_actionClassInfo;
	
	/**
	 * @var VidiunBaseService
	 */
	protected $_actionClassInstance;
	
	
	/**
	 * @var array
	 */
	protected $_actionParams;
	
	/**
	 * 
	 * @param string $serviceId
	 * @param array $serviceCallback
	 */
	public function __construct($serviceId, $actionId, $serviceCallback)
	{
		list($this->_actionClass, $this->_actionMethodName, $this->_actionServiceId, $this->_actionName) = array_values($serviceCallback);
		
		$this->_serviceId = $serviceId;
		$this->_actionId = $actionId;
		
		$fetchFromAPCSuccess = null;
		if(function_exists('apc_fetch'))
		{
			$actionFromCache = apc_fetch("{$this->_serviceId}_{$this->_actionId}", $fetchFromAPCSuccess);
			if($fetchFromAPCSuccess && $actionFromCache[VidiunServicesMap::SERVICES_MAP_MODIFICATION_TIME] == VidiunServicesMap::getServiceMapModificationTime())
			{
				$this->_actionInfo = $actionFromCache["actionInfo"];
				$this->_actionParams = $actionFromCache["actionParams"];
				$this->_actionClassInfo = $actionFromCache["actionClassInfo"];
			}
			else
			{
				$this->cacheReflectionValues();
			}
		}
	}
	
	/**
	 * Function returns the parsed doccomment of the action method.
	 * @return VidiunDocCommentParser
	 */
	public function getActionInfo ( )
	{
		if (is_null($this->_actionInfo))
		{
			$reflectionClass = new ReflectionClass($this->_actionClass);
			$reflectionMethod = $reflectionClass->getMethod($this->_actionMethodName);
			$this->_actionInfo = new VidiunDocCommentParser($reflectionMethod->getDocComment());
		}
		return $this->_actionInfo;
	}
	
	/**
	 * Action returns array of the parameters the action method expects
	 * @return array<VidiunParamInfo>
	 */
	public function getActionParams ( )
	{
		if (is_null($this->_actionParams))
		{
			// reflect the service 
			$reflectionClass = new ReflectionClass($this->_actionClass);
			$reflectionMethod = $reflectionClass->getMethod($this->_actionMethodName);
			
			$docComment = $reflectionMethod->getDocComment();
			$reflectionParams = $reflectionMethod->getParameters();
			$this->_actionParams = array();
			foreach($reflectionParams as $reflectionParam)
			{
				$name = $reflectionParam->getName();
				if (in_array($name, $this->_reservedKeys))
					throw new Exception("Param [$name] in action [$this->_actionMethodName] is a reserved key");
					
				$parsedDocComment = new VidiunDocCommentParser( $docComment, array(
					VidiunDocCommentParser::DOCCOMMENT_REPLACENET_PARAM_NAME => $name , ) );
				$paramClass = $reflectionParam->getClass(); // type hinting for objects
				if ($paramClass)
				{
					$type = $paramClass->getName();
				}
				else //
				{
					$result = null;
					if ($parsedDocComment->param)
						$type = $parsedDocComment->param;
					else 
					{
						throw new Exception("Type not found in doc comment for param [".$name."] in action [".$this->_actionMethodName."] in service [".$this->_serviceId."]");
					}
				}
				
				$paramInfo = new VidiunParamInfo($type, $name);
				$paramInfo->setDescription($parsedDocComment->paramDescription);
				
				if ($reflectionParam->isOptional()) // for normal parameters
				{
					$paramInfo->setDefaultValue($reflectionParam->getDefaultValue());
					$paramInfo->setOptional(true);
				}
				else if ($reflectionParam->getClass() && $reflectionParam->allowsNull()) // for object parameter
				{
					$paramInfo->setOptional(true);
				}
				
				if(array_key_exists($name, $parsedDocComment->validateConstraints))
					$paramInfo->setConstraints($parsedDocComment->validateConstraints[$name]);

				if(in_array($name, $parsedDocComment->disableRelativeTimeParams, true))
					$paramInfo->setDisableRelativeTime(true);
				
				$this->_actionParams[$name] = $paramInfo;
			}
		}
		
		return $this->_actionParams;
	}
	
	/**
	 * @param string $actionName
	 * @return VidiunParamInfo
	 */
	public function getActionOutputType()
	{
		// reflect the service
		$reflectionClass = new ReflectionClass($this->_actionClass);
		$reflectionMethod = $reflectionClass->getMethod($this->_actionMethodName);
		
		$docComment = $reflectionMethod->getDocComment();
		$parsedDocComment = new VidiunDocCommentParser($docComment);
		if ($parsedDocComment->returnType)
			return new VidiunParamInfo($parsedDocComment->returnType, "output");
		
		return null;
	}
	/**
	 * @return the $_serviceId
	 */
	public function getServiceId ()
	{
		return $this->_serviceId;
	}
	/**
	 * @return VidiunDocCommentParser
	 */
	public function getActionClassInfo ()
	{
		if (is_null($this->_actionClassInfo))
		{
			$reflectionClass = new ReflectionClass($this->_actionClass);
			$this->_actionClassInfo = new VidiunDocCommentParser($reflectionClass->getDocComment());
		}
		return $this->_actionClassInfo;
	}
	
	/**
	 * @return string
	 */
	public function getActionId ()
	{
		return $this->_actionId;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function getServiceInstance()
	{
		if ( ! $this->_actionClassInstance ) 
		{
			 $this->_actionClassInstance = new $this->_actionClass();
		}
		
		return $this->_actionClassInstance;
	}
	
	public function invoke( $arguments )
	{
		$instance = $this->getServiceInstance();
		return call_user_func_array(array($instance, $this->_actionMethodName), $arguments);
	}
	/**
	 * @return the $_actionName
	 */
	public function getActionName ()
	{
		return $this->_actionName;
	}
	
	/**
	 * Transparently call the initService() of the real service class.
	 */
	public function initService(VidiunDetachedResponseProfile $responseProfile = null)
	{
		$instance = $this->getServiceInstance();
		
		if($responseProfile)
		{
			$instance->setResponseProfile($responseProfile);
		}
		
		$instance->initService($this->_actionServiceId, $this->getActionClassInfo()->serviceName, $this->getActionInfo()->action);
	}
	/**
	 * @return string
	 */
	public function getActionServiceId ()
	{
		return $this->_actionServiceId;
	}

	/**
	 * Save the following attributes into cache
	 * actionInfo, actionParams, actionClassInfo
	 */
	protected function cacheReflectionValues()
	{
		if (!function_exists('apc_store'))
			return;
			
		$servicesMapLastModTime = VidiunServicesMap::getServiceMapModificationTime();
		
		$cacheValue = array(
			VidiunServicesMap::SERVICES_MAP_MODIFICATION_TIME => $servicesMapLastModTime, 
			"actionInfo" => $this->getActionInfo(), 
			"actionParams" => $this->getActionParams(),
			"actionClassInfo" => $this->getActionClassInfo(),
		);
		
		$success = apc_store("{$this->_serviceId}_{$this->_actionId}", $cacheValue);
	}


}
