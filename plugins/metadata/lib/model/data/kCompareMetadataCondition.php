<?php
/**
 * @package plugins.metadata
 * @subpackage model.data
 */
class vCompareMetadataCondition extends vCompareCondition
{
	/**
	 * May contain the full xpath to the field in two formats
	 * 1. Slashed xPath, e.g. /metadata/myElementName
	 * 2. Using local-name function, e.g. /*[local-name()='metadata']/*[local-name()='myElementName']
	 * 3. Using only the field name, e.g. myElementName, it will be searched as //myElementName
	 *
	 * @var string
	 */
	private $xPath;
	
	/**
	 * @var int
	 */
	private $profileId;
	
	/**
	 * @var string
	 */
	private $profileSystemName;
	
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(MetadataPlugin::getConditionTypeCoreValue(MetadataConditionType::METADATA_FIELD_COMPARE));
		parent::__construct($not);
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::applyDynamicValues()
	 */
	protected function applyDynamicValues(vScope $scope)
	{
		parent::applyDynamicValues($scope);
	
		$dynamicValues = $scope->getDynamicValues('{', '}');
		
		if(is_array($dynamicValues) && count($dynamicValues))
		{
			$this->xPath = str_replace(array_keys($dynamicValues), $dynamicValues, $this->xPath);
			if($this->profileSystemName)
				$this->profileSystemName = str_replace(array_keys($dynamicValues), $dynamicValues, $this->profileSystemName);
		}
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::getFieldValue()
	 */
	public function getFieldValue(vScope $scope)
	{
		$profileId = $this->profileId;
		if(!$profileId)
		{
			if(!$this->profileSystemName)
				return null;
				
			$profile = MetadataProfilePeer::retrieveBySystemName($this->profileSystemName, vCurrentContext::getCurrentPartnerId());
			if(!$profile)
				return null;
				
			$profileId = $profile->getId();
		}
		
		$metadata = null;
		if($scope instanceof accessControlScope || $scope instanceof vStorageProfileScope)
		{
			$metadata = MetadataPeer::retrieveByObject($profileId, MetadataObjectType::ENTRY, $scope->getEntryId());
		}
		elseif($scope instanceof vEventScope && $scope->getEvent() instanceof vApplicativeEvent)
		{
			$object = $scope->getEvent()->getObject();
			if(vMetadataManager::isMetadataObject($object))
			{
				$objectType = vMetadataManager::getTypeNameFromObject($object);
				$metadata = MetadataPeer::retrieveByObject($profileId, $objectType, $object->getId());
			}
			else if ($object instanceof Metadata)
			{
				$metadata = $object;
			}	
		}
			
		if(!$metadata)
			return null;
			
		$values = vMetadataManager::parseMetadataValues($metadata, $this->xPath);
		if(is_null($values))
			return null;
			
		return array_map('intval', $values);
	}
	
	/**
	 * @return string $xPath
	 */
	public function getXPath()
	{
		return $this->xPath;
	}

	/**
	 * @return int $profileId
	 */
	public function getProfileId()
	{
		return $this->profileId;
	}

	/**
	 * @param string $xPath
	 */
	public function setXPath($xPath)
	{
		$this->xPath = $xPath;
	}

	/**
	 * @param int $profileId
	 */
	public function setProfileId($profileId)
	{
		$this->profileId = $profileId;
	}

	/**
	 * @return string
	 */
	public function getProfileSystemName() 
	{
		return $this->profileSystemName;
	}

	/**
	 * @param string $profileSystemName
	 */
	public function setProfileSystemName($profileSystemName) 
	{
		$this->profileSystemName = $profileSystemName;
	}

	/* (non-PHPdoc)
	 * @see vCompareCondition::shouldFieldDisableCache()
	 */
	public function shouldFieldDisableCache($scope)
	{
		return false;
	}
}
