<?php

/***
 * eSearchQuery parser will take a string-like query and will transform it to an eSearchParams object so it can be used for eSearch queries.
 * The process:
 * 1. Parse a string query and validate its format - will return an tree-like array representing the eSearchParams format
 * 2. Create an eSearchParams objects from the tree-like array - traversing the array and creating the eSearchParams items as we go.
 * Example string query contains valid complex objects:
 *      NOT (~entry_id"0_xafasda" And ^entry_tags:"my tags")
 *      OR (_metadata:"{xpath:demo,METADATA_PROFILE_ID:1214,term:myTerm,metadata_Field_Id:123"})
 *      OR (entry_length_in_msecs:"[get 20 ; lt 100]'
 *          AND entry_description )
 *      OR _all:"find this in all"
 */

/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */
class vESearchQueryParser
{
	const NOT_OPERAND = "NOT";
	const AND_OPERAND = "AND";
	const OR_OPERAND = "OR";
	const STARTS_WITH = '^';
	const PARTIAL = '~';
	const XPATH = 'XPATH';
	const METADATA_PROFILE_ID = 'METADATA_PROFILE_ID';
	const METADATA_FIELD_ID = 'METADATA_FIELD_ID';
	const TERM = 'TERM';
	const RANGE_ITEM_MIN_LENGTH = 3;
	const RANGE_CODE_MAX_LENGTH = 3;
	const RANGE_CODE_MIN_LENGTH = 2;
	const LESS_THAN = 'LT';
	const LESS_THAN_OR_EQUAL = 'LTE';
	const GREATER_THAN = 'GT';
	const GREATER_THAN_OR_EQUAL = 'GTE';

	/**
	 * @param $eSearchQuery
	 * @return VidiunESearchParams
	 * @throws vESearchException
	 */
	public static function buildVESearchParamsFromVESearchQuery($eSearchQuery)
	{
		// in case of a free text query (wihtout fields or brackets or special commands - a simple unified search object will be created
		$searchItem = null;
		if (self::isFreeTextQuery($eSearchQuery))
			$searchItem = self::createSimpleUnifiedSearchParam($eSearchQuery);
		else
		{
			$parsedQuery = self::parseVESearchQuery($eSearchQuery);
			$searchItem = self::createVESearchParams($parsedQuery);
		}
		return $searchItem;
	}

	/**
	 * Check if entire query is a only alphanumeric chars so we can create a simple unified search
	 * @param $eSearchQuery
	 * @return bool
	 */
	private static function isFreeTextQuery($eSearchQuery)
	{
		$aValid = array('_',' ');
		$query = str_replace($aValid, 'A', $eSearchQuery);
		return preg_match('/^[\p{L}\p{N} -]+$/u', $query);
	}

	/**
	 * @param $eSearchQuery
	 * @return VidiunESearchOperator
	 */
	private static function createSimpleUnifiedSearchParam($eSearchQuery)
	{
		$vSearchItem = new VidiunESearchUnifiedItem();
		$vSearchItem->itemType =  VidiunESearchItemType::EXACT_MATCH;
		$vSearchItem->searchTerm = $eSearchQuery;

		return $vSearchItem;
	}

	/**
	 * parseVESearchQuery Flow - recursive method to create an tree-like array by levels for the string query.
	 * 1. locate 1st level brackets ( ) example: id and ( tags and ( day and year ))) - will find the outer brackets only
	 * 2. accumulate tokens to string in order to handle the different parts without the inner brackets parts.
	 * 3. handle the accumulated part (before / in the middle / after the brackets)
	 *
	 * @param string $query
	 * @return array
	 * @throws vESearchException
	 */
	public static function parseVESearchQuery($query)
	{
		VidiunLog::debug("Parsing $query");
		//remove starting and trailing whitespaces
		$currentQuery = trim($query);
		// find next level inner queries within ( ) brackets - TODO add informative Example and description
		$innerQueriesMatcher = '~("|\').*?\1(*SKIP)(*FAIL)|\((?:[^()]|(?R))*\)~';
		preg_match_all($innerQueriesMatcher, $currentQuery, $innerQueries);

		$innerQueriesCounter = 0;
		$cursorLocation = 0;
		$eSearchQueryResult = array();
		$partialQuery = null;
		$levelOperand = null;
		$shouldBeOperand = false; // flag flip to control if next item should be an operand or a query part.

		//token iteration on current query
		while ($cursorLocation < strlen($currentQuery))
		{
			//Iterate and get tokens until finding an inner query
			if ($currentQuery[$cursorLocation] == '(')
			{
				//extract method
				//handle accumulated text until opening brackets
				if ($partialQuery)
				{
					self::handlePartialQueryAndAddToResult($partialQuery, $shouldBeOperand, $eSearchQueryResult, $levelOperand);
					$partialQuery = null;
				}
				if (empty($innerQueries[0]))
					throw new vESearchException('Un-matching brackets', vESearchException::UNMATCHING_BRACKETS);

				//get next inner query between ( ) brackets and parse it.
				$innerQuery = preg_replace('/(^\s*\()|(\)\s*$)/', '', $innerQueries[0][$innerQueriesCounter]);
				$innerQuery = trim($innerQuery);
				if ($innerQuery)
					$eSearchQueryResult[] = self::parseVESearchQuery($innerQuery);

				//move cursor location to end of inner query
				$cursorLocation = $cursorLocation + strlen($innerQueries[0][$innerQueriesCounter]);
				$innerQueriesCounter++;
			} else
			{
				$partialQuery = $partialQuery . $currentQuery[$cursorLocation];
				$cursorLocation++;
			}
		}
		//handling last part of query after last () brackets if exists
		if ($partialQuery)
			self::handlePartialQueryAndAddToResult($partialQuery, $shouldBeOperand, $eSearchQueryResult, $levelOperand);

		return $eSearchQueryResult;
	}

	/**
	 * handlePartialQueryAndAddToResult will handle any simple string with/without operands ( e.g. "id and tags and year )
	 * 1. trim the outer whitespaces and after every : (but not within quotes)
	 * 2. split words by whitespaces and iterate them one by one and:
	 *      a. validating OPERANDS order (keeping to by the level in the query)
	 *      b. splitting field and value by : and setting it in the right place in the tree like array
	 *
	 * @param $partialQuery
	 * @param $shouldBeOperand
	 * @param $eSearchQueryResult
	 * @param $levelOperand
	 * @throws vESearchException
	 */
	private static function handlePartialQueryAndAddToResult($partialQuery, &$shouldBeOperand, &$eSearchQueryResult, $levelOperand)
	{
		//trim outer whitespace
		$partialQuery = trim($partialQuery);
		//trim whitespaces after colon (:) - but not within quotes
		$partialQuery = preg_replace("/:\s+(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/", ":", $partialQuery);
		//split by whitespaces but ignore within quotes
		$matches = preg_split('/".*?"(*SKIP)(*FAIL)|\s+/', $partialQuery);
		$operand = null;
		foreach ($matches as $match)
		{
			if (!in_array(strtoupper($match), array(self::AND_OPERAND, self::OR_OPERAND, self::NOT_OPERAND)))
			{
				if ($shouldBeOperand)
					throw new vESearchException('Missing operand', vESearchException::MISSING_QUERY_OPERAND);

				$currentQuery = str_getcsv($match, ":", "\"");
				$eSearchQueryResult[] = $currentQuery;
				$shouldBeOperand = true;
			} else
			{
				$match = strtoupper($match);
				if (!$levelOperand)
				{
					$levelOperand = in_array($match, array(self::AND_OPERAND, self::OR_OPERAND)) ? $match : null;
				} elseif ($levelOperand != $match && $match != self::NOT_OPERAND)
					throw new vESearchException('Un-matching query operand', vESearchException::UNMATCHING_QUERY_OPERAND);
				elseif (!$shouldBeOperand && $match != self::NOT_OPERAND)
					throw new vESearchException('Illegal consecutive operands', vESearchException::CONSECUTIVE_OPERANDS_MISMATCH);
				$eSearchQueryResult[] = $match;
				$shouldBeOperand = false;
			}
		}
	}

	/**
	 * build VidiunESearchParams tree from a tree-like array representing a query by traversing the array in recursive way we will be able to build the result correctly
	 * 1. in case we a single item ( e.g. fieldName without any value - we will create a searchQueryItem with type EXIST
	 * 2. in case we have 2 items ( e.g. fieldName:value) we will create a searchQueryItem with types accordingly (PARTIAL/RANGE/EXACT_MATCH/STARTS_WITH) to the identifiers
	 *      ~fieldNamme = PARTIAL , ~fieldNamme = STARTS_WITH , fieldName:"[ $rangeType$ $rangeValue$]" = RANGE , default = EXACT_MATCH
	 * 3. in case we have more than 2 items we need to go deeper and create and eSearchOperator object to hold more than 1 eSearchObject so we will recurse.
	 *
	 * @param $queryItemArray
	 * @return VidiunESearchCaptionItem|VidiunESearchCategoryItem|VidiunESearchCuePointItem|VidiunESearchEntryItem|VidiunESearchMetadataItem|VidiunESearchOperator|VidiunESearchUserItem|null
	 * @throws vESearchException
	 */
	public static function createVESearchParams($queryItemArray)
	{
		//no query item to handle
		if (!$queryItemArray || count($queryItemArray) == 0)
			$vSearchItem = null;
		//Single item to handle - an inner query part or non-value search item
		elseif (count($queryItemArray) == 1)
			$vSearchItem = self::handleAndCreateSearchQueryItem($queryItemArray[0]);
		//double item to handle - meaning handling fieldName:fieldValue item
		elseif (count($queryItemArray) == 2 && $queryItemArray[0] != self::NOT_OPERAND && !(is_array($queryItemArray[0]) && is_array($queryItemArray[1])))
			$vSearchItem = self::handleAndCreateSearchQueryItem($queryItemArray[0], $queryItemArray[1]);
		else
			$vSearchItem = self::handleAndCreateOperatorQueryItem($queryItemArray);

		return $vSearchItem;
	}

	/**
	 * create a simple eSearchItem according to the different types and setting the type (EXACT_MATCH/PARTIAL/STARTS_WITH/RANGE) and term value accordingly.
	 * @param $fieldName
	 * @param null $fieldValue
	 * @return VidiunESearchCaptionItem|VidiunESearchCategoryItem|VidiunESearchCuePointItem|VidiunESearchEntryItem|VidiunESearchMetadataItem|VidiunESearchUserItem|null
	 */
	private static function CreateVESearchItem($fieldName, $fieldValue = null)
	{
		VidiunLog::debug("Creating Search Item for field [$fieldName] and value [$fieldValue]");

		$isPartial = self::isPartial($fieldName);
		$isStartsWith = self::isStartsWith($fieldName);

		$vSearchItem = self::getClassFromFieldName($fieldName);

		if ($vSearchItem)
		{
			if (is_null($fieldValue))
				$vSearchItem->itemType = VidiunESearchItemType::EXISTS;
			else
				self::handleAndSetTypeAndValue($vSearchItem, $fieldName, $fieldValue, $isPartial, $isStartsWith);
		}
		return $vSearchItem;
	}

	private static function isPartial(&$fieldName)
	{
		if (substr($fieldName, 0, 1) == self::PARTIAL)
		{
			$fieldName = substr($fieldName, 1);
			return true;
		}
		return false;
	}

	private static function isStartsWith(&$fieldName)
	{
		if (substr($fieldName, 0, 1) == self::STARTS_WITH)
		{
			$fieldName = substr($fieldName, 1);
			return true;
		}
		return false;
	}

	/**
	 * Handle setting the type and value - in case we create a metaData item we need to parse the value as json and handle the different fields
	 *
	 * @param $vSearchItem
	 * @param $fieldName
	 * @param $fieldValue
	 * @param $isPartial
	 * @param $isStartsWith
	 * @throws vESearchException
	 */
	private static function handleAndSetTypeAndValue($vSearchItem, $fieldName, $fieldValue, $isPartial, $isStartsWith)
	{
		if ($vSearchItem instanceof VidiunESearchMetadataItem)
			self::handleMetaDataItem($vSearchItem, $fieldName, $fieldValue, $isPartial, $isStartsWith);
		else
			self::validateAndSetTypeAndValue($vSearchItem, $fieldName, $fieldValue, $isPartial, $isStartsWith);
	}

	/**
	 * @param VidiunESearchMetadataItem $vSearchItem
	 * @param $fieldName
	 * @param $fieldValue
	 * @throws vESearchException
	 */
	private static function handleMetaDataItem(VidiunESearchMetadataItem $vSearchItem, $fieldName, $fieldValue, $isPartial, $isStartsWith)
	{
		$fieldValue = preg_replace('/(?<!")(?<!\w)(\w+)(?!")(?!\w)/', '"$1"', $fieldValue); //fix to json format.
		$valueItems = json_decode($fieldValue);
		if (!$valueItems)
			throw new vESearchException('Illegal metadata format [use json format - {xpath:value, metadata_profile_id:value, term:value}]', vESearchException::INVALID_METADATA_FORMAT);
		foreach ($valueItems as $key => $value)
		{
			if (!in_array(strtoupper($key), array(self::XPATH, self::METADATA_PROFILE_ID, self::TERM, self::METADATA_FIELD_ID)))
			{
				$data = array();
				$data['fieldName'] = $key;
				throw new vESearchException('Illegal metadata field name', vESearchException::INVALID_METADATA_FIELD, $data);
			}

			switch (strtoupper($key))
			{
				case self::XPATH:
					$vSearchItem->xpath = $value;
					break;
				case self::METADATA_PROFILE_ID:
					$vSearchItem->metadataProfileId = $value;
					break;
				case self::METADATA_FIELD_ID:
					$vSearchItem->metadataFieldId = $value;
					break;
				case self::TERM:
				{
					self::validateAndSetTypeAndValue($vSearchItem, $fieldName, $value, $isPartial, $isStartsWith);
					break;
				}
			}
		}
	}

	/**
	 * @param $vSearchItem
	 * @param $fieldName
	 * @param $fieldValue
	 * @param $isPartial
	 * @param $isStartsWith
	 * @throws vESearchException
	 */
	private static function validateAndSetTypeAndValue($vSearchItem, $fieldName, $fieldValue, $isPartial, $isStartsWith)
	{
		$rangeObject = self::createRangeObject($fieldValue);
		if (($isStartsWith || $isPartial) && $rangeObject)
		{
			$data = array();
			$data['fieldName'] = $fieldName;
			$data['fieldValue'] = $fieldValue;
			throw new vESearchException("Illegal mixed search item types [$fieldName:$fieldValue]", vESearchException::INVALID_MIXED_SEARCH_TYPES, $data);
		}

		if ($rangeObject)
		{
			$vSearchItem->range = $rangeObject;
			$vSearchItem->itemType = VidiunESearchItemType::RANGE;
		} else
		{
			if ($isPartial)
				$vSearchItem->itemType = VidiunESearchItemType::PARTIAL;
			if ($isStartsWith)
				$vSearchItem->itemType = VidiunESearchItemType::STARTS_WITH;

			$vSearchItem->searchTerm = $fieldValue;
		}
	}


	/**
	 * @param $fieldValue
	 * @return VidiunESearchRange|null
	 */
	private static function createRangeObject($fieldValue)
	{
		//validate we are actually in a range object
		//match range pattern [ LT 12 ; GTE 43 ]
		$rangeItems = self::validateRangeFormatAndExtractItems($fieldValue);
		if (!$rangeItems)
			return null;

		$vESearchRangeObject = new VidiunESearchRange();
		foreach ($rangeItems as $rangeItem)
		{
			if (!self::validateAndSetRangeItem($rangeItem, $vESearchRangeObject))
				return null;
		}
		return $vESearchRangeObject;
	}

	/**
	 * @param $fieldName
	 * @return VidiunESearchCaptionItem|VidiunESearchCategoryItem|VidiunESearchCuePointItem|VidiunESearchEntryItem|VidiunESearchMetadataItem|VidiunESearchUserItem|null
	 */
	private static function getClassFromFieldName($fieldName)
	{
		$vSearchItem = null;
		$fieldName = strtoupper($fieldName);
		if (defined('VidiunESearchEntryFieldName::' . $fieldName))
		{
			$vSearchItem = new VidiunESearchEntryItem();
			$vSearchItem->fieldName = constant("VidiunESearchEntryFieldName::$fieldName");
		}
		if (defined('VidiunESearchCaptionFieldName::' . $fieldName))
		{
			$vSearchItem = new VidiunESearchCaptionItem();
			$vSearchItem->fieldName = constant("VidiunESearchCaptionFieldName::$fieldName");
		}
		if (defined('VidiunESearchCategoryFieldName::' . $fieldName))
		{
			$vSearchItem = new VidiunESearchCategoryItem();
			$vSearchItem->fieldName = constant("VidiunESearchCategoryFieldName::$fieldName");
		}
		if (defined('VidiunESearchCuePointFieldName::' . $fieldName))
		{
			$vSearchItem = new VidiunESearchCuePointItem();
			$vSearchItem->fieldName = constant("VidiunESearchCuePointFieldName::$fieldName");
		}
		if (defined('VidiunESearchUserFieldName::' . $fieldName))
		{
			$vSearchItem = new VidiunESearchUserItem();
			$vSearchItem->fieldName = constant("VidiunESearchUserFieldName::$fieldName");
		}
		if ($fieldName == '_METADATA')
			$vSearchItem = new VidiunESearchMetadataItem();
		if ($fieldName == '_ALL')
			$vSearchItem = new VidiunESearchUnifiedItem();

		//default search item type
		if ($vSearchItem)
			$vSearchItem->itemType = VidiunESearchItemType::EXACT_MATCH;

		return $vSearchItem;
	}

	/**
	 * @param $arr
	 * @return VidiunESearchOperatorType|null
	 */
	private static function getLevelOperator($arr)
	{
		foreach ($arr as $part)
		{
			if ($part == self::AND_OPERAND)
				return VidiunESearchOperatorType::AND_OP;

			if ($part == self::OR_OPERAND)
				return VidiunESearchOperatorType::OR_OP;
		}
		return null;
	}


	/**
	 * @param string $rangeItem
	 * @param VidiunESearchRange $rangeESearchObject
	 * @return int|null
	 */
	private static function validateAndSetRangeItem($rangeItem, VidiunESearchRange $rangeObject)
	{
		$rangeItem = trim($rangeItem);

		// each range param must be XX followed by at least one digit
		if (strlen($rangeItem) < self::RANGE_ITEM_MIN_LENGTH)
			return false;

		$commandPart = substr($rangeItem, 0, self::RANGE_CODE_MAX_LENGTH);
		if (in_array(strtoupper($commandPart), array(self::LESS_THAN_OR_EQUAL, self::GREATER_THAN_OR_EQUAL)))
			$numberPart = substr($rangeItem, self::RANGE_CODE_MAX_LENGTH);
		else
		{
			$commandPart = substr($rangeItem, 0, self::RANGE_CODE_MIN_LENGTH);
			if (!in_array(strtoupper($commandPart), array(self::LESS_THAN, self::GREATER_THAN)))
				return false;
			$numberPart = substr($rangeItem, self::RANGE_CODE_MIN_LENGTH);
		}

		if (!is_numeric($numberPart))
			return false;

		switch (strtoupper($commandPart))
		{
			case self::LESS_THAN:
			{
				if ($rangeObject->lessThan)
					return false;
				else
					$rangeObject->lessThan = $numberPart;
				break;

			}
			case self::LESS_THAN_OR_EQUAL:
			{
				if ($rangeObject->lessThanOrEqual)
					return false;
				else
					$rangeObject->lessThanOrEqual = $numberPart;
				break;
			}
			case self::GREATER_THAN:
			{
				if ($rangeObject->greaterThan)
					return false;
				else
					$rangeObject->greaterThan = $numberPart;
				break;
			}
			case self::GREATER_THAN_OR_EQUAL:
			{
				if ($rangeObject->greaterThanOrEqual)
					return false;
				else
					$rangeObject->greaterThanOrEqual = $numberPart;
				break;
			}
			default:
				return false;
		}

		return true;
	}

	/**
	 * @param $fieldValue
	 * @param $out
	 * Range format example - "[ LT 20 ; GTE 10 ]"
	 * @return array|null
	 */
	private static function validateRangeFormatAndExtractItems($value)
	{
		//validate outer [ ] brackets first location exists
		$a = '/\s*\[.*\]\s*/';
		preg_match($a, $value, $out);
		if (empty($out))
			return null;

		//check if we have more characters out of [ ] brackets
		$other = preg_replace($a, null, $value, 1);
		if ($other)
			return null;

		//get within [ ] brackets
		preg_match('/\[([^)(]+)\]/', $out[0], $out);
		if (empty($out) || empty($out[1]))
			return null;

		$rangeCommand = preg_replace('/\s+/', '', $out[1]);
		$rangeItems = explode(';', $rangeCommand);
		return $rangeItems;
	}

	/**
	 * @param $queryItem
	 * @param  $queryItemValue
	 * @return VidiunESearchCaptionItem|VidiunESearchCategoryItem|VidiunESearchCuePointItem|VidiunESearchEntryItem|VidiunESearchMetadataItem|VidiunESearchOperator|VidiunESearchUserItem|null
	 * @throws vESearchException
	 */
	private static function handleAndCreateSearchQueryItem($queryItem, $queryItemValue = null)
	{
		$vSearchItem = null;
		//If query item is array create inner query search items
		if (is_array($queryItem))
		{
			$vSearchItem = self::createVSearchOperatorObject();
			$innerObject = self::createVESearchParams($queryItem);
			if ($innerObject)
			{
				$innerObjectArray = new VidiunESearchBaseItemArray();
				$innerObjectArray[] = $innerObject;
				$vSearchItem->searchItems = $innerObjectArray;
			}
		} else
		{
			//create a single search item only with field name existance
			$vSearchItem = self::CreateVESearchItem($queryItem, $queryItemValue);
			if (!isset($vSearchItem))
			{
				$data = array();
				$data['fieldName'] = $queryItem;
				throw new vESearchException('Illegal query field name', vESearchException::INVALID_FIELD_NAME, $data);
			}
		}
		return $vSearchItem;
	}

	/**
	 * Create an eSearchOperator object to hold all the items of the same level ( allowed is the same AND/OR OperatorType for all item on the same level but NOT operator is allowed)
	 * 1. get the level operator and create the VSearchOperatorObject container
	 * 2. iterate through the level queryItemsArray and create the objects:
	 *  a. in case we create a not operator we will recurse to the createVESearchParams method since we need to create a deeper level in the tree
	 *  b. we will ignore the other operands in the same level (since they were already verified)
	 *     and we will call createVESearchParams for the items (which can be simple types or hold a deeper level for query commmands.
	 * @param $queryItemArray
	 * @return VidiunESearchOperator
	 */
	private static function handleAndCreateOperatorQueryItem($queryItemArray)
	{
		$vSearchItem = self::createVSearchOperatorObject(self::getLevelOperator($queryItemArray));
		$queryArrayIndex = 0;
		$innerObjects = new VidiunESearchBaseItemArray();
		while ($queryArrayIndex < count($queryItemArray))
		{
			if ($queryItemArray[$queryArrayIndex] == self::NOT_OPERAND)
			{
				$vNotItem = self::createVSearchOperatorObject(VidiunESearchOperatorType::NOT_OP);
				$innerObject = self::createVESearchParams($queryItemArray[$queryArrayIndex + 1]);
				if ($innerObject)
				{
					$vNotItemInnerObject = new VidiunESearchBaseItemArray();
					$vNotItemInnerObject[] = $innerObject;
					$vNotItem->searchItems = $vNotItemInnerObject;
				}
				$innerObjects[] = $vNotItem;
				$queryArrayIndex = $queryArrayIndex + 2;
			} else
			{
				if ($queryItemArray[$queryArrayIndex] != self::OR_OPERAND && $queryItemArray[$queryArrayIndex] != self::AND_OPERAND)
				{
					$innerObject = self::createVESearchParams($queryItemArray[$queryArrayIndex]);
					if ($innerObject)
						$innerObjects[] = $innerObject;
				}
				$queryArrayIndex++;
			}
		}

		$vSearchItem->searchItems = $innerObjects;
		return $vSearchItem;
	}

	/**
	 * @param $operator
	 * @return VidiunESearchOperator
	 */
	private static function createVSearchOperatorObject($operator = null)
	{
		$vSearchItem = new VidiunESearchOperator();
		if (!$operator)
			$operator = VidiunESearchOperatorType::AND_OP;
		$vSearchItem->operator = $operator;
		return $vSearchItem;
	}
}
