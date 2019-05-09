<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchGroupUserItem extends VidiunESearchAbstractUserItem
{
	const VUSER_ID_THAT_DOESNT_EXIST = -1;

	/**
	 * @var VidiunEsearchGroupUserFieldName
	 */
	public $fieldName;

	/**
	 * @var VidiunGroupUserCreationMode
	 */
	public $creationMode;


	private static $map_between_objects = array(
		'fieldName',
		'creationMode',
	);

	private static $map_dynamic_enum = array();

	private static $map_field_enum = array(
		VidiunEsearchGroupUserFieldName::GROUP_IDS => ESearchGroupUserFieldName::GROUP_USER_DATA,

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
		{
			$object_to_fill = new ESearchGroupUserItem();
		}

		if (in_array($this->fieldName, array(VidiunEsearchGroupUserFieldName::GROUP_IDS)))
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

}