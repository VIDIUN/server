<?php

/**
 * Retrieve information and invoke actions on Flavor Asset
 *
 * @service drmLicenseAccess
 * @package plugins.drm
 * @subpackage api.services
 */

class DrmLicenseAccessService extends VidiunBaseService
{

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		if (!DrmPlugin::isAllowedPartner(vCurrentContext::$vs_partner_id))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, DrmPlugin::PLUGIN_NAME);
	}

    /**
     * getAccessAction
     * input: flavor ids, drmProvider
     * Get Access Action
     * @action getAccess
     * @param string $entryId
     * @param string $flavorIds
     * @param string $referrer
* @return VidiunDrmLicenseAccessDetails
     **/
    public function getAccessAction($entryId, $flavorIds, $referrer)
    {
        $response = new VidiunDrmLicenseAccessDetails();
        $response->policy = "";
        $response->duration = 0;
        $response->absolute_duration = 0;
        $flavorIdsArr = explode(",",$flavorIds);

        $entry = entryPeer::retrieveByPK($entryId);
        if (isset($entry))
        {
            try {
                $drmLU = new DrmLicenseUtils($entry, $referrer);
                if ($this->validateFlavorAssetssAllowed($drmLU, $flavorIdsArr) == true)
                {
                    $policyId = $drmLU->getPolicyId();
                    VidiunLog::info("policy_id is '$policyId'");

                    $dbPolicy = DrmPolicyPeer::retrieveByPK($policyId);
                    if (isset($dbPolicy)) {

                        $expirationDate = DrmLicenseUtils::calculateExpirationDate($dbPolicy, $entry);

                        $response->policy = $dbPolicy->getName();
                        $response->licenseParams = $this->buildPolicy($dbPolicy);
                        $response->duration = $expirationDate;
                        $response->absolute_duration = $expirationDate;
                        VidiunLog::info("response is  '" . print_r($response, true) . "' ");
                    } else {
                        VidiunLog::err("Could not get DRM policy from DB");
                    }
                }
            } catch (Exception $e) {
                VidiunLog::err("Could not validate license access, returned with message '".$e->getMessage()."'");
            }
        }
        else
        {
            VidiunLog::err("Entry '$entryId' not found");
        }
        return $response;

    }

    protected function validateFlavorAssetssAllowed(DrmLicenseUtils $drmLU, $flavorIdsArr)
    {
        $secureEntryHelper = $drmLU->getSecureEntryHelper();
        foreach($flavorIdsArr as $flavorId)
        {
            $flavorAsset = assetPeer::retrieveById($flavorId);
            if (isset($flavorAsset))
            {
                if (!$secureEntryHelper->isAssetAllowed($flavorAsset))
                {
                    VidiunLog::err("Asset '$flavorId' is not allowed according to policy'");
                    return false;
                }
            }
        }
        return true;
    }

    protected function buildPolicy(DrmPolicy $dbDrmPolicy)
    {
        $licenseParams = $dbDrmPolicy->getLicenseParams();
        if (is_null($licenseParams))
            return null;
        return $licenseParams;
    }

}