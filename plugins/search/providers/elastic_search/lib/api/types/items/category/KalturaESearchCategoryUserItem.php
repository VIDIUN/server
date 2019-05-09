<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchCategoryUserItem extends VidiunESearchAbstractCategoryItem
{

	const VUSER_ID_THAT_DOESNT_EXIST = -1;

	/**
	 * @var VidiunESearchCategoryUserFieldName
	 */
	public $fieldName;
	
	/**
	 * @var VidiunCategoryUserPermissionLevel
	 */
	public $permissionLevel;

	/**
	 * @var string
	 */
	public $permissionName;
	
	private static $map_between_objects = array(
		'fieldName',
		'permissionLevel',
		'permissionName',
	);

	private static $map_dynamic_enum = array();

	private static $map_field_enum = array(
		VidiunESearchCategoryUserFieldName::USER_ID => ESearchCategoryUserFieldName::USER_ID,
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
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
	
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchCategoryUserItem();

		if(in_array($this->fieldName, array(VidiunESearchCategoryUserFieldName::USER_ID)))
		{
			$vuserId = self::VUSER_ID_THAT_DOESNT_EXIST;
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::getCurrentPartnerId(), $this->searchTerm, true);
			if($vuser)
				$vuserId = $vuser->getId();

			$this->searchTerm = $vuserId;
		}

		return parent::toObject($object_to_fill, $props_to_skip);
	}

}
