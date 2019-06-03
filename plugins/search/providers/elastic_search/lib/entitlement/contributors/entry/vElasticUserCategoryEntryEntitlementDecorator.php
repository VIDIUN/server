<?php
/**
 * @package plugins.elasticSearch
 * @subpackage lib.entitlement
 */

class vElasticUserCategoryEntryEntitlementDecorator implements IVidiunESearchEntryEntitlementDecorator
{
	const MAX_CATEGORIES = 512;

	public static function shouldContribute()
	{
		if(vEntryElasticEntitlement::$userCategoryToEntryEntitlement || vEntryElasticEntitlement::$entryInSomeCategoryNoPC)
			return true;

		return false;
	}

	public static function getEntitlementCondition(array $params = array(), $fieldPrefix = '')
	{
		$condition = new vESearchBoolQuery();
		//members
		$ids = self::getFormattedCategoryIds($params['category_ids']);
		$idsFieldName = ESearchBaseCategoryEntryItem::CATEGORY_IDS_MAPPING_FIELD;
		$idsCondition = new vESearchTermsQuery("{$fieldPrefix}{$idsFieldName}", $ids);
		$condition->addToShould($idsCondition);
		//privacy_by_contexts
		$privacyByContexts = array();
		$privacyContexts = self::getPrivacyContexts(vEntryElasticEntitlement::$privacyContext);
		foreach ($privacyContexts as $privacyContext)
		{
			foreach (vEntryElasticEntitlement::$privacy as $privacyValue)
			{
				$privacyByContexts[] = elasticSearchUtils::formatSearchTerm($privacyContext . vEntitlementUtils::TYPE_SEPERATOR . $privacyValue);
			}
		}
		$pcFieldName = ESearchEntryFieldName::PRIVACY_BY_CONTEXTS;
		$privacyByContextCondition = new vESearchTermsQuery("{$fieldPrefix}{$pcFieldName}", $privacyByContexts);
		$condition->addToShould($privacyByContextCondition);

		return $condition;
	}

	public static function applyCondition(&$entryQuery, &$parentEntryQuery)
	{
		$vuserId = vEntryElasticEntitlement::$vuserId;
		if(!$vuserId)
		{
			VidiunLog::log('cannot add userCategory to entry entitlement to elastic without a vuserId - setting vuser id to -1');
			$vuserId = -1;
		}
		//get category ids with $privacyContext
		$categories = self::getUserCategories($vuserId, vEntryElasticEntitlement::$privacyContext);
		if(count($categories) == 0)
			$categories = array(category::CATEGORY_ID_THAT_DOES_NOT_EXIST);

		$params['category_ids'] = $categories;

		if($parentEntryQuery)
		{
			$condition = self::getEntitlementCondition($params, 'parent_entry.');
			$parentEntryQuery->addToShould($condition);
		}

		$condition = self::getEntitlementCondition($params);
		$entryQuery->addToShould($condition);
	}

	protected static function getPrivacyContexts($privacyContext)
	{
		$privacyContexts = null;
		if (!$privacyContext || trim($privacyContext) == '')
		{
			$privacyContexts = array(vEntitlementUtils::getDefaultContextString(vEntryElasticEntitlement::$partnerId));
		}
		else
		{
			$privacyContexts = explode(',', $privacyContext);
			$privacyContexts = vEntitlementUtils::addPrivacyContextsPrefix($privacyContexts, vEntryElasticEntitlement::$partnerId);
		}

		$privacyContexts = array_map('elasticSearchUtils::formatSearchTerm', $privacyContexts);
		return $privacyContexts;
	}

	protected static function getUserCategories($vuserId, $privacyContext = null)
	{
		$maxUserCategories = vConf::get('maxUserCategories', 'elastic', self::MAX_CATEGORIES);

		$params = array(
			'index' => ElasticIndexMap::ELASTIC_CATEGORY_INDEX,
			'type' => ElasticIndexMap::ELASTIC_CATEGORY_TYPE,
			'size' => $maxUserCategories
		);
		$body = array();
		$body['_source'] = false;

		$mainBool = new vESearchBoolQuery();
		$partnerStatus = elasticSearchUtils::formatPartnerStatus(vEntryElasticEntitlement::$partnerId, CategoryStatus::ACTIVE);
		$partnerStatusQuery = new vESearchTermQuery('partner_status', $partnerStatus);
		$mainBool->addToFilter($partnerStatusQuery);

		if (count(vEntryElasticEntitlement::$filteredCategoryIds))
		{
			$filteredCategoryIdsQuery = new vESearchTermsQuery('_id', vEntryElasticEntitlement::$filteredCategoryIds);
			$mainBool->addToFilter($filteredCategoryIdsQuery);
		}

		$conditionsBoolQuery = new vESearchBoolQuery();

		$userGroupsQuery = new vESearchTermsQuery(ESearchCategoryFieldName::VUSER_IDS,array(
			'index' => ElasticIndexMap::ELASTIC_VUSER_INDEX,
			'type' => ElasticIndexMap::ELASTIC_VUSER_TYPE,
			'id' => $vuserId,
			'path' => 'group_ids'
		));
		$conditionsBoolQuery->addToShould($userGroupsQuery);
		$userQuery = new vESearchTermQuery(ESearchCategoryFieldName::VUSER_IDS, $vuserId);
		$conditionsBoolQuery->addToShould($userQuery);

		if(vEntryElasticEntitlement::$entryInSomeCategoryNoPC)
		{
			$noPcQuery = new vESearchBoolQuery();
			$pcExistQuery = new vESearchExistsQuery('privacy_context');
			$noPcQuery->addToMustNot($pcExistQuery);
			$conditionsBoolQuery->addToShould($noPcQuery);
		}

		$privacyContexts = self::getPrivacyContexts($privacyContext);
		$privacyContextsQuery = new vESearchTermsQuery('privacy_contexts',$privacyContexts);
		$mainBool->addToFilter($privacyContextsQuery);

		//fetch only categories with privacy MEMBERS_ONLY
		//categories with privacy ALL/AUTHENTICATED_USERS will be handled with privacy_by_contexts
		$privacy = category::formatPrivacy(PrivacyType::MEMBERS_ONLY, vEntryElasticEntitlement::$partnerId);
		$privacyQuery = new vESearchTermQuery('privacy', elasticSearchUtils::formatSearchTerm($privacy));
		$mainBool->addToFilter($privacyQuery);

		$mainBool->addToFilter($conditionsBoolQuery);
		$body['query'] = $mainBool->getFinalQuery();
		$params['body'] = $body;
		//order categories by updated at
		$params['body']['sort'] = array('updated_at' => 'desc');
		$elasticClient = new elasticClient();
		$results = $elasticClient->search($params, true);
		$categories = $results['hits']['hits'];

		$categoriesCount = $results['hits']['total'];
		if ($categoriesCount > $maxUserCategories)
		{
			VidiunLog::debug("More then max user categories found. userId[$vuserId] count[$categoriesCount]");
		}

		$categoryIds = array();

		foreach ($categories as $category)
		{
			$categoryIds[] = $category['_id'];
		}
		return $categoryIds;
	}

	private static function getFormattedCategoryIds($categoryIds)
	{
		$searchIds = array();
		foreach ($categoryIds as $categoryId)
		{
			$searchIds[] = elasticSearchUtils::formatCategoryIdStatus($categoryId, CategoryEntryStatus::ACTIVE);
			$searchIds[] = elasticSearchUtils::formatCategoryIdStatus($categoryId, CategoryEntryStatus::PENDING);
		}
		return $searchIds;
	}

}
