<?php
/**
 * @package plugins.metadata
 * @subpackage api.objects
 * @relatedService MetadataService
 */
class VidiunMetadata extends VidiunObject implements IRelatedFilterable 
{
	/**
	 * 
	 * @var int
	 * @readonly
	 */
	public $id;

	
	/**
	 * 
	 * @var int
	 * @filter eq
	 * @readonly
	 */
	public $partnerId;

	
	/**
	 * 
	 * @var int
	 * @filter eq,in
	 * @readonly
	 */
	public $metadataProfileId;


	
	/**
	 * 
	 * @var int
	 * @filter eq,gte,lte,order
	 * @readonly
	 */
	public $metadataProfileVersion;


	
	/**
	 * 
	 * @var VidiunMetadataObjectType
	 * @filter eq
	 * @readonly
	 */
	public $metadataObjectType;


	
	/**
	 * 
	 * @var string
	 * @filter eq,in
	 * @readonly
	 */
	public $objectId;


	
	/**
	 * 
	 * @var int
	 * @filter eq,gte,lte,order
	 * @readonly
	 */
	public $version;


	
	/**
	 * 
	 * @var time
	 * @filter gte,lte,order
	 * @readonly
	 */
	public $createdAt;


	
	/**
	 * 
	 * @var time
	 * @filter gte,lte,order
	 * @readonly
	 */
	public $updatedAt;


	
	/**
	 * 
	 * @var VidiunMetadataStatus
	 * @filter eq,in
	 * @readonly
	 */
	public $status;


	
	/**
	 * 
	 * @var string
	 * @readonly
	 */
	public $xml;
	
	private static $map_between_objects = array
	(
		"id",
		"partnerId",
		"metadataProfileId",
		"metadataProfileVersion",
		"metadataObjectType" => "objectType",
		"objectId",
		"version",
		"createdAt",
		"updatedAt",
		"status",
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
		return array('metadataObjectTypeEqual' => 'When null, default is VidiunMetadataObjectType::ENTRY');
	}
	
	public function toObject($dbMetadata = null, $propsToSkip = array())
	{
		if(is_null($dbMetadata))
			$dbMetadata = new Metadata();
			
		return parent::toObject($dbMetadata, $propsToSkip);
	}
	
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);
		
		if($this->shouldGet('objectId', $responseProfile))
		{
			if($this->metadataObjectType == VidiunMetadataObjectType::USER)
			{
				$user = vuserPeer::retrieveByPK($this->objectId);
				
				$this->objectId = null;
				if($user)
					$this->objectId = $user->getPuserId();
			}
		}

		if($this->shouldGet('xml', $responseProfile))
		{
			$key = $source_object->getSyncKey(Metadata::FILE_SYNC_METADATA_DATA);
			$this->xml = vFileSyncUtils::file_get_contents($key, true, false);
		}
	}
}