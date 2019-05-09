<?php


/**
 * Skeleton subclass for performing query and update operations on the 'category_vuser' table.
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
class categoryVuserPeer extends BasecategoryVuserPeer {
	
	/**
	 * 
	 * @param int $categoryId
	 * @param int $vuserId
	 * @param $con
	 * 
	 * @return categoryVuser
	 */
	public static function retrieveByCategoryIdAndVuserId($categoryId, $vuserId, $con = null)
	{
		$criteria = new Criteria();

		$criteria->add(categoryVuserPeer::CATEGORY_ID, $categoryId);
		$criteria->add(categoryVuserPeer::VUSER_ID, $vuserId);

		return categoryVuserPeer::doSelectOne($criteria, $con);
	}

	/**
	 *
	 * @param int $vuserId
	 * @param $con
	 *
	 * @return array Array of categoryVuser
	 */
	public static function retrieveByVuserId($vuserId, $con = null)
	{
		$criteria = new Criteria();
		$criteria->add(categoryVuserPeer::VUSER_ID, $vuserId);

		return categoryVuserPeer::doSelect($criteria, $con);
	}
	
	/**
	 * 
	 * @param int $vuserId
	 * @return bool - no need to fetch the objects
	 */
	public static function isCategroyVuserExistsForVuser($vuserId)
	{
		$criteria = new Criteria();

		$criteria->add(categoryVuserPeer::VUSER_ID, $vuserId);
		
		$categoryVuser = categoryVuserPeer::doSelectOne($criteria);
		
		if($categoryVuser)
			return true;
			
		return false;
	}


	/**
	 *  this function return categoryUser if the user has explicit or implicit (by group) required permissions on the category
	 *
	 * @param int $categoryId
	 * @param int $vuserId
	 * @param array $requiredPermissions
	 * @param bool $supportGroups
	 * @param null $con
	 * @return categoryVuser|null
	 */
	public static function retrievePermittedVuserInCategory($categoryId, $vuserId = null, $requiredPermissions = null, $supportGroups = true, $con = null){
		$category = categoryPeer::retrieveByPK($categoryId);
		if(!$category)
			return null;

		if($category->getInheritedParentId())
			$categoryId = $category->getInheritedParentId();

		if(is_null($vuserId))
			$vuserId = vCurrentContext::getCurrentVsVuserId();

		if(is_null($requiredPermissions))
			$requiredPermissions = array(PermissionName::CATEGORY_VIEW);

		$categoryVuser = self::retrieveByCategoryIdAndActiveVuserId($categoryId, $vuserId, $requiredPermissions, $con);
		if (!is_null($categoryVuser)){
			return $categoryVuser;
		}
		
		$permittedCategoryVuser = null;
		//check if vuserId has permission in category by a junction group
		if($supportGroups)
		{
			$vgroupIds = VuserVgroupPeer::retrieveVgroupIdsByVuserId($vuserId);
			if (count($vgroupIds) == 0)
				return null;

			$criteria = new Criteria();
			$criteria->add(categoryVuserPeer::CATEGORY_ID, $categoryId);
			$criteria->add(categoryVuserPeer::VUSER_ID, $vgroupIds, Criteria::IN);
			$criteria->add(categoryVuserPeer::STATUS, CategoryVuserStatus::ACTIVE);
			$categoryVusers = categoryVuserPeer::doSelect($criteria, $con);
			if(!$categoryVusers)
				return null;
			
			foreach( $categoryVusers as $categoryVuser)
			{
				/* @var $categoryVuser categoryVuser */
				foreach($requiredPermissions as $requiredPermission)
				{
					if($categoryVuser->hasPermission($requiredPermission))
					{
						//In case of multiple category users return the one with the highest permission level
						if(!$permittedCategoryVuser || $categoryVuser->getPermissionLevel() < $permittedCategoryVuser->getPermissionLevel())
							$permittedCategoryVuser = $categoryVuser;
					}
				}
			}
		}
		return $permittedCategoryVuser;
	}
	
	/**
	 * 
	 * @param int $categoryId
	 * @param int $vuserId
	 * @param array $requiredPermissions
	 * @param $con
	 * 
	 * @return categoryVuser
	 */
	public static function retrieveByCategoryIdAndActiveVuserId($categoryId, $vuserId, $requiredPermissions, $con = null)
	{
		$criteria = new Criteria();
		$criteria->add(categoryVuserPeer::CATEGORY_ID, $categoryId);
		$criteria->add(categoryVuserPeer::VUSER_ID, $vuserId);
		$criteria->add(categoryVuserPeer::STATUS, CategoryVuserStatus::ACTIVE);

		$categoryVuser = categoryVuserPeer::doSelectOne($criteria, $con);
		if(!$categoryVuser)
			return null;
			
		foreach($requiredPermissions as $requiredPermission)
			if(!$categoryVuser->hasPermission($requiredPermission))
				return null;
				
		return $categoryVuser;
	}
	
	/**
	 * 
	 * @param array $categoriesIds
	 * @param int $vuserId
	 * @param array $requiredPermissions
	 * @param $con
	 * 
	 * @return categoryVuser
	 */
	public static function areCategoriesAllowed(array $categoriesIds, $vuserId = null, $requiredPermissions = null, $con = null)
	{
		if(is_null($vuserId))
			$vuserId = vCurrentContext::getCurrentVsVuserId();
			
		if(is_null($requiredPermissions))
			$requiredPermissions = array(PermissionName::CATEGORY_VIEW);
			
		$categories = categoryPeer::retrieveByPKs($categoriesIds);
		if(count($categories) < count($categoriesIds))
			return false;
		
		$categoriesIds = array();
		foreach($categories as $category)
		{
			/* @var $category category */
			$categoriesIds[] = $category->getInheritedParentId() ? $category->getInheritedParentId() : $category->getId();
		}
		$categoriesIds = array_unique($categoriesIds);
		
		$criteria = new Criteria();
		$criteria->add(categoryVuserPeer::CATEGORY_ID, $categoriesIds, Criteria::IN);
		$criteria->add(categoryVuserPeer::VUSER_ID, $vuserId);
		$criteria->add(categoryVuserPeer::STATUS, CategoryVuserStatus::ACTIVE);

		$categoryVusers = categoryVuserPeer::doSelectOne($criteria, $con);
		if(count($categoryVusers) < count($categoriesIds))
			return false;
			
		foreach($categoryVusers as $categoryVuser)
		{
			$permissions = explode(',', $categoryVuser->getPermissionNames());
			foreach($requiredPermissions as $requiredPermission)
				if(!in_array($requiredPermission, $permissions))
					return false;
		}
		return true;
	}
	
	/**
	 * 
	 * @param int $categoryId
	 * @param int $vuserId
	 * @param $con
	 * 
	 * @return array
	 */
	public static function retrieveActiveVusersByCategoryId($categoryId, $con = null)
	{
		$criteria = new Criteria();

		$criteria->add(categoryVuserPeer::CATEGORY_ID, $categoryId);
		$criteria->add(categoryVuserPeer::STATUS, CategoryVuserStatus::ACTIVE);

		self::setUseCriteriaFilter(false);
		$categoryVusers = categoryVuserPeer::doSelect($criteria, $con);
		self::setUseCriteriaFilter(true);
		return $categoryVusers;
	}
	
	
	public static function setDefaultCriteriaFilter ()
	{
		if ( self::$s_criteria_filter == null )
		{
			self::$s_criteria_filter = new criteriaFilter ();
		}
		
		$c =  VidiunCriteria::create(categoryVuserPeer::OM_CLASS); 
		$c->addAnd ( categoryVuserPeer::STATUS, array(CategoryVuserStatus::DELETED), Criteria::NOT_IN);
		$partnerId = vCurrentContext::getCurrentPartnerId();
		if($partnerId)
			$c->add(categoryVuserPeer::PARTNER_ID,$partnerId);

		self::$s_criteria_filter->setFilter($c);
	}
	
	public static function getCacheInvalidationKeys()
	{
		return array(array("categoryVuser:id=%s", self::ID), array("categoryVuser:categoryId=%s", self::CATEGORY_ID));		
	}
} // categoryVuserPeer
