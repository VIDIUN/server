<?php

/**
 * Auto-generated index class for categoryVuser
*/
class categoryVuserIndex extends BaseIndexObject
{
	public static function getObjectName()
	{
		return 'category_vuser';
	}

	public static function getObjectIndexName()
	{
		return 'category_vuser';
	}

	public static function getSphinxIdField()
	{
		return 'id';
	}

	public static function getPropelIdField()
	{
		return categoryVuserPeer::ID;
	}

	public static function getIdField()
	{
		return null;
	}

	public static function getDefaultCriteriaFilter()
	{
		return categoryVuserPeer::getCriteriaFilter();
	}

	protected static $fieldsMap;

	public static function getIndexFieldsMap()
	{
		if (!self::$fieldsMap)
		{
			self::$fieldsMap = array(
				'category_id' => 'categoryId',
				'vuser_id' => 'vuserId',
				'category_full_ids' => 'searchIndexCategoryFullIds',
				'permission_names' => 'searchIndexPermissionNames',
				'puser_id' => 'puserId',
				'screen_name' => 'screenName',
				'category_vuser_status' => 'searchIndexStatus',
				'partner_id' => 'partnerId',
				'update_method' => 'searchIndexUpdateMethod',
				'created_at' => 'createdAt',
				'updated_at' => 'updatedAt',
			);
		}
		return self::$fieldsMap;
	}

	protected static $typesMap;

	public static function getIndexFieldTypesMap()
	{
		if (!self::$typesMap)
		{
			self::$typesMap = array(
				'category_id' => IIndexable::FIELD_TYPE_STRING,
				'vuser_id' => IIndexable::FIELD_TYPE_STRING,
				'category_full_ids' => IIndexable::FIELD_TYPE_STRING,
				'permission_names' => IIndexable::FIELD_TYPE_STRING,
				'puser_id' => IIndexable::FIELD_TYPE_STRING,
				'screen_name' => IIndexable::FIELD_TYPE_STRING,
				'category_vuser_status' => IIndexable::FIELD_TYPE_STRING,
				'partner_id' => IIndexable::FIELD_TYPE_STRING,
				'update_method' => IIndexable::FIELD_TYPE_STRING,
				'created_at' => IIndexable::FIELD_TYPE_DATETIME,
				'updated_at' => IIndexable::FIELD_TYPE_DATETIME,
			);
		}
		return self::$typesMap;
	}

	protected static $nullableFields;

	public static function getIndexNullableList()
	{
		if (!self::$nullableFields)
		{
			self::$nullableFields = array(
			);
		}
		return self::$nullableFields;
	}

	protected static $searchableFieldsMap;

	public static function getIndexSearchableFieldsMap()
	{
		if (!self::$searchableFieldsMap)
		{
			self::$searchableFieldsMap = array(
				'category_vuser.CATEGORY_ID' => 'category_id',
				'category_vuser.VUSER_ID' => 'vuser_id',
				'category_vuser.CATEGORY_FULL_IDS' => 'category_full_ids',
				'category_vuser.PERMISSION_NAMES' => 'permission_names',
				'category_vuser.PUSER_ID' => 'puser_id',
				'category_vuser.SCREEN_NAME' => 'screen_name',
				'category_vuser.STATUS' => 'category_vuser_status',
				'category_vuser.PARTNER_ID' => 'partner_id',
				'category_vuser.UPDATE_METHOD' => 'update_method',
				'category_vuser.CREATED_AT' => 'created_at',
				'category_vuser.UPDATED_AT' => 'updated_at',
			);
		}
		return self::$searchableFieldsMap;
	}

	protected static $searchEscapeTypes;

	public static function getSearchFieldsEscapeTypeList()
	{
		if (!self::$searchEscapeTypes)
		{
			self::$searchEscapeTypes = array(
				'category_vuser.CATEGORY_FULL_IDS' => SearchIndexFieldEscapeType::MD5_LOWER_CASE,
			);
		}
		return self::$searchEscapeTypes;
	}

	protected static $indexEscapeTypes;

	public static function getIndexFieldsEscapeTypeList()
	{
		if (!self::$indexEscapeTypes)
		{
			self::$indexEscapeTypes = array(
				'category_vuser.CATEGORY_FULL_IDS' => SearchIndexFieldEscapeType::NO_ESCAPE,
			);
		}
		return self::$indexEscapeTypes;
	}

	protected static $matchableFields;

	public static function getIndexMatchableList()
	{
		if (!self::$matchableFields)
		{
			self::$matchableFields = array(
				"category_id",
				"vuser_id",
				"category_full_ids",
				"permission_names",
				"puser_id",
				"screen_name",
				"status",
				"update_method",
			);
		}
		return self::$matchableFields;
	}

	protected static $orderFields;

	public static function getIndexOrderList()
	{
		if (!self::$orderFields)
		{
			self::$orderFields = array(
				'category_vuser.CREATED_AT' => 'created_at',
				'category_vuser.UPDATED_AT' => 'updated_at',
			);
		}
		return self::$orderFields;
	}

	protected static $skipFields;

	public static function getIndexSkipFieldsList()
	{
		if (!self::$skipFields)
		{
			self::$skipFields = array(
			);
		}
		return self::$skipFields;
	}

	protected static $conditionToKeep;

	public static function getSphinxConditionsToKeep()
	{
		if (!self::$conditionToKeep)
		{
			self::$conditionToKeep = array(
			);
		}
		return self::$conditionToKeep;
	}

	protected static $apiCompareAttributesMap;

	public static function getApiCompareAttributesMap()
	{
		if (!self::$apiCompareAttributesMap)
		{
			self::$apiCompareAttributesMap = array(
			);
		}
		return self::$apiCompareAttributesMap;
	}

	protected static $apiMatchAttributesMap;

	public static function getApiMatchAttributesMap()
	{
		if (!self::$apiMatchAttributesMap)
		{
			self::$apiMatchAttributesMap = array(
			);
		}
		return self::$apiMatchAttributesMap;
	}

	//This function is generated based on index elements in the relevant IndexSchema.xml
	public static function getSphinxOptimizationMap()
	{
		return array(
		);
	}

	//This function is generated based on index elements in the relevant IndexSchema.xml
	public static function getSphinxOptimizationValues()
	{
		return array(
		);
	}

	public static function doCountOnPeer(Criteria $c)
	{
		return categoryVuserPeer::doCount($c);
	}

	//This function is generated based on cacheInvalidationKey elements in the relevant IndexSchema.xml
	public static function getCacheInvalidationKeys($object = null)
	{
		if (is_null($object))
			return array(array("category_vuser:partnerId=%s", categoryVuserPeer::PARTNER_ID));
		else
			return array("category_vuser:partnerId=".strtolower($object->getPartnerId()));
	}

}

