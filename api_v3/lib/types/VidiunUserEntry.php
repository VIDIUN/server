<?php
/**
 * @package api
 * @subpackage objects
 * @abstract
 * @relatedService UserEntryService
 */
abstract class VidiunUserEntry extends VidiunObject implements IRelatedFilterable
{

	/**
	 * unique auto-generated identifier
	 * @var int
	 * @readonly
	 * @filter eq,in,notin
	 */
	public $id;

	/**
	 * @var string
	 * @insertonly
	 * @filter eq,in,notin
	 */
	public $entryId;

	/**
	 * @var string
	 * @insertonly
	 * @filter eq,in,notin
	 */
	public $userId;

	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;

	/**
	 * @var VidiunUserEntryStatus
	 * @readonly
	 * @filter eq
	 */
	public $status;

	/**
	 * @var time
	 * @readonly
	 * @filter lte,gte,order
	 */
	public $createdAt;

	/**
	 * @var time
	 * @readonly
	 * @filter lte,gte,order
	 */
	public $updatedAt;

	/**
	 * @var VidiunUserEntryType
	 * @readonly
	 * @filter eq
	 */
	public $type;
	
	/**
	 * @var VidiunUserEntryExtendedStatus
	 * @filter eq,in,notin
	 */
	public $extendedStatus;

	private static $map_between_objects = array
	(
		"id",
		"entryId",
		"userId" => "VuserId",
		"partnerId",
		"type",
		"status",
		"createdAt",
		"updatedAt",
		"type",
		"extendedStatus",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}


	/**
	 * Function returns VidiunUserEntry sub-type according to protocol
	 * @var string $type
	 * @return VidiunUserEntry
	 *
	 */
	public static function getInstanceByType ($type)
	{
		$obj = VidiunPluginManager::loadObject("VidiunUserEntry",$type);
		if (is_null($obj))
		{
			VidiunLog::err("The type '$type' is unknown");
		}
		return $obj;
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		$object_to_fill = parent::toInsertableObject($object_to_fill, $props_to_skip);
		if (empty($this->userId))
		{
			$currentVsVuser = vCurrentContext::getCurrentVsVuserId();
			$object_to_fill->setVuserId($currentVsVuser);
		}
		else
		{
			$vuser = vuserPeer::getVuserByPartnerAndUid(vCurrentContext::$vs_partner_id, $this->userId);
			if (!$vuser)
			{
				throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID, $this->userId);
			}
			$object_to_fill->setVuserId($vuser->getVuserId());
		}
		$object_to_fill->setPartnerId(vCurrentContext::getCurrentPartnerId());
		return $object_to_fill;
	}

	/**
	 * Should return the extra filters that are using more than one field
	 * On inherited classes, do not merge the array with the parent class
	 *
	 * @return array
	 */
	function getExtraFilters()
	{
		return array();
	}

	/**
	 * Should return the filter documentation texts
	 *
	 */
	function getFilterDocs()
	{
		return array();
	}

	protected function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$vuser = $srcObj->getvuser();
		if ($vuser)
		{
			$this->userId = $vuser->getPuserId();
		}
		parent::doFromObject($srcObj, $responseProfile);
	}

	public function validateForInsert($propertiesToSkip = array())
	{
		parent::validateForInsert($propertiesToSkip);
		$this->validatePropertyNotNull("entryId");
		$this->validateEntryId();
		$this->validateUserID();
	}
	
	/*
	 * @param string $userEntryID
	 * @throw VidiunAPIException
	 */
	protected function validateEntryId()
	{
		$dbEntry = entryPeer::retrieveByPK($this->entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $this->entryId);
	}
	
	/*
	 * @param string $userEntryID
	 * @throw VidiunAPIException
	 */
	protected function validateUserId()
	{
		$userId = $this->userId ? $this->userId : vCurrentContext::getCurrentVsVuserId();
		if(!$userId || trim($userId) == '')
			throw new VidiunAPIException(VidiunErrors::USER_ID_NOT_PROVIDED_OR_EMPTY);
	}
}