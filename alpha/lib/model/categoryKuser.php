<?php


/**
 * Skeleton subclass for representing a row from the 'category_vuser' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package Core
 * @subpackage model
 */
class categoryVuser extends BasecategoryVuser implements IIndexable
{
	
	private $old_status = null;

	private $isInInsert = false;
	
	const BULK_UPLOAD_ID = "bulk_upload_id";
	
	const PARTNER_INDEX_PREFIX = 'p';
	
	const UPDATE_METHOD_INDEX_PREFIX = 'um';
	
	const STATUS_INDEX_PREFIX = 'st';
	
	const PERMISSION_NAME_INDEX_PREFIX = "pn";
	
	const PERMISSION_NAME_FIELD_INDEX_PREFIX = "per";
	
	const STATUS_FIELD_PREFIX = "status";

	public function __construct()
	{
		parent::__construct();
		$this->applyDefaultValues();
	}

	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or
	 * equivalent initialization method).
	 * @see        __construct()
	 */
	public function applyDefaultValues()
	{
		$this->setUpdateMethod(UpdateMethodType::MANUAL);
	}

	public function updateVuser($puserId = null, $screenName = null) {
		if ($puserId)
			parent::setPuserId($puserId);
		if ($screenName)
			parent::setScreenName($screenName);
	}

	public function setPuserId($puserId)
	{
		if ( $this->getPuserId() == $puserId )  // same value - don't set for nothing 
			return;

		parent::setPuserId($puserId);
		
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
			
		$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, $puserId);
		if (!$vuser)
		    throw new vCoreException("Invalid user Id [{$puserId}]", vCoreException::INVALID_USER_ID );
			
		parent::setVuserId($vuser->getId());
		parent::setScreenName($vuser->getScreenName());
	}
	
	/**
	 * @param string $permissionName
	 * @return boolean
	 */
	public function hasPermission($permissionName)
	{
		$permissions = explode(',', $this->getPermissionNames());
		return in_array($permissionName, $permissions);
	}
	
	/* (non-PHPdoc)
	 * @see BasecategoryVuser::setVuserId()
	 */
	public function setVuserId($vuserId)
	{
		if ( $this->getVuserId() == $vuserId )  // same value - don't set for nothing 
			return;

		parent::setVuserId($vuserId);

		$vuser = vuserPeer::retrieveByPK($vuserId);
		if (!$vuser)
			throw new vCoreException("Invalid vuser Id [$vuserId]", vCoreException::INVALID_USER_ID);

		parent::setPuserId($vuser->getPuserId());
		parent::setScreenName($vuser->getScreenName());
	}
	
	/* (non-PHPdoc)
	 * @see BasecategoryVuser::setStatus()
	 */
	public function setStatus($v)
	{
		$this->old_status = $this->getStatus();

		parent::setStatus($v);
	}
	
	
	/* (non-PHPdoc)
	 * @see BasecategoryVuser::preUpdate()
	 */
	public function preUpdate(PropelPDO $con = null)
	{
		// no need to update the category if the categoryVuser wasn't updated
		if ($this->isModified())
			$this->updateCategory();
		
		return parent::preUpdate($con);
	}
	
	/* (non-PHPdoc)
	 * @see BaseObject::preDelete()
	 */
	public function preDelete(PropelPDO $con = null)
	{
		$this->updateCategory(true);

		return parent::preDelete();	
	}

	/* (non-PHPdoc)
	 * @see BasecategoryVuser::preInsert()
	 */
	public function preInsert(PropelPDO $con = null)
	{
		$this->isInInsert = true;

		return parent::preInsert($con);
	}
	
	private function updateCategory($isDelete = false)
	{
		categoryPeer::setUseCriteriaFilter(false);
		$category = categoryPeer::retrieveByPK($this->category_id);
		categoryPeer::setUseCriteriaFilter(true);
		
		if(!$category)
			throw new vCoreException('category not found');
			
		if ($this->isInInsert)
		{
			if($this->status == CategoryVuserStatus::PENDING)
				$category->setPendingMembersCount($category->getPendingMembersCount() + 1);
			
			if($this->status == CategoryVuserStatus::ACTIVE)
				$category->setMembersCount($category->getMembersCount() + 1);

		}
		elseif($this->isColumnModified(categoryVuserPeer::STATUS))
		{
			if($this->status == CategoryVuserStatus::PENDING)
				$category->setPendingMembersCount($category->getPendingMembersCount() + 1);
			
			if($this->status == CategoryVuserStatus::ACTIVE )
				$category->setMembersCount($category->getMembersCount() + 1);
			
			if($this->old_status == CategoryVuserStatus::PENDING)
				$category->setPendingMembersCount($category->getPendingMembersCount() - 1);
			
			if($this->old_status == CategoryVuserStatus::ACTIVE)
				$category->setMembersCount($category->getMembersCount() - 1);
				
		}
		
		if($isDelete)
		{				
			if($this->status == CategoryVuserStatus::PENDING)
				$category->setPendingMembersCount($category->getPendingMembersCount() - 1);
				
			if($this->status == CategoryVuserStatus::ACTIVE)
				$category->setMembersCount($category->getMembersCount() - 1);
				
		}

		$category->save();
		$category->indexCategoryInheritedTree();
	}

	
	public function reSetCategoryFullIds()
	{
		$category = categoryPeer::retrieveByPK($this->getCategoryId());
		if(!$category)
			throw new vCoreException('category id [' . $this->getCategoryId() . 'was not found', vCoreException::ID_NOT_FOUND);
			
		$this->setCategoryFullIds($category->getFullIds());
	}
	
	public function reSetScreenName()
	{
		$vuser = vuserPeer::retrieveByPK($this->getVuserId());
		
		if($vuser)
		{
			$this->setScreenName($vuser->getScreenName());
		}
	}
	
	//	set properties in custom data
	
    public function setBulkUploadId ($bulkUploadId){$this->putInCustomData (self::BULK_UPLOAD_ID, $bulkUploadId);}
	public function getBulkUploadId (){return $this->getFromCustomData(self::BULK_UPLOAD_ID);}
	
	/* (non-PHPdoc)
	 * @see IIndexable::getIntId()
	 */
	public function getIntId() {
		return $this->getId();		
	}

	/* (non-PHPdoc)
	 * @see IIndexable::getEntryId()
	 */
	public function getEntryId() {}

	public function getIndexObjectName() {
		return "categoryVuserIndex";
	}
	
	/* (non-PHPdoc)
	 * @see IIndexable::indexToSearchIndex()
	 */
	public function indexToSearchIndex() {
		
		vEventsManager::raiseEventDeferred(new vObjectReadyForIndexEvent($this));
	}
	
	/**
	 * Return permission_names property value for index
	 * @return string
	 */
	public function getSearchIndexPermissionNames ()
	{
		$permissionNames = explode(",", $this->getPermissionNames());
		foreach ($permissionNames as &$permissionName)
			$permissionName = self::getSearchIndexFieldValue(categoryVuserPeer::PERMISSION_NAMES, $permissionName, $this->getPartnerId());
		
		return self::PERMISSION_NAME_FIELD_INDEX_PREFIX.$this->getPartnerId()." ". implode(" ", $permissionNames);
	}
	
	/**
	 * Return status property value for index
	 * @return string
	 */
	public function getSearchIndexStatus ()
	{
		return self::STATUS_FIELD_PREFIX. $this->getPartnerId() ." ". self::getSearchIndexFieldValue(categoryVuserPeer::STATUS, $this->getStatus(), $this->getPartnerId());
	}
	
	/**
	 * Return update_method property value for index
	 * @return string
	 */
	public function getSearchIndexUpdateMethod ()
	{
		return self::getSearchIndexFieldValue(categoryVuserPeer::UPDATE_METHOD, $this->getUpdateMethod(), $this->getPartnerId());
	}
	
	/**
	 * Return category_full_ids property value for index
	 * @return string
	 */
	public function getSearchIndexCategoryFullIds ()
	{
		$fullIds = $this->getCategoryFullIds();
		$fullIdsArr = explode(categoryPeer::CATEGORY_SEPARATOR, $fullIds);
		
		$parsedFullId = '';
		$fullIds = '';
		foreach ($fullIdsArr as $categoryId)
		{
			if($fullIds == '')
			{
				$fullIds = $categoryId;
			}
			else
			{
				$parsedFullId .= md5($fullIds . categoryPeer::CATEGORY_SEPARATOR) . ' ';
				$fullIds .= '>' . $categoryId;
			}
			
			$parsedFullId .= md5($fullIds) . ' ';
		}
		
		$parsedFullId .= md5($fullIds . category::FULL_IDS_EQUAL_MATCH_STRING);
		
		return $parsedFullId ;
	}
	
	public static function getSearchIndexFieldValue ($fieldName, $fieldValue, $partnerId)
	{
		switch ($fieldName)
		{
			case categoryVuserPeer::STATUS:
				return $partnerId . self::STATUS_INDEX_PREFIX . $fieldValue;
				break;
			case categoryVuserPeer::UPDATE_METHOD:
				return $partnerId . self::UPDATE_METHOD_INDEX_PREFIX . $fieldValue;
				break;
			case categoryVuserPeer::PERMISSION_NAMES:
				return $partnerId . self::PERMISSION_NAME_INDEX_PREFIX . $fieldValue;
				break;
			default:
				return $fieldValue;
			
		}
	}
	
	/* (non-PHPdoc)
	 * @see lib/model/om/Baseentry#postInsert()
	 */
	public function postInsert(PropelPDO $con = null)
	{
		parent::postInsert($con);
	
		if (!$this->alreadyInSave)
		{
			vEventsManager::raiseEvent(new vObjectAddedEvent($this));
			
			$category = $this->getcategory();
			if($category && $category->getPrivacyContexts() && !PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENTITLEMENT_USED, $category->getPartnerId()))
				PermissionPeer::enableForPartner(PermissionName::FEATURE_ENTITLEMENT_USED, PermissionType::SPECIAL_FEATURE, $category->getPartnerId());

			$this->updateCategory();
		}

		$this->isInInsert = false;
	}
	
	/* (non-PHPdoc)
	 * @see BasecategoryVuser::postUpdate()
	 */
	public function postUpdate(PropelPDO $con = null)
	{
		parent::postUpdate($con);
		
		if (!$this->alreadyInSave)
			vEventsManager::raiseEvent(new vObjectUpdatedEvent($this));

		if($this->getColumnsOldValue(categoryVuserPeer::STATUS) != CategoryVuserStatus::DELETED  && $this->getStatus() == CategoryVuserStatus::DELETED)
		{
			vEventsManager::raiseEvent(new vObjectDeletedEvent($this));
		}
	}
	
	/**
	 * @param array $permissionNames
	 * @return array
	 */
	public static function removeCategoryPermissions (array $permissionNames)
	{
		$return = array();
		foreach ($permissionNames as $permissionName)
		{
			if ($permissionName != PermissionName::CATEGORY_CONTRIBUTE && $permissionName != PermissionName::CATEGORY_EDIT &&
				$permissionName != PermissionName::CATEGORY_MODERATE && $permissionName != PermissionName::CATEGORY_VIEW)
				{
					$return[] = $permissionName;
				}
		}
		
		return $return;
	}
	
	public static function getPermissionNamesByPermissionLevel($permissionLevel)
	{
		switch ($permissionLevel)
		{
			case CategoryVuserPermissionLevel::MANAGER:
				$permissionNamesArr[] = PermissionName::CATEGORY_EDIT;
				$permissionNamesArr[] = PermissionName::CATEGORY_MODERATE;
				$permissionNamesArr[] = PermissionName::CATEGORY_CONTRIBUTE;
				$permissionNamesArr[] = PermissionName::CATEGORY_VIEW;
				break;
			case CategoryVuserPermissionLevel::MODERATOR:
				$permissionNamesArr[] = PermissionName::CATEGORY_MODERATE;
				$permissionNamesArr[] = PermissionName::CATEGORY_VIEW;
				break;
			case CategoryVuserPermissionLevel::CONTRIBUTOR:
				$permissionNamesArr[] = PermissionName::CATEGORY_CONTRIBUTE;
				$permissionNamesArr[] = PermissionName::CATEGORY_VIEW;
				break;
			case CategoryVuserPermissionLevel::MEMBER:
				$permissionNamesArr[] = PermissionName::CATEGORY_VIEW;
				break;
		}
		
		return $permissionNamesArr;
	}

	public function getCacheInvalidationKeys()
	{
		return array("categoryVuser:id=".strtolower($this->getId()), "categoryVuser:categoryId=".strtolower($this->getCategoryId()));
	}

	/**
	 * @return partner
	 */
	public function getPartner()
	{
		return PartnerPeer::retrieveByPK( $this->getPartnerId() );
	}

} // categoryVuser
