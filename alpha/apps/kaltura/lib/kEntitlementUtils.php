<?php
/**
 * vEntitlementUtils is all utils needed for entitlement use cases.
 * @package Core
 * @subpackage utils
 *
 */
class vEntitlementUtils
{
	const DEFAULT_CONTEXT = 'DEFAULTPC';
	const NOT_DEFAULT_CONTEXT = 'NOTDEFAULTPC';
	const TYPE_SEPERATOR = "TYPE";
	const ENTRY_PRIVACY_CONTEXT = 'ENTRYPC';
	const PARTNER_ID_PREFIX = 'pid';

	protected static $initialized = false;
	protected static $entitlementEnforcement = false;
	protected static $entitlementForced = null;
	protected static $privacyContextSearch = null;
	protected static $categoryModeration = false;

	public static function getDefaultContextString( $partnerId )
	{
		return self::getPartnerPrefix($partnerId) . self::DEFAULT_CONTEXT;
	}

	public static function getPartnerPrefix($partnerId)
	{
		return vEntitlementUtils::PARTNER_ID_PREFIX . $partnerId;
	}

	public static function addPrivacyContextsPrefix($privacyContextsArray, $partnerId )
	{
		if ( is_null($privacyContextsArray) || is_null($partnerId))
		{
			VidiunLog::err("can't handle privacy context for privacyContextsArray: $privacyContextsArray and partnerId: $partnerId.");
			return $privacyContextsArray;
		}
		$prefix = self::getPartnerPrefix($partnerId);

		foreach ($privacyContextsArray as &$value)
		{
			$value = $prefix . $value;
		}

		return $privacyContextsArray;

	}

	public static function getEntitlementEnforcement()
	{
		return self::$entitlementEnforcement;
	}

	public static function getCategoryModeration ()
	{
		return self::$categoryModeration;
	}

	public static function getInitialized()
	{
		return self::$initialized;
	}

	public static function isVsPrivacyContextSet()
	{
		$vs = vs::fromSecureString(vCurrentContext::$vs);

		if(!$vs || !$vs->getPrivacyContext())
			return false;

		return true;
	}

	/**
	 * Returns true if vuser or current vuser is entitled to entryId
	 * @param entry $entry
	 * @param int $vuser
	 * @return bool
	 */
	public static function isEntryEntitled(entry $entry, $vuserId = null)
	{
		if($entry->getPartnerId() == PartnerPeer::GLOBAL_PARTNER)
		{
			return true;
		}

		if($entry->getSecurityParentId())
		{
			$entry = $entry->getParentEntry();
			if(!$entry)
			{
				VidiunLog::log('Parent entry not found, cannot validate entitlement');
				return false;
			}
		}

		$vs = vs::fromSecureString(vCurrentContext::$vs);

		if(self::$entitlementForced === false)
		{
			VidiunLog::log('Entitlement forced to be disabled');
			return true;
		}

		// entry is entitled when entitlement is disable
		// for actions with no vs - need to check if partner have default entitlement feature enable.
		if(!self::getEntitlementEnforcement() && $vs)
		{
			VidiunLog::log('Entry entitled: entitlement disabled');
			return true;
		}

		$partner = $entry->getPartner();
		if(!$vs && !$partner->getDefaultEntitlementEnforcement())
		{
			VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] entitled: no vs and default is with no enforcement');
			return true;
		}

		if($vs && in_array($entry->getId(), $vs->getDisableEntitlementForEntry()))
		{
			VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] entitled: vs disble entitlement for this entry');
			return true;
		}

		$vuserId = self::getVuserIdForEntitlement($vuserId, $vs);

		if($vs && $vuserId)
		{
			// vuser is set on the entry as creator or uploader
			if ($vuserId != '' && ($entry->getVuserId() == $vuserId))
			{
				VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] entitled: vs user is the same as entry->vuserId or entry->creatorVuserId [' . $vuserId . ']');
				return true;
			}

			// vuser is set on the entry entitled users edit or publish or view
			if($entry->isEntitledVuserEdit($vuserId) || $entry->isEntitledVuserPublish($vuserId) || $entry->isEntitledVuserView($vuserId))
			{
				VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] entitled: vs user is the same as entry->entitledVusersEdit or entry->entitledVusersPublish or entry->entitledVusersView');
				return true;
			}
		}

		if(!$vs)
		{
			// entry that doesn't belong to any category is public
			//when vs is not provided - the entry is still public (for example - download action)
			$categoryEntry = categoryEntryPeer::retrieveOneActiveByEntryId($entry->getId());
			if(!$categoryEntry)
			{
				VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] entitled: entry does not belong to any category');
				return true;
			}
		}

		$vsPrivacyContexts = null;
		if($vs)
			$vsPrivacyContexts = $vs->getPrivacyContext();

		$allCategoriesEntry = array();

		if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_DISABLE_CATEGORY_LIMIT, $partner->getId()))
		{
			if(!$vsPrivacyContexts || trim($vsPrivacyContexts) == '')
			{
				$categoryEntry = categoryEntryPeer::retrieveOneByEntryIdStatusPrivacyContextExistance($entry->getId(), array(CategoryEntryStatus::PENDING, CategoryEntryStatus::ACTIVE));
				if($categoryEntry)
				{
					VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] entitled: entry belongs to public category and privacy context on the vs is not set');
					return true;
				}
			}
			else
				$allCategoriesEntry = categoryEntryPeer::retrieveActiveAndPendingByEntryIdAndPrivacyContext($entry->getId(), $vsPrivacyContexts);
		}
		else
		{
			$allCategoriesEntry = categoryEntryPeer::retrieveActiveAndPendingByEntryId($entry->getId());
			if($vs && (!$vsPrivacyContexts || trim($vsPrivacyContexts) == '') && !count($allCategoriesEntry))
			{
				// entry that doesn't belong to any category is public
				VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] entitled: entry does not belong to any category and privacy context on the vs is not set');
				return true;
			}
		}

		return self::isMemberOfCategory($allCategoriesEntry, $entry, $partner, $vuserId, $vs, $vsPrivacyContexts);
	}

	public static function getVuserIdForEntitlement($vuserId = null, $vs = null)
	{
		if($vs && !$vuserId)
		{
			$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
			$vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, vCurrentContext::$vs_uid, true);
			if($vuser)
				$vuserId = $vuser->getId();
		}

		return $vuserId;
	}

	private static function isMemberOfCategory($allCategoriesEntry, entry $entry, Partner $partner, $vuserId = null, $vs = null, $vsPrivacyContexts = null)
	{
		$categories = array();
		foreach($allCategoriesEntry as $categoryEntry)
			$categories[] = $categoryEntry->getCategoryId();

		//if entry doesn't belong to any category.
		$categories[] = category::CATEGORY_ID_THAT_DOES_NOT_EXIST;

		$c = VidiunCriteria::create(categoryPeer::OM_CLASS);
		$c->add(categoryPeer::ID, $categories, Criteria::IN);

		$privacy = array(category::formatPrivacy(PrivacyType::ALL, $partner->getId()));
		if($vs && !$vs->isAnonymousSession())
			$privacy[] = category::formatPrivacy(PrivacyType::AUTHENTICATED_USERS, $partner->getId());

		$crit = $c->getNewCriterion (categoryPeer::PRIVACY, $privacy, Criteria::IN);

		if($vs)
		{
			if (!$vsPrivacyContexts || trim($vsPrivacyContexts) == '')
				$vsPrivacyContexts = self::getDefaultContextString( $partner->getId());
			else
			{
				$vsPrivacyContexts = explode(',', $vsPrivacyContexts);
				$vsPrivacyContexts = self::addPrivacyContextsPrefix( $vsPrivacyContexts, $partner->getId() );
			}

			$c->add(categoryPeer::PRIVACY_CONTEXTS, $vsPrivacyContexts, VidiunCriteria::IN_LIKE);

			// vuser is set on the category as member
			// this ugly code is temporery - since we have a bug in sphinxCriteria::getAllCriterionFields
			if($vuserId)
			{
				// get the groups that the user belongs to in case she is not associated to the category directly
				$vgroupIds = VuserVgroupPeer::retrieveVgroupIdsByVuserId($vuserId);
				$vgroupIds[] = $vuserId;
				$membersCrit = $c->getNewCriterion ( categoryPeer::MEMBERS , $vgroupIds, VidiunCriteria::IN_LIKE);
				$membersCrit->addOr($crit);
				$crit = $membersCrit;
			}
		}
		else
		{
			//no vs = set privacy context to default.
			$c->add(categoryPeer::PRIVACY_CONTEXTS, array( self::getDefaultContextString( $partner->getId() )) , VidiunCriteria::IN_LIKE);
		}

		$c->addAnd($crit);

		//remove default FORCED criteria since categories that has display in search = public - doesn't mean that all of their entries are public
		VidiunCriterion::disableTag(VidiunCriterion::TAG_ENTITLEMENT_CATEGORY);
		$category = categoryPeer::doSelectOne($c);
		VidiunCriterion::restoreTag(VidiunCriterion::TAG_ENTITLEMENT_CATEGORY);

		if($category)
		{
			VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] entitled: vs user is a member of this category or category privacy is set to public of authenticated');
			return true;
		}

		VidiunLog::info('Entry [' . print_r($entry->getId(), true) . '] not entitled');
		return false;
	}

	/**
	 * Set Entitlement Enforcement - if entitelement is enabled \ disabled in this session
	 * @param int $categoryId
	 * @param int $vuser
	 * @return bool
	 */
	public static function initEntitlementEnforcement($partnerId = null, $enableEntit = null)
	{
		self::$initialized = true;
		self::$entitlementForced = $enableEntit;

		if(is_null($partnerId))
			$partnerId = vCurrentContext::getCurrentPartnerId();

		if(is_null($partnerId) || $partnerId == Partner::BATCH_PARTNER_ID)
			return;

		$partner = PartnerPeer::retrieveByPK($partnerId);
		if (!$partner)
			return;

		$vs = null;
		$vsString = vCurrentContext::$vs ? vCurrentContext::$vs : '';
		if ($vsString != '') // for actions with no VS or when creating vs.
		{
			$vs = vs::fromSecureString($vsString);
		}

		self::initCategoryModeration($vs);

		if(!PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENTITLEMENT, $partnerId))
			return;

		$partnerDefaultEntitlementEnforcement = $partner->getDefaultEntitlementEnforcement();

		// default entitlement scope is true - enable.
		if(is_null($partnerDefaultEntitlementEnforcement))
			$partnerDefaultEntitlementEnforcement = true;

		self::$entitlementEnforcement = $partnerDefaultEntitlementEnforcement;

		if ($vs) // for actions with no VS or when creating vs.
		{
			$enableEntitlement = $vs->getDisableEntitlement();
			if ($enableEntitlement)
				self::$entitlementEnforcement = false;

			$enableEntitlement = $vs->getEnableEntitlement();
			if ($enableEntitlement)
				self::$entitlementEnforcement = true;

		}

		if(!is_null($enableEntit))
		{
			if($enableEntit)
				self::$entitlementEnforcement = true;
			else
				self::$entitlementEnforcement = false;
		}

		if (self::$entitlementEnforcement)
		{
			VidiunCriterion::enableTag(VidiunCriterion::TAG_ENTITLEMENT_ENTRY);
			VidiunCriterion::enableTag(VidiunCriterion::TAG_ENTITLEMENT_CATEGORY);
		}
	}

	public static function getPrivacyForVs($partnerId)
	{
		$vs = vs::fromSecureString(vCurrentContext::$vs);
		if(!$vs || $vs->isAnonymousSession())
			return array(category::formatPrivacy(PrivacyType::ALL, $partnerId));

		return array(category::formatPrivacy(PrivacyType::ALL, $partnerId),
			category::formatPrivacy(PrivacyType::AUTHENTICATED_USERS, $partnerId));
	}

	public static function getPrivacyContextSearch()
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;

		if (self::$privacyContextSearch)
			return self::$privacyContextSearch;

		$privacyContextSearch = array();

		$vs = vs::fromSecureString(vCurrentContext::$vs);
		if(!$vs)
			return array( self::getDefaultContextString( $partnerId ) . self::TYPE_SEPERATOR . PrivacyType::ALL);

		$vsPrivacyContexts = $vs->getPrivacyContext();

		if(is_null($vsPrivacyContexts))
		{   // setting $vsPrivacyContexts only with DEFAULT_CONTEXT string (to resolve conflicts)
			// since prefix will be add in the addPrivacyContextsPrefix bellow
			$vsPrivacyContexts = self::DEFAULT_CONTEXT;
		}

		$vsPrivacyContexts = explode(',', $vsPrivacyContexts);

		foreach ($vsPrivacyContexts as $vsPrivacyContext)
		{
			$privacyContextSearch[] = $vsPrivacyContext . self::TYPE_SEPERATOR . PrivacyType::ALL;

			if (!$vs->isAnonymousSession())
				$privacyContextSearch[] = $vsPrivacyContext . self::TYPE_SEPERATOR  . PrivacyType::AUTHENTICATED_USERS;
		}

		self::$privacyContextSearch = self::addPrivacyContextsPrefix( $privacyContextSearch, $partnerId );

		return self::$privacyContextSearch;
	}

	public static function setPrivacyContextSearch($privacyContextSearch)
	{
		self::$privacyContextSearch = array($privacyContextSearch . self::TYPE_SEPERATOR . PrivacyType::ALL);
	}

	public static function getPrivacyContextForEntry(entry $entry)
	{
		$privacyContexts = array();

		if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_DISABLE_CATEGORY_LIMIT, $entry->getPartnerId()))
			$privacyContexts = self::getPrivacyContextsByCategoryEntries($entry);
		else
			$privacyContexts = self::getPrivacyContextsByAllCategoryIds($entry);

		//Entry That doesn't assinged to any category is public.
		if (!count($privacyContexts))
			$privacyContexts[self::DEFAULT_CONTEXT] = PrivacyType::ALL ;

		$entryPrivacyContexts = array();
		foreach ($privacyContexts as $categoryPrivacyContext => $Privacy)
			$entryPrivacyContexts[] = $categoryPrivacyContext . self::TYPE_SEPERATOR . $Privacy;

		VidiunLog::info('Privacy by context: ' . print_r($entryPrivacyContexts,true));

		return $entryPrivacyContexts;
	}

	private static function getCategoriesByIds($categoriesIds)
	{
		$c = VidiunCriteria::create(categoryPeer::OM_CLASS);
		VidiunCriterion::disableTag(VidiunCriterion::TAG_ENTITLEMENT_CATEGORY);
		$c->add(categoryPeer::ID, $categoriesIds, Criteria::IN);
		VidiunCriterion::restoreTag(VidiunCriterion::TAG_ENTITLEMENT_CATEGORY);
		$c->dontCount();

		VidiunCriterion::disableTag(VidiunCriterion::TAG_ENTITLEMENT_CATEGORY);
		$categories = categoryPeer::doSelect($c);
		VidiunCriterion::restoreTag(VidiunCriterion::TAG_ENTITLEMENT_CATEGORY);

		return $categories;
	}

	private static function getPrivacyContextsByAllCategoryIds(entry $entry)
	{
		$privacyContexts = array();

		$allCategoriesIds = $entry->getAllCategoriesIds(true);
		if (count($allCategoriesIds))
		{
			$categories = self::getCategoriesByIds($allCategoriesIds);
			foreach ($categories as $category)
			{
				$categoryPrivacy = $category->getPrivacy();
				$categoryPrivacyContexts = $category->getPrivacyContexts();
				if($categoryPrivacyContexts)
				{
					$categoryPrivacyContexts = explode(',', $categoryPrivacyContexts);

					foreach ($categoryPrivacyContexts as $categoryPrivacyContext)
					{
						if(trim($categoryPrivacyContext) == '')
							$categoryPrivacyContext = self::DEFAULT_CONTEXT;

						if(!isset($privacyContexts[$categoryPrivacyContext]) || $privacyContexts[$categoryPrivacyContext] > $categoryPrivacy)
							$privacyContexts[trim($categoryPrivacyContext)] = $categoryPrivacy;
					}
				}
				else
				{
					$privacyContexts[self::DEFAULT_CONTEXT] = PrivacyType::ALL;
				}
			}
		}

		return $privacyContexts;
	}

	private static function getPrivacyContextsByCategoryEntries(entry $entry)
	{
		$privacyContexts = array();
		$categoriesIds = array();

		//get category entries that have privacy context
		$categoryEntries = categoryEntryPeer::retrieveByEntryIdStatusPrivacyContextExistance($entry->getId(), null, true);
		foreach ($categoryEntries as $categoryEntry)
		{
			$categoriesIds[] = $categoryEntry->getCategoryId();
		}

		$categories = self::getCategoriesByIds($categoriesIds);
		foreach ($categories as $category)
		{
			$categoryPrivacy = $category->getPrivacy();
			$categoryPrivacyContext = $category->getPrivacyContexts();
			if(!isset($privacyContexts[$categoryPrivacyContext]) || $privacyContexts[$categoryPrivacyContext] > $categoryPrivacy)
				$privacyContexts[trim($categoryPrivacyContext)] = $categoryPrivacy;
		}

		$noPrivacyContextCategory = categoryEntryPeer::retrieveOneByEntryIdStatusPrivacyContextExistance($entry->getId());
		if($noPrivacyContextCategory)
			$privacyContexts[ self::DEFAULT_CONTEXT ] = PrivacyType::ALL;

		return $privacyContexts;
	}

	public static function getEntitledVuserByPrivacyContext()
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;

		$privacyContextSearch = array();

		$vs = vs::fromSecureString(vCurrentContext::$vs);
		$vsPrivacyContexts = null;
		if ($vs)
			$vsPrivacyContexts = $vs->getPrivacyContext();

		if(is_null($vsPrivacyContexts) || $vsPrivacyContexts == '')
			$vsPrivacyContexts = self::DEFAULT_CONTEXT . $partnerId;

		$vsPrivacyContexts = explode(',', $vsPrivacyContexts);

		$privacyContexts = $vsPrivacyContexts;
		$privacyContexts[] = self::ENTRY_PRIVACY_CONTEXT;

		// get the groups that the user belongs to in case she is not associated to the category directly
		$vuserIds = VuserVgroupPeer::retrieveVgroupIdsByVuserId(vCurrentContext::getCurrentVsVuserId());
		$vuserIds[] = vCurrentContext::getCurrentVsVuserId();
		foreach ($privacyContexts as $privacyContext){
			foreach ( $vuserIds as $vuserId){
				$privacyContextSearch[] = $privacyContext . '_' . $vuserId;
			}
		}

		return $privacyContextSearch;
	}
	public static function getVsPrivacyContext()
	{
		$partnerId = vCurrentContext::$vs_partner_id ? vCurrentContext::$vs_partner_id : vCurrentContext::$partner_id;

		$vs = vs::fromSecureString(vCurrentContext::$vs);
		if(!$vs)
			return array(self::getDefaultContextString( $partnerId ) );

		$vsPrivacyContexts = $vs->getPrivacyContext();
		if(is_null($vsPrivacyContexts) || $vsPrivacyContexts == '')
			return array(self::getDefaultContextString( $partnerId ));
		else
		{
			$vsPrivacyContexts = explode(',', $vsPrivacyContexts);
			$vsPrivacyContexts = self::addPrivacyContextsPrefix( $vsPrivacyContexts, $partnerId);
		}

		return $vsPrivacyContexts;
	}

	/**
	 * Function returns the privacy context(s) found on the VS, if none are found returns array containing DEFAULT_PC
	 */
	public static function getVsPrivacyContextArray()
	{
		$partnerId = vCurrentContext::$vs_partner_id ? vCurrentContext::$vs_partner_id : vCurrentContext::$partner_id;

		$vs = vs::fromSecureString(vCurrentContext::$vs);
		if(!$vs)
			return array(self::DEFAULT_CONTEXT);

		$vsPrivacyContexts = $vs->getPrivacyContext();
		if(is_null($vsPrivacyContexts) || $vsPrivacyContexts == '')
			return array(self::DEFAULT_CONTEXT);

		return explode(',', $vsPrivacyContexts);
	}

	protected static function initCategoryModeration (vs $vs = null)
	{
		if (!$vs)
			return;

		$enableCategoryModeration = $vs->getEnableCategoryModeration();
		if ($enableCategoryModeration)
			self::$categoryModeration = true;
	}

	/**
	 * @param entry $dbEntry
	 * @return bool if current user is admin / entry's owner / co-editor
	 */
	public static function isEntitledForEditEntry( entry $dbEntry )
	{
		if ( vCurrentContext::$is_admin_session || vCurrentContext::getCurrentVsVuserId() == $dbEntry->getVuserId())
			return true;

		return $dbEntry->isEntitledVuserEdit(vCurrentContext::getCurrentVsVuserId());
	}
}
