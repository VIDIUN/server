<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

class vElasticDisplayAndMemberEntitlementDecorator extends vElasticCategoryEntitlementDecorator
{
	public static function getEntitlementCondition(array $params = array(), $fieldPrefix = '')
	{
		$query = new vESearchBoolQuery();
		$query->addToShould(self::getDisplayInSearchQuery());
		return self::getMembersQuery($query);
	}


	/**
	 * @param vESearchBoolQuery $query
	 * @return vESearchTermsQuery
	 */
	private static function getMembersQuery($query)
	{
		$vuser = null;
		if (vCurrentContext::$vs)
			$vuser = vCurrentContext::getCurrentVsVuser();

		if ($vuser)
		{
			// get the groups that the user belongs to in case she is not associated to the category directly
			//vuser ids are equivalent to members in our elastic search
			$userGroupsQuery = new vESearchTermsQuery(ESearchCategoryFieldName::VUSER_IDS,array(
				'index' => ElasticIndexMap::ELASTIC_VUSER_INDEX,
				'type' => ElasticIndexMap::ELASTIC_VUSER_TYPE,
				'id' => $vuser->getId(),
				'path' => ESearchUserFieldName::GROUP_IDS
			));

			$query->addToShould($userGroupsQuery);
			$userQuery = new vESearchTermQuery(ESearchCategoryFieldName::VUSER_IDS, $vuser->getId());
			$query->addToShould($userQuery);
		}

		return $query;
	}

	private static function getDisplayInSearchQuery()
	{
		return new vESearchTermQuery(ESearchCategoryFieldName::DISPLAY_IN_SEARCH,DisplayInSearchType::PARTNER_ONLY);
	}
}
