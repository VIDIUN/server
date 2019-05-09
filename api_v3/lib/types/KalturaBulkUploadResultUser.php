<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBulkUploadResultUser extends VidiunBulkUploadResult
{
    /**
     * @var string
     */
    public $userId;
    /**
     * @var string
     */
    public $screenName;
    
    /**
     * @var string
     */
    public $email;
    
    /**
     * @var string
     */
    public $description;
    
    /**
     * @var string
     */
    public $tags;
    
    /**
     * @var int
     */
    public $dateOfBirth;
    
    /**
     * @var string
     */
    public $country;
    
    /**
     * @var string
     */    
    public $state;
    
    /**
     * @var string
     */
    public $city;
    
    /**
     * @var string
     */
    public $zip;
    
    /**
     * @var int
     */
    public $gender;
    
    /**
     * @var string
     */
    public $firstName;
    
    /**
     * @var string
     */
    public $lastName;
    
	/**
     * @var string
     */
	public $group;
    
    private static $mapBetweenObjects = array
	(
		"userId" => "puserId",
		"screenName",
	    "email",
	    "dateOfBirth",
	    "country",
		"state",
		"city",
		"zip",
	    "gender",
	    "firstName",
	    "lastName",
	    "tags",
	    "group",
	);
	
    /* (non-PHPdoc)
     * @see VidiunBulkUploadResult::getMapBetweenObjects()
     */
    public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
    /* (non-PHPdoc)
     * @see VidiunBulkUploadResult::toInsertableObject()
     */
    public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
	    if ($this->userId)
	    {
	        $vuser = vuserPeer::getVuserByPartnerAndUid($this->partnerId, $this->userId);
	        if ($vuser)
                $this->objectId = $vuser->getId();	            
	    }
	    
		return parent::toInsertableObject(new BulkUploadResultVuser(), $props_to_skip);
	}

    /* (non-PHPdoc)
     * @see VidiunObject::toObject()
     */
    public function toObject($object_to_fill = null, $props_to_skip = array())
	{
	    if (!is_numeric($this->objectId))
	    {
	        $vuser = vuserPeer::getVuserByPartnerAndUid($this->partnerId, $this->userId);
	        if ($vuser)
                $this->objectId = $vuser->getId();	            
	    }
	    
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}