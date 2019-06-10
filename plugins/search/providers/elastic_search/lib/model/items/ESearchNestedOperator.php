<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.items
 */
class ESearchNestedOperator extends ESearchBaseOperator
{

	const ESEARCH_NESTED_OPERATOR = 'ESearchNestedOperator';

	protected static function createSearchQueryForItems($categorizedSearchItems, $boolOperator, &$queryAttributes)
	{
		$shouldCreateNested = self::initNestedOperatorQuery($queryAttributes);

		$outQuery = new vESearchBoolQuery();
		foreach ($categorizedSearchItems as $categorizedSearchItem)
		{
			list($itemClassName, $itemSearchItems, $operatorType) = self::getParamsFromCategorizedSearchItem($categorizedSearchItem);
			$queryAttributes->addToNestedOperatorObjectTypes($itemClassName);
			//call createSearchQuery on child nested object items or nested operator
			$subQuery = $itemClassName::createSearchQuery($itemSearchItems, $boolOperator, $queryAttributes, $operatorType);
			self::addSubQueryToFinalQuery($subQuery, $outQuery, $itemClassName, $boolOperator);
		}

		if($shouldCreateNested)
			$outQuery = self::createNestedQueryForOperator($outQuery, $queryAttributes);

		return $outQuery;
	}

	private static function initNestedOperatorQuery(&$queryAttributes)
	{
		$shouldCreateNested = false;
		if($queryAttributes->isInitNestedQuery())
		{
			$shouldCreateNested = true;
			$queryAttributes->setNestedOperatorContext(true);
			$queryAttributes->setInitNestedQuery(false);
			$queryAttributes->getQueryHighlightsAttributes()->setScopeToInner();
			$queryAttributes->resetNestedOperatorObjectTypes();
		}
		return $shouldCreateNested;
	}

	private static function createNestedQueryForOperator(&$boolQuery, &$queryAttributes)
	{
		if(!$queryAttributes->validateNestedOperatorObjectTypes())
			throw new vESearchException('mixed search items in nested operator not allowed', vESearchException::MIXED_SEARCH_ITEMS_IN_NESTED_OPERATOR_NOT_ALLOWED);

		$outQuery = vESearchQueryManager::getNestedQuery($boolQuery, $queryAttributes);
		$queryAttributes->setNestedQueryName(null);
		$queryAttributes->setNestedOperatorContext(false);
		return $outQuery;
	}

}
