<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBulkUploadResultCategoryUser extends VidiunBulkUploadResult
{
   /**
    * @var int
    */
   public $categoryId;
   
   /**
    * @var string
    */
   public $categoryReferenceId;
   
   /**
    * @var string
    */
   public $userId;
   
   /**
    * @var int
    */
   public $permissionLevel;
   
   /**
    * @var int
    */
   public $updateMethod;
   
   /**
    * @var int
    */
   public $requiredObjectStatus;
    
    private static $mapBetweenObjects = array
	(
	    "categoryId",
	    "categoryReferenceId",
		"userId",
	    "permissionLevel",
	    "updateMethod",
		"requiredObjectStatus" => "requiredStatus",
	);
	
    public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
    /* (non-PHPdoc)
     * @see VidiunBulkUploadResult::toInsertableObject()
     */
    public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		return parent::toInsertableObject(new BulkUploadResultCategoryVuser(), $props_to_skip);
	}
	
    /* (non-PHPdoc)
     * @see VidiunObject::toObject()
     */
    public function toObject($object_to_fill = null, $props_to_skip = array())
	{
	    //No need to add objectId to result with status ERROR
	    if ($this->status != VidiunBulkUploadResultStatus::ERROR)
	    {
		    $vuser = vuserPeer::getVuserByPartnerAndUid($this->partnerId, $this->userId);
		    if (!$vuser)
		    {
		        throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
		    }
		    $categoryVuser = categoryVuserPeer::retrieveByCategoryIdAndVuserId($this->categoryId, $vuser->getId());
		    if ($categoryVuser)
		        $this->objectId = $categoryVuser->getId();
	    }
	        
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}