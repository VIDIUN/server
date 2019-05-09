<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.items
 */
class ESearchCategoryEntryItemFactory
{

	public static function getCoreItemByFieldName($fieldName)
	{
		switch ($fieldName)
		{
			case VidiunESearchCategoryEntryFieldName::ID:
				return new ESearchCategoryEntryIdItem();
			case VidiunESearchCategoryEntryFieldName::NAME:
				return new ESearchCategoryEntryNameItem();
			case VidiunESearchCategoryEntryFieldName::FULL_IDS:
				return new ESearchCategoryEntryFullIdsItem();
			case VidiunESearchCategoryEntryFieldName::ANCESTOR_ID:
				return new ESearchCategoryEntryAncestorIdItem();
			case VidiunESearchCategoryEntryFieldName::ANCESTOR_NAME:
				return new ESearchCategoryEntryAncestorNameItem();
			default:
				VidiunLog::err("Unknown field name $fieldName in ESearchCategoryEntryItemFactory");
				return null;
		}
	}

}
