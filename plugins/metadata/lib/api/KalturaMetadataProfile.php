<?php
/**
 * @package plugins.metadata
 * @subpackage api.objects
 */
class VidiunMetadataProfile extends VidiunObject implements IFilterable 
{
	/**
	 * @var int
	 * @filter eq
	 * @readonly
	 */
	public $id;
	
	/**
	 * @var int
	 * @filter eq
	 * @readonly
	 */
	public $partnerId;
	
	/**
	 * @var VidiunMetadataObjectType
	 * @filter eq,in
	 */
	public $metadataObjectType;
	
	/**
	 * @var int
	 * @filter eq
	 * @readonly
	 */
	public $version;
	
	/**
	 * @var string
	 * @filter eq
	 */
	public $name;
	
	/**
	 * @var string
	 * @filter eq,in
	 */
	public $systemName;
	
	/**
	 * @var string
	 */
	public $description;
	
	/**
	 * @var time
	 * @filter gte,lte,order
	 * @readonly
	 */
	public $createdAt;
	
	/**
	 * @var time
	 * @filter gte,lte,order
	 * @readonly
	 */
	public $updatedAt;
	
	/**
	 * @var VidiunMetadataProfileStatus
	 * @filter eq,in
	 * @readonly
	 */
	public $status;
	
	/**
	 * @var string
	 * @readonly
	 */
	public $xsd;

	/**
	 * @var string
	 * @readonly
	 */
	public $views;

	/**
	 * @var string
	 * @readonly
	 */
	public $xslt;

	/**
	 * @var VidiunMetadataProfileCreateMode
	 * @filter eq,not,in,notin
	 */
	public $createMode;

	/**
	 * @var bool
	 */
	public $disableReIndexing;
	
	private static $map_between_objects = array
	(
		"id",
		"partnerId",
		"metadataObjectType" => "objectType",
		"version",
		"name",
		"systemName",
		"description",
		"createdAt",
		"updatedAt",
		"status",
		"createMode",
		"disableReIndexing",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbMetadataProfile = null, $propsToSkip = array())
	{
		if(is_null($dbMetadataProfile))
			$dbMetadataProfile = new MetadataProfile();
			
		return parent::toObject($dbMetadataProfile, $propsToSkip);
	}
	
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);

		if($this->shouldGet('xsd', $responseProfile))
		{
			$key = $source_object->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_DEFINITION);
			$this->xsd = vFileSyncUtils::file_get_contents($key, true, false);
		}
		
		if($this->shouldGet('views', $responseProfile))
		{	
			$key = $source_object->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_VIEWS);
			$this->views = vFileSyncUtils::file_get_contents($key, true, false);
		}
		
		if($this->shouldGet('xslt', $responseProfile))
		{
			$key = $source_object->getSyncKey(MetadataProfile::FILE_SYNC_METADATA_XSLT);
			$this->xslt = vFileSyncUtils::file_get_contents($key, true, false);
		}
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyMinLength("name", 1);
		
		if($this->systemName)
		{
			$c = VidiunCriteria::create(MetadataProfilePeer::OM_CLASS);
			$c->add(MetadataProfilePeer::SYSTEM_NAME, $this->systemName);
			if(MetadataProfilePeer::doCount($c))
				throw new VidiunAPIException(VidiunErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
		}
		
		return parent::validateForInsert($propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		if (!is_null($this->name))
			$this->validatePropertyMinLength("name", 1);
				    
	    if ($this->systemName)
	    {
	        $c = VidiunCriteria::create(MetadataProfilePeer::OM_CLASS);
	        $c->add(MetadataProfilePeer::ID, $sourceObject->getId(), Criteria::NOT_EQUAL);
			$c->add(MetadataProfilePeer::SYSTEM_NAME, $this->systemName);
			if(MetadataProfilePeer::doCount($c))
				throw new VidiunAPIException(VidiunErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
	    }
	    
	    return parent::validateForUpdate($sourceObject);
	}
}