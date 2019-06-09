<?php
/**
 * @package plugins.dropFolder
 * @subpackage api.objects
 */
class VidiunDropFolder extends VidiunObject implements IFilterable
{	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in,order
	 */
	public $id;
	
	/**
	 * @var int
	 * @insertonly
	 * @filter eq,in
	 */
	public $partnerId;
	
	/**
	 * @var string
	 * @filter like,order
	 */
	public $name;
	
	/**
	 * @var string
	 */
	public $description;
	
	/**
	 * @var VidiunDropFolderType
	 * @filter eq,in
	 */
	public $type;
	
	/**
	 * @var VidiunDropFolderStatus
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * @var int
	 * @filter eq,in
	 */
	public $conversionProfileId;
	
	/**
	 * @var int
	 * @filter eq,in
	 */
	public $dc;
	
	/**
	 * @var string
	 * @filter eq, like
	 */
	public $path;
	
	/**
	 * The ammount of time, in seconds, that should pass so that a file with no change in size we'll be treated as "finished uploading to folder"
	 * @var int
	 */
	public $fileSizeCheckInterval;
	
	/**
	 * @var VidiunDropFolderFileDeletePolicy
	 */
	public $fileDeletePolicy;
	
	/**
	 * @var int
	 */
	public $autoFileDeleteDays;
	
	
	/**
	 * @var VidiunDropFolderFileHandlerType
	 * @filter eq,in
	 */
	public $fileHandlerType;
	
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $fileNamePatterns;
	
	/**
	 * @var VidiunDropFolderFileHandlerConfig
	 */
	public $fileHandlerConfig;

	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $tags;
	
	/**
	 * @var VidiunDropFolderErrorCode
	 * @filter eq,in
	 */
	public $errorCode;
	
	/**
	 * @var string
	 */
	public $errorDescription;
	
	/**
	 * @var string
	 */
	public $ignoreFileNamePatterns;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;

	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;

	/**
	 * @var int
	 */
	public $lastAccessedAt;
	
	/**
	 * @var bool
	 */
	public $incremental;
	
	/**
	 * @var int
	 */
	public $lastFileTimestamp;
	
	/**
	 * @var int
	 */
	public $metadataProfileId;
	
	/**
	 * @var string
	 */
	public $categoriesMetadataFieldName;
	
	/**
	* @var bool
	*/
	public $enforceEntitlement;
	
	/**
	* @var bool
	*/
	public $shouldValidateVS;
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array(
		'id',
		'partnerId',
		'name',
		'description',
		'type',
		'status',
		'conversionProfileId',
		'dc',
		'path',
		'fileSizeCheckInterval',
		'fileDeletePolicy',
		'autoFileDeleteDays',
		'fileHandlerType',
		'fileNamePatterns',
		'createdAt',
		'updatedAt',
		'tags',
		'errorCode',
		'errorDescription',
		'ignoreFileNamePatterns',
		'lastAccessedAt',
		'incremental',
		'lastFileTimestamp',
		'metadataProfileId',
		'categoriesMetadataFieldName',
		'enforceEntitlement',
		'shouldValidateVS',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new DropFolder();
		$this->trimStringProperties(array ('path'));	
		parent::toObject($dbObject, $skip);
		if ($this->fileHandlerConfig)
		{
			$dbFileHandlerConfig = $this->fileHandlerConfig->toObject();
			$dbObject->setFileHandlerConfig($dbFileHandlerConfig);
		}
		
		return $dbObject;
	}
	
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);
		
		if($this->shouldGet('fileHandlerConfig', $responseProfile))
		{
			$dbFileHandlerConfig = $source_object->getFileHandlerConfig();
			if ($dbFileHandlerConfig)
			{
				$apiFileHandlerConfig = VidiunPluginManager::loadObject('VidiunDropFolderFileHandlerConfig', $source_object->getFileHandlerType());
				if($apiFileHandlerConfig)
				{
					$apiFileHandlerConfig->fromObject($dbFileHandlerConfig);
					$this->fileHandlerConfig  = $apiFileHandlerConfig;
				}
				else
				{
					VidiunLog::err("Cannot load API object for core file handler config type [" . $dbFileHandlerConfig->getHandlerType() . "]");
				}
			}
		}
	}

	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
	
	public function toUpdatableObject ( $object_to_fill , $props_to_skip = array() )
	{
		/* @var $object_to_fill DropFolder */
		$this->validateForUpdate($object_to_fill, $props_to_skip); // will check that not updatable properties are not set 
		
		$dbUpdatedHandlerConfig = null;
		if (!is_null($this->fileHandlerConfig)) {
			$dbOldHanlderConfig = $object_to_fill->getFileHandlerConfig();
			$dbUpdatedHandlerConfig = $this->fileHandlerConfig->toUpdatableObject($dbOldHanlderConfig);
		}
		
		$result = $this->toObject($object_to_fill, $props_to_skip);
		if ($dbUpdatedHandlerConfig) {
			$result->setFileHandlerConfig($dbUpdatedHandlerConfig);
		}
		
		return $result;
	}
	
	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		$this->validateForInsert($props_to_skip); // will check that not insertable properties are not set
		$this->fileHandlerConfig->validateForInsert();
		
		$result = $this->toObject($object_to_fill, $props_to_skip);
		if (!is_null($this->fileHandlerConfig))
		{
			$dbInsertedHandlerConfig = $this->fileHandlerConfig->toInsertableObject();
			$result->setFileHandlerConfig($dbInsertedHandlerConfig);
		}
		return $result;
	}
	
	/**
	 * @param int $type
	 * @return VidiunDropFolder
	 */
	static function getInstanceByType ($type)
	{
		switch ($type) 
		{
			case VidiunDropFolderType::LOCAL:
			    $obj = new VidiunDropFolder();
				break;
		    
		    case VidiunDropFolderType::FTP:
				$obj = new VidiunFtpDropFolder();
				break;
				
			case VidiunDropFolderType::SFTP:
			    $obj = new VidiunSftpDropFolder();
				break;
			    
			case VidiunDropFolderType::SCP:
			    $obj = new VidiunScpDropFolder();
				break;
			    
			default:
				$obj = VidiunPluginManager::loadObject('VidiunDropFolder', $type);
				break;
		}
		
		return $obj;
	}
	
}