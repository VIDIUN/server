<?php
/**
 * @package plugins.metadata
 * @subpackage model.data
 */
class vMatchMetadataCondition extends vMatchCondition
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
		$this->setType(MetadataPlugin::getConditionTypeCoreValue(MetadataConditionType::METADATA_FIELD_MATCH));
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
	 * @see vMatchCondition::getFieldValue()
	 */
	public function getFieldValue(vScope $scope)
	{
		$profileId = $this->profileId;
		if(!$profileId)
		{
			if(!$this->profileSystemName)
			{
				VidiunLog::err("No metadata profile id and system-name supplied");
				return null;
			}
				
			$profile = MetadataProfilePeer::retrieveBySystemName($this->profileSystemName, array(vCurrentContext::getCurrentPartnerId(), PartnerPeer::GLOBAL_PARTNER));
			if(!$profile)
			{
				VidiunLog::notice("Metadata profile with system-name [$this->profileSystemName] not found");
				return null;
			}
				
			$profileId = $profile->getId();
		}
		
		$metadata = null;
		if($scope instanceof accessControlScope || $scope instanceof vStorageProfileScope)
		{
			$metadata = MetadataPeer::retrieveByObject($profileId, MetadataObjectType::ENTRY, $scope->getEntryId());
		}
		elseif($scope instanceof vEventScope)
		{
			$object = $scope->getEvent()->getObject();
			if(vMetadataManager::isMetadataObject($object))
			{
				$objectType = vMetadataManager::getTypeNameFromObject($object);
				$metadata = MetadataPeer::retrieveByObject($profileId, $objectType, $object->getId());
			}
			else if ($object instanceof Metadata && $profileId == $object->getMetadataProfileId())
			{
				$metadata = $object;
			}
			elseif ($scope->getEvent()->getObject() instanceof categoryEntry)
			{
				$profileObject = vMetadataManager::getObjectTypeName($profile->getObjectType());
				$getter = "get{$profileObject}Id";
				VidiunLog::info ("Using $getter in order to retrieve the metadata object ID");
				$categoryEntry = $scope->getEvent()->getObject();
				$objectId = $categoryEntry->$getter();
				$metadata = MetadataPeer::retrieveByObject($profileId, $profile->getObjectType(), $objectId);
			}
			elseif ($object instanceof asset)
			{
				$metadata = MetadataPeer::retrieveByObject($profileId, MetadataObjectType::ENTRY, $object->getEntryId());
			}
		}
			
		if($metadata)
			return vMetadataManager::parseMetadataValues($metadata, $this->xPath);
			
		VidiunLog::notice("Metadata object not found for scope [" . get_class($scope) . "]");
		return null;
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
	 * @see vMatchCondition::shouldFieldDisableCache()
	 */
	public function shouldFieldDisableCache($scope)
	{
		return false;
	}	
}
