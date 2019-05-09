<?php

/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */
class vEntryElasticEntitlement extends vBaseElasticEntitlement
{
    
    public static $privacyContext = null;
    public static $privacy = null;
    public static $userEntitlement = false;
    public static $userCategoryToEntryEntitlement = false;
    public static $entriesDisabledEntitlement = array();
    public static $publicEntries = false; //active + pending
    public static $publicActiveEntries = false; //active
    public static $parentEntitlement = false;
    public static $entryInSomeCategoryNoPC = false; //active + pending
    public static $filteredCategoryIds = array();

    protected static $entitlementContributors = array(
        'vElasticEntryDisableEntitlementDecorator',
        'vElasticPublicEntriesEntitlementDecorator',
        'vElasticUserCategoryEntryEntitlementDecorator',
        'vElasticUserEntitlementDecorator',
    );

    protected static function initialize()
    {
        parent::initialize();

        //check if we need to enforce entitlement
        if(!self::shouldEnforceEntitlement())
            return;

        self::initializeParentEntitlement();
        self::initializeDisableEntitlement(self::$vs);
        self::initializeUserEntitlement(self::$vs);

        if(self::$vs)
            self::$privacyContext = self::$vs->getPrivacyContext();

        self::initializePublicEntryEntitlement(self::$vs);
        self::initializeUserCategoryEntryEntitlement(self::$vs);
        
        self::$isInitialized = true;
    }

    private static function shouldEnforceEntitlement()
    {
        return vEntitlementUtils::getEntitlementEnforcement();
    }

    private static function initializeParentEntitlement()
    {
        if(!(PermissionPeer::isValidForPartner(PermissionName::FEATURE_DISABLE_PARENT_ENTRY_SECURITY_INHERITANCE, self::$partnerId)))
        {
            //we need to add entitlement check on the parent
            self::$parentEntitlement = true;
        }
    }

    private static function initializeDisableEntitlement($vs)
    {
        if($vs && count($vs->getDisableEntitlementForEntry()))
        {
            //disable entitlement for entries
            $entries = $vs->getDisableEntitlementForEntry();
            self::$entriesDisabledEntitlement = $entries;
        }
    }

    private static function initializeUserEntitlement($vs)
    {
        if($vs && self::$vuserId)
        {
            self::$userEntitlement = true;
        }
    }

    private static function initializePublicEntryEntitlement($vs)
    {
        if(!$vs)
        {
            self::$publicActiveEntries = true; //add entries that are not in any active category
        }
        else //vs
        {
            if(!PermissionPeer::isValidForPartner(PermissionName::FEATURE_DISABLE_CATEGORY_LIMIT, self::$partnerId) && !self::$privacyContext)
                self::$publicEntries = true; //return entries that are not in any active/pending category
        }
    }

    private static function initializeUserCategoryEntryEntitlement($vs)
    {
        if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_DISABLE_CATEGORY_LIMIT, self::$partnerId))
        {
            if(!self::$privacyContext)//add entries that are in some category and doesnt have pc
                self::$entryInSomeCategoryNoPC = true;
        }

        if(self::$vuserId)
        {
            $privacy = array(PrivacyType::ALL);
            if($vs && !$vs->isAnonymousSession())
                $privacy[] = PrivacyType::AUTHENTICATED_USERS;

            self::$privacy = $privacy;
            self::$filteredCategoryIds = array();
            self::$userCategoryToEntryEntitlement = true;
        }
    }

    public static function setFilteredCategoryIds(ESearchOperator $eSearchOperator, $objectId)
    {
        if($eSearchOperator->getOperator() != ESearchOperatorType::AND_OP)
            return;

        $searchItems = $eSearchOperator->getSearchItems();
        $filteredCategoryIds = array();
        $filteredEntryId = $objectId ? array($objectId) : array();
        foreach ($searchItems as $searchItem)
        {
            $filteredObjectId = $searchItem->getFilteredObjectId();
            if ($filteredObjectId)
            {
                $filteredEntryId[] = $filteredObjectId;
            }
            $FilteredCategoryId = $searchItem->getFilteredCategoryId();
            if ($FilteredCategoryId)
            {
                $filteredCategoryIds[] = $FilteredCategoryId;
            }
        }

        $filteredCategoriesByEntryId = self::getCategoryIdsForEntryId($filteredEntryId);
        $filteredCategoryIds = array_merge($filteredCategoryIds, $filteredCategoriesByEntryId);
        self::$filteredCategoryIds = $filteredCategoryIds;
    }

    protected static function getCategoryIdsForEntryId($filteredEntryId)
    {
        $filteredCategoryIds = array();
        $filteredEntriesIds = array_unique($filteredEntryId);
        $filteredEntriesIds = array_values($filteredEntriesIds);
        if (count($filteredEntriesIds) == 1)
        {
            $categoryEntries = categoryEntryPeer::selectByEntryId($filteredEntriesIds[0]);
            foreach ($categoryEntries as $categoryEntry)
            {
                $filteredCategoryIds[] = $categoryEntry->getCategoryId();
            }
        }
        return $filteredCategoryIds;
    }

}
