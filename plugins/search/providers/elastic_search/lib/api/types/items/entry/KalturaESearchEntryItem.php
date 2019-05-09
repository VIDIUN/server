<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchEntryItem extends VidiunESearchAbstractEntryItem
{

	const VUSER_ID_THAT_DOESNT_EXIST = -1;

	/**
	 * @var VidiunESearchEntryFieldName
	 */
	public $fieldName;

	private static $map_between_objects = array(
		'fieldName'
	);

	private static $map_dynamic_enum = array(
		VidiunESearchEntryFieldName::ENTRY_TYPE => 'VidiunEntryType',
		VidiunESearchEntryFieldName::SOURCE_TYPE => 'VidiunSourceType',
		VidiunESearchEntryFieldName::EXTERNAL_SOURCE_TYPE => 'VidiunExternalMediaSourceType'
	);

	private static $map_field_enum = array(
		VidiunESearchEntryFieldName::ID => ESearchEntryFieldName::ID,
		VidiunESearchEntryFieldName::NAME => ESearchEntryFieldName::NAME,
		VidiunESearchEntryFieldName::DESCRIPTION => ESearchEntryFieldName::DESCRIPTION,
		VidiunESearchEntryFieldName::TAGS => ESearchEntryFieldName::TAGS,
		VidiunESearchEntryFieldName::USER_ID => ESearchEntryFieldName::USER_ID,
		VidiunESearchEntryFieldName::CREATOR_ID => ESearchEntryFieldName::CREATOR_ID,
		VidiunESearchEntryFieldName::START_DATE => ESearchEntryFieldName::START_DATE,
		VidiunESearchEntryFieldName::END_DATE => ESearchEntryFieldName::END_DATE,
		VidiunESearchEntryFieldName::REFERENCE_ID => ESearchEntryFieldName::REFERENCE_ID,
		VidiunESearchEntryFieldName::CONVERSION_PROFILE_ID => ESearchEntryFieldName::CONVERSION_PROFILE_ID,
		VidiunESearchEntryFieldName::REDIRECT_ENTRY_ID => ESearchEntryFieldName::REDIRECT_ENTRY_ID,
		VidiunESearchEntryFieldName::ENTITLED_USER_EDIT => ESearchEntryFieldName::ENTITLED_USER_EDIT,
		VidiunESearchEntryFieldName::ENTITLED_USER_PUBLISH => ESearchEntryFieldName::ENTITLED_USER_PUBLISH,
		VidiunESearchEntryFieldName::ENTITLED_USER_VIEW => ESearchEntryFieldName::ENTITLED_USER_VIEW,
		VidiunESearchEntryFieldName::TEMPLATE_ENTRY_ID => ESearchEntryFieldName::TEMPLATE_ENTRY_ID,
		VidiunESearchEntryFieldName::PARENT_ENTRY_ID => ESearchEntryFieldName::PARENT_ENTRY_ID,
		VidiunESearchEntryFieldName::MEDIA_TYPE => ESearchEntryFieldName::MEDIA_TYPE,
		VidiunESearchEntryFieldName::SOURCE_TYPE => ESearchEntryFieldName::SOURCE_TYPE,
		VidiunESearchEntryFieldName::RECORDED_ENTRY_ID => ESearchEntryFieldName::RECORDED_ENTRY_ID,
		VidiunESearchEntryFieldName::PUSH_PUBLISH => ESearchEntryFieldName::PUSH_PUBLISH,
		VidiunESearchEntryFieldName::LENGTH_IN_MSECS => ESearchEntryFieldName::LENGTH_IN_MSECS,
		VidiunESearchEntryFieldName::CREATED_AT => ESearchEntryFieldName::CREATED_AT,
		VidiunESearchEntryFieldName::UPDATED_AT => ESearchEntryFieldName::UPDATED_AT,
		VidiunESearchEntryFieldName::MODERATION_STATUS => ESearchEntryFieldName::MODERATION_STATUS,
		VidiunESearchEntryFieldName::ENTRY_TYPE => ESearchEntryFieldName::ENTRY_TYPE,
		VidiunESearchEntryFieldName::ADMIN_TAGS => ESearchEntryFieldName::ADMIN_TAGS,
		VidiunESearchEntryFieldName::CREDIT => ESearchEntryFieldName::CREDIT,
		VidiunESearchEntryFieldName::SITE_URL => ESearchEntryFieldName::SITE_URL,
		VidiunESearchEntryFieldName::ACCESS_CONTROL_ID => ESearchEntryFieldName::ACCESS_CONTROL_ID,
		VidiunESearchEntryFieldName::EXTERNAL_SOURCE_TYPE => ESearchEntryFieldName::EXTERNAL_SOURCE_TYPE,
		VidiunESearchEntryFieldName::IS_QUIZ => ESearchEntryFieldName::IS_QUIZ,
		VidiunESearchEntryFieldName::IS_LIVE => ESearchEntryFieldName::IS_LIVE,
		VidiunESearchEntryFieldName::USER_NAMES => ESearchEntryFieldName::USER_NAMES,
		VidiunESearchEntryFieldName::ROOT_ID => ESearchEntryFieldName::ROOT_ID,
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchEntryItem();

		if(in_array($this->fieldName, array(VidiunESearchEntryFieldName::USER_ID, VidiunESearchEntryFieldName::ENTITLED_USER_EDIT,
			VidiunESearchEntryFieldName::ENTITLED_USER_PUBLISH, VidiunESearchEntryFieldName::ENTITLED_USER_VIEW, VidiunESearchEntryFieldName::CREATOR_ID)))
		{
			$vuserId = self::VUSER_ID_THAT_DOESNT_EXIST;
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $this->searchTerm, true);
			if($vuser)
				$vuserId = $vuser->getId();

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
