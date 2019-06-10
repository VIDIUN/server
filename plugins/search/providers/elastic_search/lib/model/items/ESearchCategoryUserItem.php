<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.items
 */
class ESearchCategoryUserItem extends ESearchItem
{

	/**
	 * @var ESearchCategoryUserFieldName
	 */
	protected $fieldName;

	/**
	 * @var string
	 */
	protected $searchTerm;

	/**
	 * @var CategoryVuserPermissionLevel
	 */
	protected $permissionLevel;

	/**
	 * @var string
	 */
	protected $permissionName;

	private static $allowed_search_types_for_field = array(
		ESearchCategoryUserFieldName::USER_ID => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH),
	);

	/**
	 * @return ESearchCategoryUserFieldName
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

	/**
	 * @param ESearchCategoryUserFieldName $fieldName
	 */
	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;
	}

	/**
	 * @return string
	 */
	public function getSearchTerm()
	{
		return $this->searchTerm;
	}

	/**
	 * @param string $searchTerm
	 */
	public function setSearchTerm($searchTerm)
	{
		$this->searchTerm = $searchTerm;
	}

	/**
	 * @return CategoryVuserPermissionLevel
	 */
	public function getPermissionLevel()
	{
		return $this->permissionLevel;
	}

	/**
	 * @param CategoryVuserPermissionLevel $permissionLevel
	 */
	public function setPermissionLevel($permissionLevel)
	{
		$this->permissionLevel = $permissionLevel;
	}

	/**
	 * @return string
	 */
	public function getPermissionName()
	{
		return $this->permissionName;
	}

	/**
	 * @param string $permissionName
	 */
	public function setPermissionName($permissionName)
	{
		$this->permissionName = $permissionName;
	}

	public static function getAllowedSearchTypesForField()
	{
		return array_merge(self::$allowed_search_types_for_field, parent::getAllowedSearchTypesForField());
	}

	public static function createSearchQuery($eSearchItemsArr, $boolOperator, &$queryAttributes, $eSearchOperatorType = null)
	{
		$categoryUserQuery = array();
		$allowedSearchTypes = ESearchCategoryUserItem::getAllowedSearchTypesForField();
		$queryAttributes->getQueryHighlightsAttributes()->setScopeToGlobal();
		foreach ($eSearchItemsArr as $categoryUserSearchItem)
		{
			$categoryUserSearchItem->getSingleItemSearchQuery($categoryUserQuery, $allowedSearchTypes, $queryAttributes);
		}

		return $categoryUserQuery;
	}

	public function getSingleItemSearchQuery(&$categoryUserQuery, $allowedSearchTypes, &$queryAttributes)
	{
		$this->validateItemInput();
		switch ($this->getItemType())
		{
			case ESearchItemType::EXACT_MATCH:
				$categoryUserQuery[] = $this->getCategoryUserExactMatchQuery($allowedSearchTypes, $queryAttributes);
				break;
			default:
				VidiunLog::log("Undefined item type[".$this->getItemType()."]");
		}
	}

	protected function getCategoryUserExactMatchQuery($allowedSearchTypes, &$queryAttributes)
	{
		if($this->shouldAddPermissionsSearch())
			return $this->getUserIdExactMatchWithPermissions($allowedSearchTypes, $queryAttributes);

		return $this->getExactMatchQuery($allowedSearchTypes, $queryAttributes);
	}


	protected function getExactMatchQuery($allowedSearchTypes, &$queryAttributes)
	{
		$exactQuery = vESearchQueryManager::getExactMatchQuery($this, $this->getFieldName(), $allowedSearchTypes, $queryAttributes);

		if ($this->getFieldName()  ==  ESearchCategoryUserFieldName::USER_ID)
		{
			$preFixGroups = new vESearchTermsQuery($this->getFieldName(),
				array('index' => ElasticIndexMap::ELASTIC_VUSER_INDEX,
					'type' => ElasticIndexMap::ELASTIC_VUSER_TYPE,
					'id' => $this->getSearchTerm(),
					'path' => ESearchUserFieldName::GROUP_IDS));
			$boolQuery = new vESearchBoolQuery();
			$boolQuery->addToShould($exactQuery);
			$boolQuery->addToShould($preFixGroups);
			return $boolQuery;
		}
		return $exactQuery;
	}



	private function shouldAddPermissionsSearch()
	{
		$permissionLevel = $this->getPermissionLevel();
		if(in_array($this->getFieldName(), array(ESearchCategoryUserFieldName::USER_ID)) &&
			(isset($permissionLevel) || $this->getPermissionName()))
			return true;
		return false;
	}

	protected function getUserIdExactMatchWithPermissions($allowedSearchTypes, &$queryAttributes)
	{
		$vuserId = $this->getSearchTerm();
		$groupIds = $this->getGroupIds($vuserId);
		$groupIds[] = $vuserId;

		$boolQuery = new vESearchBoolQuery();
		$item = clone $this;
		foreach ($groupIds as $groupId)
		{
			$permissionLevel = $item->getPermissionLevel();
			$permissionName = $item->getPermissionName();

			//got only permission level
			if (!is_null($permissionLevel) && !$permissionName)
			{
				$item->setSearchTerm(elasticSearchUtils::formatCategoryUserPermissionLevel($groupId, $permissionLevel));
				$permissionLevelQuery = vESearchQueryManager::getExactMatchQuery($item, ESearchCategoryUserFieldName::USER_ID, $allowedSearchTypes, $queryAttributes);
				$boolQuery->addToShould($permissionLevelQuery);
			}

			//got only permission name
			else if (is_null($permissionLevel) && $permissionName)
			{
				$item->setSearchTerm(elasticSearchUtils::formatCategoryUserPermissionName($groupId, $permissionName));
				$permissionNameQuery = vESearchQueryManager::getExactMatchQuery($item, ESearchCategoryUserFieldName::USER_ID, $allowedSearchTypes, $queryAttributes);
				$boolQuery->addToShould($permissionNameQuery);
			}

			else if (!is_null($permissionLevel) && $permissionName)
			{
				$subBoolQuery = new vESearchBoolQuery();

				$item->setSearchTerm(elasticSearchUtils::formatCategoryUserPermissionLevel($groupId, $permissionLevel));
				$permissionLevelQuery = vESearchQueryManager::getExactMatchQuery($item, ESearchCategoryUserFieldName::USER_ID, $allowedSearchTypes, $queryAttributes);
				$subBoolQuery->addToFilter($permissionLevelQuery);

				$item->setSearchTerm(elasticSearchUtils::formatCategoryUserPermissionName($groupId, $permissionName));
				$permissionNameQuery = vESearchQueryManager::getExactMatchQuery($item, ESearchCategoryUserFieldName::USER_ID, $allowedSearchTypes, $queryAttributes);
				$subBoolQuery->addToFilter($permissionNameQuery);

				$boolQuery->addToShould($subBoolQuery);
			}

		}

		return $boolQuery;
	}

	protected function getGroupIds($vuserId)
	{
		$params = array(
			elasticClient::ELASTIC_INDEX_KEY => ElasticIndexMap::ELASTIC_VUSER_INDEX,
			elasticClient::ELASTIC_TYPE_KEY => ElasticIndexMap::ELASTIC_VUSER_TYPE,
			elasticClient::ELASTIC_ID_KEY => $vuserId
		);

		$elasticClient = new elasticClient();
		$elasticResults = $elasticClient->get($params);
		$groupIds = array();
		if (isset($elasticResults[vESearchCoreAdapter::SOURCE][ESearchUserFieldName::GROUP_IDS]))
		{
			$groupIds = $elasticResults[vESearchCoreAdapter::SOURCE][ESearchUserFieldName::GROUP_IDS];
		}

		return $groupIds;
	}

	public function shouldAddLanguageSearch()
	{
		return false;
	}

	public function getItemMappingFieldsDelimiter()
	{

	}

}
