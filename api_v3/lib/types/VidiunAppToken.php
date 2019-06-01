<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAppToken extends VidiunObject implements IFilterable 
{
	/**
	 * The id of the application token
	 * 
	 * @var string
	 * @readonly
	 * @filter eq,in
	 */
	public $id;
	
	/**
	 * The application token
	 * 
	 * @var string
	 * @readonly
	 */
	public $token;
	
	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;
	
	/**
	 * Creation time as Unix timestamp (In seconds) 
	 * 
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;
	
	/**
	 * Update time as Unix timestamp (In seconds) 
	 * 
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/**
	 * Application token status 
	 * 
	 * @var VidiunAppTokenStatus
	 * @readonly
	 * @filter eq,in
	 */
	public $status;

	/**
	 * Expiry time of current token (unix timestamp in seconds)
	 * 
	 * @var int
	 */
	public $expiry;

	/**
	 * Type of VS (Vidiun Session) that created using the current token
	 * 
	 * @var VidiunSessionType
	 */
	public $sessionType;

	/**
	 * User id of VS (Vidiun Session) that created using the current token
	 * 
	 * @var string
	 * @filter eq
	 */
	public $sessionUserId;

	/**
	 * Expiry duration of VS (Vidiun Session) that created using the current token (in seconds)
	 * 
	 * @var int
	 */
	public $sessionDuration;

	/**
	 * Comma separated privileges to be applied on VS (Vidiun Session) that created using the current token
	 * @var string
	 */
	public $sessionPrivileges;

	/**
	 * @var VidiunAppTokenHashType
	 */
	public $hashType;

	/**
	 *
	 * @var string
	 */
	public $description;

	private static $mapBetweenObjects = array
	(
		"id",
		"partnerId",
		"createdAt",
		"updatedAt",
		"status",
		"token",
		"expiry",
		"sessionUserId",
		"sessionType",
		"sessionDuration",
		"sessionPrivileges",
		'hashType',
		'description'
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbAppToken = null, $skip = array())
	{
		if(!$dbAppToken)
			$dbAppToken = new AppToken();
			
		return parent::toObject($dbAppToken, $skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject($dbAppToken = null, $skip = array())
	{
		$partnerId = vCurrentContext::getCurrentPartnerId();
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if($this->isNull('sessionDuration'))
		{
			$this->sessionDuration = $partner->getVsMaxExpiryInSeconds();
		}

		//if user doesn't exists - create it
		$vuser = vuserPeer::getVuserByPartnerAndUid ($partnerId , $this->sessionUserId );
		if(!$vuser)
		{
			if(!preg_match(vuser::PUSER_ID_REGEXP, $this->sessionUserId))
				throw new VidiunAPIException(VidiunErrors::INVALID_FIELD_VALUE, 'sessionUserId');

			$vuser = vuserPeer::createVuserForPartner($partnerId, $this->sessionUserId);
		}

		$dbAppToken = parent::toInsertableObject($dbAppToken, $skip);
		
		/* @var $dbAppToken AppToken */
		$dbAppToken->setPartnerId($partnerId);
		$dbAppToken->setToken(bin2hex(openssl_random_pseudo_bytes(16)));
		$dbAppToken->setStatus(AppTokenStatus::ACTIVE);
		$dbAppToken->setVuserId($vuser->getId());

		return $dbAppToken;
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	 */
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	 */
	public function getFilterDocs()
	{
		return array();
	}
	
}