<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.items
 */
class ESearchEntryItem extends ESearchItem
{

	/**
	 * @var ESearchEntryFieldName
	 */
	protected $fieldName;

	/**
	 * @var string
	 */
	protected $searchTerm;

	private static $allowed_search_types_for_field = array(
		'_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, ESearchUnifiedItem::UNIFIED),
		'name' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::PARTIAL'=> ESearchItemType::PARTIAL, 'ESearchItemType::STARTS_WITH'=> ESearchItemType::STARTS_WITH, ESearchUnifiedItem::UNIFIED),
		'description' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::PARTIAL'=> ESearchItemType::PARTIAL, 'ESearchItemType::STARTS_WITH'=> ESearchItemType::STARTS_WITH, 'ESearchItemType::EXISTS'=> ESearchItemType::EXISTS, ESearchUnifiedItem::UNIFIED),
		'tags' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::PARTIAL'=> ESearchItemType::PARTIAL, 'ESearchItemType::STARTS_WITH'=> ESearchItemType::STARTS_WITH, 'ESearchItemType::EXISTS'=> ESearchItemType::EXISTS, ESearchUnifiedItem::UNIFIED),
		'vuser_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH),
		'creator_vuser_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH),
		'start_date' => array('ESearchItemType::RANGE'=>ESearchItemType::RANGE, 'ESearchItemType::EXISTS'=> ESearchItemType::EXISTS),
		'end_date' => array('ESearchItemType::RANGE'=>ESearchItemType::RANGE, 'ESearchItemType::EXISTS'=> ESearchItemType::EXISTS),
		'reference_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS, ESearchUnifiedItem::UNIFIED),
		'conversion_profile_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, ESearchUnifiedItem::UNIFIED),
		'redirect_entry_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS, ESearchUnifiedItem::UNIFIED),
		'entitled_vusers_edit' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'entitled_vusers_publish' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'entitled_vusers_view' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'template_entry_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS, ESearchUnifiedItem::UNIFIED),
		'parent_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS, ESearchUnifiedItem::UNIFIED),
		'media_type' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH),
		'source_type' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH),
		'recorded_entry_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS, ESearchUnifiedItem::UNIFIED),
		'push_publish' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'length_in_msecs' => array('ESearchItemType::RANGE' => ESearchItemType::RANGE),
		'created_at' => array('ESearchItemType::RANGE' => ESearchItemType::RANGE),
		'updated_at' => array('ESearchItemType::RANGE' => ESearchItemType::RANGE),
		'moderation_status' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'entry_type' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH),
		'admin_tags' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'credit' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'site_url' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'access_control_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'external_source_type' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'is_quiz' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::EXISTS' => ESearchItemType::EXISTS),
		'is_live' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH),
		'user_names' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH, 'ESearchItemType::PARTIAL'=> ESearchItemType::PARTIAL, 'ESearchItemType::STARTS_WITH'=> ESearchItemType::STARTS_WITH, 'ESearchItemType::EXISTS'=> ESearchItemType::EXISTS, ESearchUnifiedItem::UNIFIED),
		'root_id' => array('ESearchItemType::EXACT_MATCH'=> ESearchItemType::EXACT_MATCH),
	);

	protected static $field_boost_values = array(
		'_id' => 100,
		'name' => 100,
		'description' => 100,
		'tags' => 100,
		'reference_id' => 100,
		'vuser_id' => 50,
		'creator_vuser_id' => 50,
		'entitled_vusers_edit' => 50,
		'entitled_vusers_publish' => 50,
		'entitled_vusers_view' => 50,
	);

	private static $multiLanguageFields = array(
		ESearchEntryFieldName::NAME,
		ESearchEntryFieldName::DESCRIPTION,
	);

	private static $ignoreDisplayInSearchFields = array(
		ESearchEntryFieldName::PARENT_ENTRY_ID,
		ESearchEntryFieldName::ID,
	);

	protected static $searchHistoryFields = array(
		ESearchEntryFieldName::NAME,
		ESearchEntryFieldName::DESCRIPTION,
		ESearchEntryFieldName::TAGS,
	);

	protected static $booleanFields = array(
		ESearchEntryFieldName::IS_LIVE,
		ESearchEntryFieldName::IS_QUIZ,
	);

	/**
	 * @return ESearchEntryFieldName
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

	/**
	 * @param ESearchEntryFieldName $fieldName
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

	public static function getAllowedSearchTypesForField()
	{
		return array_merge(self::$allowed_search_types_for_field, parent::getAllowedSearchTypesForField());
	}

	/**
	 * @param $eSearchItemsArr
	 * @param $boolOperator
	 * @param ESearchQueryAttributes $queryAttributes
	 * @param null $eSearchOperatorType
	 * @return array
	 */
	public static function createSearchQuery($eSearchItemsArr, $boolOperator, &$queryAttributes, $eSearchOperatorType = null)
	{
		$entryQuery = array();
		$allowedSearchTypes = ESearchEntryItem::getAllowedSearchTypesForField();
		$queryAttributes->getQueryHighlightsAttributes()->setScopeToGlobal();
		foreach ($eSearchItemsArr as $entrySearchItem)
		{
			$entrySearchItem->getSingleItemSearchQuery($entryQuery, $allowedSearchTypes, $queryAttributes);
		}

		return $entryQuery;
	}

	/**
	 * @param $entryQuery
	 * @param $allowedSearchTypes
	 * @param $queryAttributes
	 */
	public function getSingleItemSearchQuery(&$entryQuery, $allowedSearchTypes, &$queryAttributes)
	{
		$this->validateItemInput();
		$subQuery = null;
		switch ($this->getItemType())
		{
			case ESearchItemType::EXACT_MATCH:
				$subQuery = $this->getExactMatchQuery($allowedSearchTypes, $queryAttributes);
				break;
			case ESearchItemType::PARTIAL:
				$subQuery = vESearchQueryManager::getPartialQuery($this, $this->getFieldName(), $queryAttributes);
				break;
			case ESearchItemType::STARTS_WITH:
				$subQuery = vESearchQueryManager::getPrefixQuery($this, $this->getFieldName(), $allowedSearchTypes, $queryAttributes);
				break;
			case ESearchItemType::EXISTS:
				$subQuery = vESearchQueryManager::getExistsQuery($this, $this->getFieldName(), $allowedSearchTypes, $queryAttributes);
				break;
			case ESearchItemType::RANGE:
				$subQuery = vESearchQueryManager::getRangeQuery($this, $this->getFieldName(), $allowedSearchTypes, $queryAttributes);
				break;
			default:
				VidiunLog::log("Undefined item type[".$this->getItemType()."]");
		}

		if ($this->getItemType() == ESearchItemType::EXACT_MATCH && in_array($this->getFieldName(), self::$ignoreDisplayInSearchFields))
			$queryAttributes->getQueryFilterAttributes()->addValueToIgnoreDisplayInSearch($this->getFieldName(), $this->getSearchTerm());

		if($subQuery)
			$entryQuery[] = $subQuery;
	}

	protected function getExactMatchQuery($allowedSearchTypes, &$queryAttributes)
	{
		$exactQuery = vESearchQueryManager::getExactMatchQuery($this, $this->getFieldName(), $allowedSearchTypes, $queryAttributes);

		if (in_array($this->getFieldName(), array(ESearchEntryFieldName::ENTITLED_USER_EDIT,ESearchEntryFieldName::ENTITLED_USER_PUBLISH,
			ESearchEntryFieldName::ENTITLED_USER_VIEW, ESearchEntryFieldName::USER_ID)))
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

	public function shouldAddLanguageSearch()
	{
		if(in_array($this->getFieldName(), self::$multiLanguageFields))
			return true;

		return false;
	}

	public function getItemMappingFieldsDelimiter()
	{
		return elasticSearchUtils::DOT_FIELD_DELIMITER;
	}

	public function getFilteredObjectId()
	{
		if($this->getFieldName() == ESearchEntryFieldName::ID)
		{
			return elasticSearchUtils::formatSearchTerm($this->getSearchTerm());
		}
		return null;
	}

}
