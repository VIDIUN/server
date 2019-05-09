<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

class vElasticPrivacyContextEntitlementDecorator extends vElasticCategoryEntitlementDecorator
{
	public static function getEntitlementCondition(array $params = array(), $fieldPrefix = '')
	{
		$privacyContexts = vEntitlementUtils::getVsPrivacyContext();
		if(!is_array($privacyContexts))
		{
			$privacyContexts = array($privacyContexts);
		}

		$privacyContexts = array_map('elasticSearchUtils::formatSearchTerm', $privacyContexts);
		return new vESearchTermsQuery(ESearchCategoryFieldName::PRIVACY_CONTEXTS, $privacyContexts);
	}
}
