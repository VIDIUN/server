<?php
/**
 * @package api
 * @subpackage objects
 * @relatedService CategoryUserService
 */
class VidiunCategoryUser extends VidiunObject implements IRelatedFilterable
{
	/**
	 * 
	 * @var int
	 * @insertonly
	 * @filter eq,in
	 */
	public $categoryId;
	
	/**
	 * User id
	 * 
	 * @var string
	 * @insertonly
	 * @filter eq,in
	 */
	public $userId;
	
	/**
	 * Partner id
	 * 
	 * @var int
	 * @readonly
	 */
	public $partnerId;
	
	/**
	 * Permission level
	 * @deprecated
	 * @var VidiunCategoryUserPermissionLevel
	 * @filter eq,in
	 */
	public $permissionLevel;
	
	/**
	 * Status
	 * 
	 * @var VidiunCategoryUserStatus
	 * @readonly
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * CategoryUser creation date as Unix timestamp (In seconds)
	 * 
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;
	
	/**
	 * CategoryUser update date as Unix timestamp (In seconds)
	 * 
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/**
	 * Update method can be either manual or automatic to distinguish between manual operations (for example in VMC) on automatic - using bulk upload 
	 * 
	 * @var VidiunUpdateMethodType
	 * @filter eq, in
	 */
	public $updateMethod;
	
	/**
	 * The full ids of the Category
	 * 
	 * @var string
	 * @readonly
	 * @filter likex,eq
	 */
	public $categoryFullIds;
	
	/**
	 * Set of category-related permissions for the current category user.
	 * @var string
	 * @filter matchand,matchor,notcontains
	 */
	public $permissionNames;
	
	private static $mapBetweenObjects = array
	(
		"categoryId",
		"userId" => "puserId",
		"partnerId",
		"permissionLevel",
		"status",
		"createdAt",
		"updatedAt",
		"updateMethod",
		"categoryFullIds",
		"permissionNames",
	);
	
	public function toObject($dbObject = null, $skip = array()) {
	    
		if (is_null ( $dbObject ))
			$dbObject = new categoryVuser ();
		/* @var $dbObject categoryVuser */
		if (!$this->permissionNames && !is_null($this->permissionLevel) && $this->permissionLevel !== $dbObject->getPermissionLevel())
		{
			$permissionNames = $dbObject->getPermissionNames();
			if ($permissionNames)
			{
				$permissionNamesArr = explode(',', $permissionNames);
				$permissionNamesArr = categoryVuser::removeCategoryPermissions($permissionNamesArr);
			}
			else 
			{
				$permissionNamesArr = array();
			}
			
			$permissionNamesArr = categoryVuser::getPermissionNamesByPermissionLevel($this->permissionLevel);
			
			$dbObject->setPermissionNames(implode(',', $permissionNamesArr));
		}
		parent::toObject ( $dbObject, $skip );
		
		return $dbObject;
	}
	
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the CategoryVuser object (on the right)  
	 */
	public function getMapBetweenObjects() {
		return array_merge ( parent::getMapBetweenObjects (), self::$mapBetweenObjects );
	}
	
	public function getExtraFilters() {
		return array ();
	}
	
	public function getFilterDocs() {
		return array ();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array()) 
	{
		$category = categoryPeer::retrieveByPK ( $this->categoryId );
		if (! $category)
			throw new VidiunAPIException ( VidiunErrors::CATEGORY_NOT_FOUND, $this->categoryId );
		
		if ($category->getInheritanceType () == InheritanceType::INHERIT)
			throw new VidiunAPIException ( VidiunErrors::CATEGORY_INHERIT_MEMBERS, $this->categoryId );
		
		//validating userId is not 0 or null
		if($this->userId == "0")
		    throw new VidiunAPIException ( VidiunErrors::INVALID_USER_ID);
		$this->validatePropertyMinLength('userId',1);
		
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		
		$vuser = vuserPeer::getVuserByPartnerAndUid ($partnerId , $this->userId );
		if($vuser)
		{
			$categoryVuser = categoryVuserPeer::retrieveByCategoryIdAndVuserId ( $this->categoryId, $vuser->getId () );
			if ($categoryVuser)
				throw new VidiunAPIException ( VidiunErrors::CATEGORY_USER_ALREADY_EXISTS );
		}
		
		$currentVuserCategoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($this->categoryId, vCurrentContext::getCurrentVsVuserId());
		if ((! $currentVuserCategoryVuser || 
				$currentVuserCategoryVuser->getPermissionLevel () != CategoryVuserPermissionLevel::MANAGER) && 
				$category->getUserJoinPolicy () == UserJoinPolicyType::NOT_ALLOWED && 
				vEntitlementUtils::getEntitlementEnforcement ()) {
			throw new VidiunAPIException ( VidiunErrors::CATEGORY_USER_JOIN_NOT_ALLOWED, $this->categoryId );
		}
		
		//if user doesn't exists - create it
		if(!$vuser)
		{
			if(!preg_match(vuser::PUSER_ID_REGEXP, $this->userId))
				throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'userId');
				
			vuserPeer::createVuserForPartner($partnerId, $this->userId);
		}
		
		return parent::validateForInsert ( $propertiesToSkip );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject($dbObject = null, $skip = array())
	{
	    if (is_null($this->permissionLevel))
	    {
    	    $category = categoryPeer::retrieveByPK($this->categoryId);
    	    if(!$category)
    	    	throw new VidiunAPIException ( VidiunErrors::CATEGORY_NOT_FOUND, $this->categoryId );
    	    
	        $this->permissionLevel = $category->getDefaultPermissionLevel();
	    }
	    
	    return parent::toInsertableObject($dbObject, $skip);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUpdate($sourceObject, $propertiesToSkip)
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = null)
	{
		/* @var $sourceObject categoryVuser */
		$category = categoryPeer::retrieveByPK($sourceObject->getCategoryId());
		if (!$category)
			throw new VidiunAPIException(VidiunErrors::CATEGORY_NOT_FOUND, $sourceObject->getCategoryId());
			
		if ($this->permissionNames && $this->permissionNames != $sourceObject->getPermissionNames())
		{
			if($sourceObject->getVuserId() == $category->getVuserId())
			{
				if (strpos($this->permissionNames, PermissionName::CATEGORY_EDIT) === false)
				{
					throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_CATEGORY_USER_OWNER);
				}
			}
		}
		
		$currentVuserCategoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($sourceObject->getCategoryId(), vCurrentContext::getCurrentVsVuserId());
		if(vEntitlementUtils::getEntitlementEnforcement() && 
		(!$currentVuserCategoryVuser || !$currentVuserCategoryVuser->hasPermission(PermissionName::CATEGORY_EDIT)))
			throw new VidiunAPIException(VidiunErrors::CANNOT_UPDATE_CATEGORY_USER, $sourceObject->getCategoryId());
			
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
}
