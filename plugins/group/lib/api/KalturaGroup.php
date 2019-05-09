<?php
/**
 * @package plugins.group
 * @subpackage api.objects
 * @relatedService GroupService
 */
class VidiunGroup extends VidiunBaseUser
{
	/**
	 * @var int
	 * @readonly
	 */
	public $membersCount;

	private static $names = array('fullName','screenName');

	private static $map_between_objects = array("membersCount");

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
		{
			$dbObject = new vuser();
			$dbObject->setType(VuserType::GROUP);
		}
		parent::toObject($dbObject, $skip);
		return $dbObject;
	}

	public function validateForInsert($propertiesToSkip = array())
	{
		$id = $this->id;
		if (!$this->id && $propertiesToSkip->getPuserId())
		{
			$id = $propertiesToSkip->getPuserId();
		}
		if (!preg_match(vuser::PUSER_ID_REGEXP, $id))
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'id');
		}
		$this->validateNames($this,self::$names);
		parent::validateForInsert($propertiesToSkip);
	}

	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$this->validateNames($sourceObject ,self::$names);
		parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}

	public function clonedObject($dbOriginalGroup, $newGroupId, $newGroupName)
	{
		$dbObject = $this->toObject();

		$dbObject->setScreenName($newGroupName);
		$dbObject->setPuserId($newGroupId);
		$dbObject->setTags($dbOriginalGroup->getTags());
		$dbObject->setPartnerId($dbOriginalGroup->getPartnerId());
		$dbObject->setPartnerData($dbOriginalGroup->getPartnerData());
		$dbObject->setStatus($dbOriginalGroup->getStatus());
		$dbObject->setEmail($dbOriginalGroup->getEmail());
		$dbObject->setLanguage($dbOriginalGroup->getLanguage());
		$dbObject->setPicture($dbOriginalGroup->getPicture());
		$dbObject->setAboutMe($dbOriginalGroup->getAboutMe());


		return $dbObject;
	}

}