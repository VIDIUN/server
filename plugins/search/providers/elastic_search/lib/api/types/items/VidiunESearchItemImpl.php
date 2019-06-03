<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
abstract class VidiunESearchItemImpl
{

	const MAX_SEARCH_TERM_LENGTH = 128;

	public static function eSearchItemToObjectImpl(&$eSearchItem, $dynamicEnumMap, $itemFieldName, $fieldEnumMap, $object_to_fill = null, $props_to_skip = array())
	{
		if(strlen($eSearchItem->searchTerm) > self::MAX_SEARCH_TERM_LENGTH)
		{
			$eSearchItem->searchTerm =  mb_strcut($eSearchItem->searchTerm, 0, self::MAX_SEARCH_TERM_LENGTH, "utf-8");
			VidiunLog::log("Search term exceeded maximum allowed length, setting search term to [$eSearchItem->searchTerm]");
		}

		$searchTerm = trim($eSearchItem->searchTerm);
		if(self::shouldChangeToExact($searchTerm, $eSearchItem->itemType))
		{
			$searchTerm = substr($searchTerm, 1, -1);
			$object_to_fill->setSearchTerm($searchTerm);
			$object_to_fill->setItemType(VidiunESearchItemType::EXACT_MATCH);
			$props_to_skip[] = 'searchTerm';
			$props_to_skip[] = 'itemType';
		}

		if(isset($dynamicEnumMap[$itemFieldName]))
		{
			try
			{
				$enumType = call_user_func(array($dynamicEnumMap[$itemFieldName], 'getEnumClass'));
				$SearchTermValue = vPluginableEnumsManager::apiToCore($enumType, $eSearchItem->searchTerm);
				$object_to_fill->setSearchTerm($SearchTermValue);
				$props_to_skip[] = 'searchTerm';
			}
			catch (vCoreException $e)
			{
				if($e->getCode() == vCoreException::ENUM_NOT_FOUND)
					throw new VidiunAPIException(VidiunErrors::INVALID_ENUM_VALUE, $eSearchItem->searchTerm, 'searchTerm', $dynamicEnumMap[$itemFieldName]);
			}

		}

		if(isset($fieldEnumMap[$itemFieldName]))
		{
			$coreFieldName = $fieldEnumMap[$itemFieldName];
			$object_to_fill->setFieldName($coreFieldName);
			$props_to_skip[] = 'fieldName';
		}

		return array($object_to_fill, $props_to_skip);
	}


	private static function shouldChangeToExact($searchTerm, $itemType)
	{
		/*
		 * if itemType is PARTIAL and the searchTerm is wrapped with '"' - change search to EXACT_MATCH and trim '"'
		 * if itemType is EXACT_MATCH and the searchTerm is wrapped with '"' - trim '"'
		 */
		if(in_array($itemType, array(VidiunESearchItemType::PARTIAL, VidiunESearchItemType::EXACT_MATCH)) &&
			strlen($searchTerm) > 2 &&
			substr($searchTerm, 0, 1) == '"' &&
			substr($searchTerm,-1) == '"')
			return true;

		return false;
	}

}
