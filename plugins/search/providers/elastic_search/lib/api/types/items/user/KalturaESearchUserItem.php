<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchUserItem extends VidiunESearchAbstractUserItem
{

	const VUSER_ID_THAT_DOESNT_EXIST = -1;

	/**
	 * @var VidiunESearchUserFieldName
	 */
	public $fieldName;

	private static $map_between_objects = array(
		'fieldName'
	);

	private static $map_dynamic_enum = array();

	private static $map_field_enum = array(
		VidiunESearchUserFieldName::SCREEN_NAME => ESearchUserFieldName::SCREEN_NAME,
		VidiunESearchUserFieldName::EMAIL => ESearchUserFieldName::EMAIL,
		VidiunESearchUserFieldName::TYPE => ESearchUserFieldName::TYPE,
		VidiunESearchUserFieldName::TAGS => ESearchUserFieldName::TAGS,
		VidiunESearchUserFieldName::UPDATED_AT => ESearchUserFieldName::UPDATED_AT,
		VidiunESearchUserFieldName::CREATED_AT => ESearchUserFieldName::CREATED_AT,
		VidiunESearchUserFieldName::LAST_NAME => ESearchUserFieldName::LAST_NAME,
		VidiunESearchUserFieldName::FIRST_NAME => ESearchUserFieldName::FIRST_NAME,
		VidiunESearchUserFieldName::PERMISSION_NAMES => ESearchUserFieldName::PERMISSION_NAMES,
		VidiunESearchUserFieldName::GROUP_IDS => ESearchUserFieldName::GROUP_IDS,
		VidiunESearchUserFieldName::ROLE_IDS => ESearchUserFieldName::ROLE_IDS,
		VidiunESearchUserFieldName::USER_ID => ESearchUserFieldName::PUSER_ID,
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchUserItem();

		if (in_array($this->fieldName, array(VidiunESearchUserFieldName::GROUP_IDS)))
		{
			$vuserId = self::VUSER_ID_THAT_DOESNT_EXIST;
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $this->searchTerm, true);
			if ($vuser)
			{
				$vuserId = $vuser->getId();
			}

			$this->searchTerm = $vuserId;
		}
		return parent::toObject($object_to_fill, $props_to_skip);
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
