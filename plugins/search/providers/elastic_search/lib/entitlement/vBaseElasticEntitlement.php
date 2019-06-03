<?php

/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */
abstract class vBaseElasticEntitlement
{
    public static $isInitialized = false;
    public static $partnerId;
    public static $vs;
    public static $vuserId = null;

    protected static $entitlementContributors = array();

    public static function init()
    {
        if(!self::$isInitialized)
            static::initialize();
    }

    protected static function initialize()
    {
        self::$vs = vs::fromSecureString(vCurrentContext::$vs);
        self::$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
        self::$vuserId = self::getVuserIdForEntitlement(self::$partnerId, self::$vuserId, self::$vs);
    }

    public static function getEntitlementContributors()
    {
        return static::$entitlementContributors;
    }

    protected static function getVuserIdForEntitlement($partnerId, $vuserId = null, $vs = null)
    {
        if($vs && !$vuserId)
        {
            $vuser = vuserPeer::getVuserByPartnerAndUid($partnerId, vCurrentContext::$vs_uid, true);
            if ($vuser)
                $vuserId = $vuser->getId();
        }

        return $vuserId;
    }

	public static function getEntitlementFilterQueries()
	{
		$result = null;
		$contributors = self::getEntitlementContributors();
		foreach ($contributors as $contributor)
		{
			if($contributor::shouldContribute())
			{
				$result[] = $contributor::getEntitlementCondition();
			}
		}

		return $result;
	}
}
