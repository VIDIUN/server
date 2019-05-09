<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchCuePointItem extends VidiunESearchEntryAbstractNestedItem
{

	/**
	 * @var VidiunESearchCuePointFieldName
	 */
	public $fieldName;

	private static $map_between_objects = array(
		'fieldName',
	);

	private static $map_dynamic_enum = array(
		VidiunESearchCuePointFieldName::TYPE => 'VidiunCuePointType',
	);

	private static $map_field_enum = array(
		VidiunESearchCuePointFieldName::ANSWERS => ESearchCuePointFieldName::ANSWERS,
		VidiunESearchCuePointFieldName::END_TIME => ESearchCuePointFieldName::END_TIME,
		VidiunESearchCuePointFieldName::EXPLANATION => ESearchCuePointFieldName::EXPLANATION,
		VidiunESearchCuePointFieldName::HINT => ESearchCuePointFieldName::HINT,
		VidiunESearchCuePointFieldName::ID => ESearchCuePointFieldName::ID,
		VidiunESearchCuePointFieldName::NAME => ESearchCuePointFieldName::NAME,
		VidiunESearchCuePointFieldName::QUESTION => ESearchCuePointFieldName::QUESTION,
		VidiunESearchCuePointFieldName::START_TIME => ESearchCuePointFieldName::START_TIME,
		VidiunESearchCuePointFieldName::TAGS => ESearchCuePointFieldName::TAGS,
		VidiunESearchCuePointFieldName::TEXT => ESearchCuePointFieldName::TEXT,
		VidiunESearchCuePointFieldName::SUB_TYPE => ESearchCuePointFieldName::SUB_TYPE,
		VidiunESearchCuePointFieldName::TYPE => ESearchCuePointFieldName::TYPE,
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchCuePointItem();

		return parent::toObject($object_to_fill, $props_to_skip);
	}
	
	protected function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$this->fieldName = self::getApiFieldName($srcObj->getFieldName());
		return parent::doFromObject($srcObj, $responseProfile);
	}
	
	protected static function getApiFieldName ($srcFieldName)
	{
		foreach (self::$map_field_enum as $key => $value)
		{
			if ($value == $srcFieldName)
			{
				return $key;
			}
		}
		
		return null;
	}

	protected function getItemFieldName()
	{
		return $this->fieldName;
	}

	protected function getDynamicEnumMap()
	{
		return self::$map_dynamic_enum;
	}

	protected function getFieldEnumMap()
	{
		return self::$map_field_enum;
	}

}
