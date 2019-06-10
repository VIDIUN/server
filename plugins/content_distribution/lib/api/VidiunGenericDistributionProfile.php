<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunGenericDistributionProfile extends VidiunDistributionProfile
{
	/**
	 * @insertonly
	 * @var int
	 */
	public $genericProviderId;
	
	/**
	 * @var VidiunGenericDistributionProfileAction
	 */
	public $submitAction;
	
	/**
	 * @var VidiunGenericDistributionProfileAction
	 */
	public $updateAction;	
	
	/**
	 * @var VidiunGenericDistributionProfileAction
	 */
	public $deleteAction;	
	
	/**
	 * @var VidiunGenericDistributionProfileAction
	 */
	public $fetchReportAction;
	
	/**
	 * @var string
	 */
	public $updateRequiredEntryFields;
	
	/**
	 * @var string
	 */
	public $updateRequiredMetadataXPaths;
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)  
	 */
	private static $map_between_objects = array 
	(
		'genericProviderId',	
	);
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	private static $actions = array 
	(
		'submit',
		'update',
		'delete',
		'fetchReport',
	);
	
	public function toObject($object = null, $skip = array())
	{
		if(is_null($object))
			$object = new GenericDistributionProfile();
			
		$object = parent::toObject($object, $skip);
			
		foreach(self::$actions as $action)
		{
			$actionAttribute = "{$action}Action";
			if(!$this->$actionAttribute)
				continue;
				
			$typeReflector = VidiunTypeReflectorCacher::get(get_class($this->$actionAttribute));
			
			foreach ( $this->$actionAttribute->getMapBetweenObjects() as $this_prop => $object_prop )
			{
			 	if ( is_numeric( $this_prop) ) $this_prop = $object_prop;
				if (in_array($this_prop, $skip)) continue;
				
				$value = $this->$actionAttribute->$this_prop;
				if ($value !== null)
				{
					$propertyInfo = $typeReflector->getProperty($this_prop);
					if (!$propertyInfo)
					{
			            VidiunLog::alert("property [$this_prop] was not found on object class [" . get_class($object) . "]");
					}
					else if ($propertyInfo->isDynamicEnum())
					{
						$propertyType = $propertyInfo->getType();
						$enumType = call_user_func(array($propertyType, 'getEnumClass'));
						$value = vPluginableEnumsManager::apiToCore($enumType, $value);
					}
					
					if ($value !== null)
					{
						$setter_callback = array($object, "set{$object_prop}");
						if (is_callable($setter_callback))
					 	    call_user_func_array($setter_callback, array($value, $action));
				 	    else 
			            	VidiunLog::alert("setter for property [$object_prop] was not found on object class [" . get_class($object) . "]");
					}
				}
			}
		}
		
		$object->setUpdateRequiredEntryFields(explode(',', $this->updateRequiredEntryFields));
		$object->setUpdateRequiredMetadataXpaths(explode(',', $this->updateRequiredMetadataXPaths));
		
		return $object;		
	}

	public function doFromObject($object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($object, $responseProfile);
		
		foreach(self::$actions as $action)
		{
			if(!$this->shouldGet('$actionAttribute', $responseProfile))
				continue;
				
			$actionAttribute = "{$action}Action";
			
			if(!$this->$actionAttribute)
				$this->$actionAttribute = new VidiunGenericDistributionProfileAction();
				
			$reflector = VidiunTypeReflectorCacher::get(get_class($this->$actionAttribute));
			$properties = $reflector->getProperties();
			
			foreach ( $this->$actionAttribute->getMapBetweenObjects() as $this_prop => $object_prop )
			{
				if ( is_numeric( $this_prop) ) 
				    $this_prop = $object_prop;
				    
				if(!isset($properties[$this_prop]) || $properties[$this_prop]->isWriteOnly())
					continue;
					
	            $getter_callback = array ( $object ,"get{$object_prop}"  );
	            if (is_callable($getter_callback))
	            {
	                $value = call_user_func($getter_callback, $action);
	                if($properties[$this_prop]->isDynamicEnum())
	                {
						$propertyType = $properties[$this_prop]->getType();
						$enumType = call_user_func(array($propertyType, 'getEnumClass'));
	                	$value = vPluginableEnumsManager::coreToApi($enumType, $value);
	                }
	                	
	                $this->$actionAttribute->$this_prop = $value;
	            }
	            else
	            { 
	            	VidiunLog::alert("getter for property [$object_prop] was not found on object class [" . get_class($object) . "]");
	            }
			}
		}
		
		if($this->shouldGet('updateRequiredEntryFields', $responseProfile))
			$this->updateRequiredEntryFields = implode(',', $object->getUpdateRequiredEntryFields());
		if($this->shouldGet('updateRequiredMetadataXPaths', $responseProfile))
			$this->updateRequiredMetadataXPaths = implode(',', $object->getUpdateRequiredMetadataXPaths());
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		parent::validateForInsert($propertiesToSkip);
		
		$this->validatePropertyNumeric('genericProviderId');
	}
}