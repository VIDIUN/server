<?php
/**
 * @package plugins.WebexDropFolder
 * @subpackage api.objects
 */
class VidiunWebexDropFolder extends VidiunDropFolder
{
	/**
	 * @var string
	 * @requiresPermission read
	 */
	public $webexUserId;
	
	/**
	 * @var string
	 * @requiresPermission read
	 */
	public $webexPassword;
	
	/**
	 * @var int
	 * @requiresPermission read
	 */
	public $webexSiteId;
	
	/**
	 * @var string
	 * @requiresPermission read
	 */	
	public $webexPartnerId;
	
	/**
	 * @var string
	 * @requiresPermission read
	 */
	public $webexServiceUrl;
	
	/**
	 * @var string
	 */
	public $webexHostIdMetadataFieldName;
	
	/**
	 * @var bool
	 */
	public $deleteFromRecycleBin;
	
	/**
	 * @var string
	 */
	public $webexServiceType;

	/**
	 * @var string
	 */
	public $webexSiteName;

	/**
	 * @var time
	 */
	public $deleteFromTimestamp;
	
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array(
		'webexUserId',
		'webexPassword',
		'webexSiteId',
		'webexPartnerId',
		'webexServiceUrl',
		'webexHostIdMetadataFieldName',
		'deleteFromRecycleBin',
		'webexServiceType',
		'deleteFromTimestamp',
		'webexSiteName',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (!$dbObject)
			$dbObject = new WebexDropFolder();
		$this->validate();
		$dbObject->setType(WebexDropFolderPlugin::getDropFolderTypeCoreValue(WebexDropFolderType::WEBEX));
		return parent::toObject($dbObject, $skip);
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		if (!WebexDropFolderPlugin::isAllowedPartner(vCurrentContext::getCurrentPartnerId()) || !WebexDropFolderPlugin::isAllowedPartner($this->partnerId))
		{
			throw new VidiunAPIException (VidiunErrors::PERMISSION_NOT_FOUND, 'Permission not found to use the WebexDropFolder feature.');
		}
	}
	
	public function validateForUpdate ($sourceObject, $propertiesToSkip = array())
	{
		if (!WebexDropFolderPlugin::isAllowedPartner(vCurrentContext::getCurrentPartnerId()) || !WebexDropFolderPlugin::isAllowedPartner($sourceObject->getPartnerId()))
		{
			throw new VidiunAPIException (VidiunErrors::PERMISSION_NOT_FOUND, 'Permission not found to use the WebexDropFolder feature.');
		}
	}
	
	protected function validate ()
	{
		
		if (isset($this->fileHandlerType) && $this->fileHandlerType != DropFolderFileHandlerType::CONTENT) 
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_ENUM_VALUE, $this->fileHandlerType, 'fileHandlerType', DropFolderFileHandlerType::CONTENT);		
		}
		
		if (isset ($this->fileHandlerConfig) && !($this->fileHandlerConfig instanceof VidiunDropFolderContentFileHandlerConfig))
		{
			throw new VidiunAPIException (VidiunErrors::INVALID_OBJECT_TYPE, get_class($this->fileHandlerConfig));
		}
		
		if (isset ($this->fileHandlerConfig->contentMatchPolicy) )
		{
			if ($this->fileHandlerConfig->contentMatchPolicy != DropFolderContentFileHandlerMatchPolicy::ADD_AS_NEW)
			{
				throw new VidiunAPIException(VidiunErrors::INVALID_ENUM_VALUE, $this->fileHandlerConfig->contentMatchPolicy, 'contentMatchPolicy', DropFolderContentFileHandlerMatchPolicy::ADD_AS_NEW);
			}
		}
	}
}
